<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Faturas';

    protected static ?string $modelLabel = 'Fatura';

    protected static ?string $pluralModelLabel = 'Faturas';

    protected static ?string $navigationGroup = 'Financeiro';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações da Fatura')
                    ->schema([
                        Forms\Components\Select::make('tenant_id')
                            ->label('Restaurante')
                            ->relationship('tenant', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('total')
                            ->label('Valor Total (R$)')
                            ->required()
                            ->numeric()
                            ->prefix('R$')
                            ->step('0.01'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pendente',
                                'paid' => 'Paga',
                                'overdue' => 'Vencida',
                                'cancelled' => 'Cancelada',
                            ])
                            ->required()
                            ->default('pending'),

                        Forms\Components\DatePicker::make('due_date')
                            ->label('Data de Vencimento')
                            ->required()
                            ->default(now()->addDays(7)),

                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Data de Pagamento')
                            ->helperText('Preenchido automaticamente ao confirmar pagamento'),

                        Forms\Components\TextInput::make('payment_method')
                            ->label('Método de Pagamento')
                            ->maxLength(50)
                            ->placeholder('PIX, Cartão, Boleto'),

                        Forms\Components\TextInput::make('asaas_payment_id')
                            ->label('ID Pagamento Gateway')
                            ->maxLength(255)
                            ->helperText('ID do pagamento no gateway'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Observações')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Restaurante')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Invoice $record): string => $record->tenant->slug ?? ''),

                Tables\Columns\TextColumn::make('total')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'overdue' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid' => 'Paga',
                        'pending' => 'Pendente',
                        'overdue' => 'Vencida',
                        'cancelled' => 'Cancelada',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Vencimento')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Pago em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Método')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pendente',
                        'paid' => 'Paga',
                        'overdue' => 'Vencida',
                        'cancelled' => 'Cancelada',
                    ]),

                Tables\Filters\Filter::make('overdue')
                    ->label('Vencidas')
                    ->query(fn ($query) => $query->where('status', 'pending')->where('due_date', '<', now())),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('mark_paid')
                    ->label('Marcar como Paga')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Invoice $record) => $record->status !== 'paid')
                    ->requiresConfirmation()
                    ->action(fn (Invoice $record) => $record->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('due_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
