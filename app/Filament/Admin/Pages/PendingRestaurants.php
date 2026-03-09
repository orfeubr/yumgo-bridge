<?php

namespace App\Filament\Admin\Pages;

use App\Models\Tenant;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Forms;
use Filament\Forms\Components\Textarea;

class PendingRestaurants extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Aprovações Pendentes';
    protected static ?string $title = 'Restaurantes Aguardando Aprovação';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Gestão';

    protected static string $view = 'filament.admin.pages.pending-restaurants';

    public static function getNavigationBadge(): ?string
    {
        return Tenant::where('approval_status', 'pending_approval')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Tenant::query()
                    ->where('approval_status', 'pending_approval')
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Restaurante')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefone')
                    ->copyable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('URL')
                    ->formatStateUsing(fn ($state) => $state . '.yumgo.com.br')
                    ->copyable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Cadastrado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Aprovar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aprovar Restaurante')
                    ->modalDescription(fn ($record) => "Deseja aprovar o restaurante \"{$record->name}\"? Ele ficará visível no marketplace.")
                    ->action(function ($record) {
                        // ⚠️ IMPORTANTE: Usando DB::update() direto porque o stancl/tenancy
                        // bloqueia atualização de campos não-padrão via Eloquent (save/update)
                        // Tentativas com $record->save() e $record->update() NÃO funcionam!
                        \DB::update(
                            'UPDATE tenants SET approval_status = ?, approved_at = ?, rejection_reason = NULL, rejected_at = NULL WHERE id = ?',
                            ['approved', now(), $record->id]
                        );

                        Notification::make()
                            ->success()
                            ->title('Restaurante aprovado!')
                            ->body("O restaurante \"{$record->name}\" foi aprovado com sucesso.")
                            ->send();
                    })
                    ->after(fn ($livewire) => $livewire->dispatch('$refresh')),

                Tables\Actions\Action::make('reject')
                    ->label('Rejeitar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Motivo da rejeição')
                            ->required()
                            ->placeholder('Ex: Dados inconsistentes, telefone inválido, etc.')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        // UPDATE direto (bypass Eloquent)
                        \DB::update(
                            'UPDATE tenants SET approval_status = ?, rejection_reason = ?, rejected_at = ?, approved_at = NULL WHERE id = ?',
                            ['rejected', $data['rejection_reason'], now(), $record->id]
                        );

                        Notification::make()
                            ->warning()
                            ->title('Restaurante rejeitado')
                            ->body("O restaurante \"{$record->name}\" foi rejeitado.")
                            ->send();
                    })
                    ->after(fn ($livewire) => $livewire->dispatch('$refresh')),

                Tables\Actions\Action::make('view_details')
                    ->label('Detalhes')
                    ->icon('heroicon-o-eye')
                    ->modalContent(fn ($record) => view('filament.admin.modals.tenant-details', ['tenant' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('approve_selected')
                    ->label('Aprovar Selecionados')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $ids = $records->pluck('id')->toArray();

                        // UPDATE direto em massa (bypass Eloquent)
                        \DB::update(
                            'UPDATE tenants SET approval_status = ?, approved_at = ?, rejection_reason = NULL, rejected_at = NULL WHERE id = ANY(?)',
                            ['approved', now(), '{' . implode(',', array_map(fn($id) => '"' . $id . '"', $ids)) . '}']
                        );

                        Notification::make()
                            ->success()
                            ->title('Restaurantes aprovados!')
                            ->body(count($records) . ' restaurante(s) aprovado(s) com sucesso.')
                            ->send();
                    })
                    ->after(fn ($livewire) => $livewire->dispatch('$refresh')),
            ])
            ->emptyStateHeading('Nenhum restaurante aguardando aprovação')
            ->emptyStateDescription('Todos os restaurantes foram revisados!')
            ->emptyStateIcon('heroicon-o-check-badge');
    }
}
