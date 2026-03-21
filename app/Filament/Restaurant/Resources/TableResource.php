<?php

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\TableResource\Pages;
use App\Models\Table as TableModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TableResource extends Resource
{
    protected static ?string $model = TableModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationLabel = '🪑 Mesas';
    protected static ?string $modelLabel = 'Mesa';
    protected static ?string $pluralModelLabel = 'Mesas';
    protected static ?string $slug = 'mesas';
    protected static ?string $navigationGroup = '🏪 Pedidos Presenciais';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações da Mesa')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->label('Número/Nome da Mesa')
                            ->required()
                            ->maxLength(10)
                            ->placeholder('Ex: 1, 2A, Varanda 3')
                            ->helperText('Identificador único para a mesa'),

                        Forms\Components\TextInput::make('seats')
                            ->label('Número de Lugares')
                            ->numeric()
                            ->default(4)
                            ->minValue(1)
                            ->maxValue(20)
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'available' => '🟢 Disponível',
                                'occupied' => '🔴 Ocupada',
                                'reserved' => '🟡 Reservada',
                            ])
                            ->default('available')
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Ativa')
                            ->default(true)
                            ->helperText('Mesas inativas não aparecem no sistema'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Observações')
                            ->rows(3)
                            ->placeholder('Ex: Perto da janela, mesa VIP')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('🔗 QR Code de Acesso')
                    ->schema([
                        Forms\Components\Placeholder::make('qr_url')
                            ->label('Link de Acesso')
                            ->content(fn (?TableModel $record): string => $record?->qr_url ?? 'Será gerado após salvar')
                            ->helperText('O cliente escaneia o QR Code e acessa o cardápio automaticamente'),

                        Forms\Components\Placeholder::make('qr_info')
                            ->label('Informações')
                            ->content(function (?TableModel $record): string {
                                if (!$record) return 'O QR Code será gerado ao criar a mesa';
                                return "Token: {$record->qr_token} | Status: {$record->status_badge}";
                            }),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('downloadQR')
                                ->label('Baixar QR Code')
                                ->icon('heroicon-o-qr-code')
                                ->color('success')
                                ->url(fn (?TableModel $record): string => $record ? route('restaurant.table.qr-code', $record) : '#')
                                ->openUrlInNewTab()
                                ->visible(fn ($get) => $get('id') !== null),
                        ]),
                    ])
                    ->hidden(fn (string $operation): bool => $operation === 'create')
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('Mesa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->size('lg'),

                Tables\Columns\TextColumn::make('seats')
                    ->label('Lugares')
                    ->badge()
                    ->suffix(' pessoas')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => '🟢 Disponível',
                        'occupied' => '🔴 Ocupada',
                        'reserved' => '🟡 Reservada',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'occupied' => 'danger',
                        'reserved' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('active_orders_count')
                    ->label('Pedidos Ativos')
                    ->badge()
                    ->color('info')
                    ->counts('activeOrders')
                    ->default(0),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativa')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

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
                        'available' => '🟢 Disponível',
                        'occupied' => '🔴 Ocupada',
                        'reserved' => '🟡 Reservada',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Ativas')
                    ->placeholder('Todas')
                    ->trueLabel('Apenas ativas')
                    ->falseLabel('Apenas inativas'),
            ])
            ->actions([
                Tables\Actions\Action::make('copyLink')
                    ->label('Copiar Link')
                    ->icon('heroicon-o-clipboard')
                    ->color('success')
                    ->extraAttributes(fn (TableModel $record) => [
                        'x-on:click' => "
                            navigator.clipboard.writeText('{$record->qr_url}');
                            \$tooltip('Link copiado!', { timeout: 2000 });
                        "
                    ]),

                Tables\Actions\Action::make('viewQR')
                    ->label('Ver QR')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->url(fn (TableModel $record): string => route('restaurant.table.qr-code', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('regenerateToken')
                    ->label('Regenerar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Regenerar Token')
                    ->modalDescription('Isso invalidará o QR Code atual. Você precisará imprimir um novo.')
                    ->action(function (TableModel $record) {
                        $record->regenerateToken();
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Token Regenerado!')
                            ->body('Novo QR Code criado. Baixe e imprima.')
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('number', 'asc')
            ->poll('30s'); // Auto-refresh a cada 30s
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
            'index' => Pages\ListTables::route('/'),
            'create' => Pages\CreateTable::route('/create'),
            'edit' => Pages\EditTable::route('/{record}/edit'),
        ];
    }
}
