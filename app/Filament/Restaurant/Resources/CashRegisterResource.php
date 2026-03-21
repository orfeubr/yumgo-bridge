<?php

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\CashRegisterResource\Pages;
use App\Filament\Restaurant\Resources\CashRegisterResource\RelationManagers;
use App\Models\CashRegister;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CashRegisterResource extends Resource
{
    protected static ?string $model = CashRegister::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = '📋 Histórico de Caixas';
    protected static ?string $modelLabel = 'Caixa';
    protected static ?string $pluralModelLabel = 'Caixas';
    protected static ?string $slug = 'caixas';
    protected static ?int $navigationSort = 4;

    // ⭐ Controle de permissão - Só quem tem permissão vê no menu
    public static function canViewAny(): bool
    {
        $user = auth()->user();

        // Admin sempre pode
        if ($user->role === 'admin') {
            return true;
        }

        // Outros roles: verifica permissão
        return $user->hasPermission('cash_registers.view');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Caixa')
                    ->schema([
                        Forms\Components\TextInput::make('user_name')
                            ->label('Operador')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('opened_at')
                            ->label('Data/Hora Abertura')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('closed_at')
                            ->label('Data/Hora Fechamento')
                            ->disabled(),

                        Forms\Components\TextInput::make('status')
                            ->label('Status')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Valores')
                    ->schema([
                        Forms\Components\TextInput::make('opening_balance')
                            ->label('Fundo de Troco')
                            ->prefix('R$')
                            ->disabled(),

                        Forms\Components\TextInput::make('closing_balance')
                            ->label('Fechamento Declarado')
                            ->prefix('R$')
                            ->disabled(),

                        Forms\Components\TextInput::make('expected_balance')
                            ->label('Fechamento Esperado')
                            ->prefix('R$')
                            ->disabled(),

                        Forms\Components\TextInput::make('difference')
                            ->label('Diferença (Quebra)')
                            ->prefix('R$')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Totais por Forma de Pagamento')
                    ->schema([
                        Forms\Components\TextInput::make('total_cash')
                            ->label('💵 Dinheiro')
                            ->prefix('R$')
                            ->disabled(),

                        Forms\Components\TextInput::make('total_pix')
                            ->label('💰 PIX')
                            ->prefix('R$')
                            ->disabled(),

                        Forms\Components\TextInput::make('total_credit_card')
                            ->label('💳 Crédito')
                            ->prefix('R$')
                            ->disabled(),

                        Forms\Components\TextInput::make('total_debit_card')
                            ->label('💳 Débito')
                            ->prefix('R$')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Observações')
                    ->schema([
                        Forms\Components\Textarea::make('opening_notes')
                            ->label('Notas de Abertura')
                            ->disabled(),

                        Forms\Components\Textarea::make('closing_notes')
                            ->label('Notas de Fechamento')
                            ->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_name')
                    ->label('Operador')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('opened_at')
                    ->label('Abertura')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('closed_at')
                    ->label('Fechamento')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'closed',
                        'warning' => 'open',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => '🔓 Aberto',
                        'closed' => '🔒 Fechado',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('opening_balance')
                    ->label('Fundo Troco')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('final_balance')
                    ->label('Total Vendas')
                    ->getStateUsing(fn (CashRegister $record) => $record->total_sales)
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('difference')
                    ->label('Quebra')
                    ->money('BRL')
                    ->color(fn ($state) => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray'))
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'open' => '🔓 Aberto',
                        'closed' => '🔒 Fechado',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Removido bulk delete por segurança
            ])
            ->defaultSort('opened_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashRegisters::route('/'),
            // ⭐ REMOVIDO create e edit - Abertura/fechamento é feita pela página "Caixa"
            'view' => Pages\ViewCashRegister::route('/{record}'),
        ];
    }
}
