<?php

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\FiscalNoteResource\Pages;
use App\Models\FiscalNote;
use App\Services\TributaAiService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;

class FiscalNoteResource extends Resource
{
    protected static ?string $model = FiscalNote::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Notas Fiscais';
    protected static ?string $modelLabel = 'Nota Fiscal';
    protected static ?string $pluralModelLabel = 'Notas Fiscais';
    protected static ?string $navigationGroup = 'Financeiro';
    protected static ?int $navigationSort = 40;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações da Nota')
                    ->schema([
                        Forms\Components\Select::make('order_id')
                            ->label('Pedido')
                            ->relationship('order', 'order_number')
                            ->required()
                            ->disabled(),

                        Forms\Components\TextInput::make('note_number')
                            ->label('Número')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('serie')
                            ->label('Série')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\Badge::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'authorized' => 'success',
                                'cancelled' => 'danger',
                                'rejected', 'error' => 'danger',
                                'processing' => 'warning',
                                'pending' => 'gray',
                                default => 'gray',
                            }),
                    ])->columns(2),

                Forms\Components\Section::make('Dados Fiscais')
                    ->schema([
                        Forms\Components\TextInput::make('chave_acesso')
                            ->label('Chave de Acesso')
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('protocolo')
                            ->label('Protocolo SEFAZ')
                            ->disabled(),

                        Forms\Components\TextInput::make('total_value')
                            ->label('Valor Total')
                            ->prefix('R$')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Datas')
                    ->schema([
                        Forms\Components\DateTimePicker::make('emission_date')
                            ->label('Data de Emissão')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('authorization_date')
                            ->label('Data de Autorização')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('cancellation_date')
                            ->label('Data de Cancelamento')
                            ->disabled()
                            ->visible(fn ($record) => $record?->cancellation_date),
                    ])->columns(3),

                Forms\Components\Section::make('Mensagens')
                    ->schema([
                        Forms\Components\Textarea::make('error_message')
                            ->label('Mensagem de Erro')
                            ->disabled()
                            ->rows(3)
                            ->visible(fn ($record) => $record?->error_message),

                        Forms\Components\Textarea::make('cancellation_reason')
                            ->label('Motivo de Cancelamento')
                            ->disabled()
                            ->rows(3)
                            ->visible(fn ($record) => $record?->cancellation_reason),
                    ])->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('note_number')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Pedido')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => route('filament.restaurant.resources.orders.edit', $record->order_id)),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'authorized',
                        'danger' => ['cancelled', 'rejected', 'error'],
                        'warning' => 'processing',
                        'gray' => 'pending',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'authorized',
                        'heroicon-o-x-circle' => ['cancelled', 'rejected', 'error'],
                        'heroicon-o-clock' => ['processing', 'pending'],
                    ]),

                Tables\Columns\TextColumn::make('total_value')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('emission_date')
                    ->label('Emissão')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('chave_acesso')
                    ->label('Chave')
                    ->limit(20)
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pendente',
                        'processing' => 'Processando',
                        'authorized' => 'Autorizada',
                        'rejected' => 'Rejeitada',
                        'cancelled' => 'Cancelada',
                        'error' => 'Erro',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('download_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn ($record) => $record->pdf_url || $record->isAuthorized())
                    ->action(function (FiscalNote $record) {
                        try {
                            $service = app(TributaAiService::class);
                            $pdf = $service->downloadPDF($record->tributaai_note_id);

                            return response()->streamDownload(function () use ($pdf) {
                                echo $pdf;
                            }, "nfce_{$record->note_number}.pdf");
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Erro ao baixar PDF')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('download_xml')
                    ->label('XML')
                    ->icon('heroicon-o-code-bracket')
                    ->color('info')
                    ->visible(fn ($record) => $record->xml_url || $record->isAuthorized())
                    ->action(function (FiscalNote $record) {
                        try {
                            $service = app(TributaAiService::class);
                            $xml = $service->downloadXML($record->tributaai_note_id);

                            return response()->streamDownload(function () use ($xml) {
                                echo $xml;
                            }, "nfce_{$record->note_number}.xml");
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Erro ao baixar XML')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->isAuthorized())
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('motivo')
                            ->label('Motivo do Cancelamento')
                            ->required()
                            ->minLength(15)
                            ->maxLength(255)
                            ->helperText('O motivo deve ter no mínimo 15 caracteres.')
                            ->rows(3),
                    ])
                    ->action(function (FiscalNote $record, array $data) {
                        try {
                            $service = app(TributaAiService::class);
                            $result = $service->cancelNote($record->tributaai_note_id, $data['motivo']);

                            $record->update([
                                'status' => 'cancelled',
                                'cancellation_date' => now(),
                                'cancellation_reason' => $data['motivo'],
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Nota cancelada com sucesso')
                                ->send();
                        } catch (\Exception $e) {
                            Log::error('Erro ao cancelar nota fiscal', [
                                'fiscal_note_id' => $record->id,
                                'error' => $e->getMessage(),
                            ]);

                            Notification::make()
                                ->danger()
                                ->title('Erro ao cancelar nota')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                // Não permitir exclusão em massa
            ])
            ->defaultSort('emission_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFiscalNotes::route('/'),
            'view' => Pages\ViewFiscalNote::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Notas fiscais só podem ser criadas automaticamente
    }
}
