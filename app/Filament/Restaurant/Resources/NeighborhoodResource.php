<?php

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\NeighborhoodResource\Pages;
use App\Models\Neighborhood;
use App\Services\LocationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class NeighborhoodResource extends Resource
{
    protected static ?string $model = Neighborhood::class;

    protected static ?string $slug = 'bairros';

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Bairros de Entrega';

    protected static ?string $modelLabel = 'Bairro';

    protected static ?string $pluralModelLabel = 'Bairros';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = '📍 Entregas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Bairro')
                    ->schema([
                        Forms\Components\TextInput::make('city')
                            ->label('Cidade')
                            ->required()
                            ->default('Louveira')
                            ->helperText('Digite o nome da cidade'),

                        Forms\Components\TextInput::make('name')
                            ->label('Nome do Bairro')
                            ->required()
                            ->helperText('Digite o nome do bairro (ex: Centro, Jardim Bela Vista)'),

                        Forms\Components\Toggle::make('enabled')
                            ->label('Ativo (Você atende este bairro?)')
                            ->default(false)
                            ->helperText('Marque para disponibilizar este bairro para delivery'),

                        Forms\Components\TextInput::make('delivery_fee')
                            ->label('Taxa de Entrega (R$)')
                            ->numeric()
                            ->prefix('R$')
                            ->step(0.01)
                            ->required()
                            ->default(5.00)
                            ->helperText('Valor cobrado para entrega neste bairro'),

                        Forms\Components\TextInput::make('delivery_time')
                            ->label('Tempo Estimado (minutos)')
                            ->numeric()
                            ->suffix('min')
                            ->required()
                            ->default(30)
                            ->helperText('Tempo médio de entrega neste bairro'),

                        Forms\Components\TextInput::make('minimum_order')
                            ->label('Pedido Mínimo (R$)')
                            ->numeric()
                            ->prefix('R$')
                            ->step(0.01)
                            ->nullable()
                            ->helperText('Deixe vazio para usar o padrão geral'),

                        Forms\Components\TextInput::make('order')
                            ->label('Ordem de Exibição')
                            ->numeric()
                            ->default(0)
                            ->helperText('Bairros com menor número aparecem primeiro'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ToggleColumn::make('enabled')
                    ->label('Ativo')
                    ->onColor('success')
                    ->offColor('danger')
                    ->sortable()
                    ->afterStateUpdated(function ($record, $state) {
                        Notification::make()
                            ->success()
                            ->title($state ? 'Bairro ativado!' : 'Bairro desativado!')
                            ->send();
                    }),

                Tables\Columns\TextColumn::make('name')
                    ->label('Bairro')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('city')
                    ->label('Cidade')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('delivery_fee')
                    ->label('Taxa')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('delivery_time')
                    ->label('Tempo')
                    ->suffix(' min')
                    ->sortable(),

                Tables\Columns\TextColumn::make('minimum_order')
                    ->label('Pedido Mín.')
                    ->money('BRL')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('order')
                    ->label('Ordem')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('enabled')
                    ->label('Status')
                    ->placeholder('Todos')
                    ->trueLabel('Apenas Ativos')
                    ->falseLabel('Apenas Inativos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('enable_all')
                        ->label('Ativar Selecionados')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['enabled' => true]));
                            Notification::make()
                                ->success()
                                ->title('Bairros ativados!')
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('disable_all')
                        ->label('Desativar Selecionados')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['enabled' => false]));
                            Notification::make()
                                ->success()
                                ->title('Bairros desativados!')
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('order')
            ->reorderable('order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNeighborhoods::route('/'),
            'create' => Pages\CreateNeighborhood::route('/create'),
            'edit' => Pages\EditNeighborhood::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('enabled', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
