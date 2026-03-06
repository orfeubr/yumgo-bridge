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

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationGroup = 'Plataforma';
    
    protected static ?string $modelLabel = 'Fatura';
    
    protected static ?string $pluralModelLabel = 'Faturas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tenant_id')
                    ->label('Restaurante')
                    ->relationship('tenant', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->data['name'] ?? $record->id)
                    ->required()
                    ->searchable(),
                
                Forms\Components\TextInput::make('invoice_number')
                    ->label('Número da Fatura')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('total')
                    ->label('Valor Total')
                    ->required()
                    ->numeric()
                    ->prefix('R$')
                    ->step(0.01),
                
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pendente',
                        'paid' => 'Paga',
                        'overdue' => 'Atrasada',
                        'canceled' => 'Cancelada',
                    ])
                    ->required()
                    ->default('pending'),
                
                Forms\Components\DatePicker::make('due_date')
                    ->label('Data de Vencimento')
                    ->required(),
                
                Forms\Components\DateTimePicker::make('paid_at')
                    ->label('Data de Pagamento')
                    ->nullable(),
                
                Forms\Components\TextInput::make('payment_method')
                    ->label('Método de Pagamento')
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('asaas_payment_id')
                    ->label('ID Pagamento Asaas')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Nº Fatura')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('tenant.data.name')
                    ->label('Restaurante')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'overdue' => 'danger',
                        'canceled' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendente',
                        'paid' => 'Paga',
                        'overdue' => 'Atrasada',
                        'canceled' => 'Cancelada',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Vencimento')
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Pago em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('-'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criada em')
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
                        'overdue' => 'Atrasada',
                        'canceled' => 'Cancelada',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
