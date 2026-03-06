<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PlanResource\Pages;
use App\Models\Plan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Planos';

    protected static ?string $modelLabel = 'Plano';

    protected static ?string $pluralModelLabel = 'Planos';

    protected static ?string $navigationGroup = 'Plataforma';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Plano')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome do Plano')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex: Starter, Pro, Enterprise'),

                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->placeholder('Descreva os benefícios deste plano'),

                        Forms\Components\TextInput::make('price_monthly')
                            ->label('Preço Mensal (R$)')
                            ->required()
                            ->numeric()
                            ->prefix('R$')
                            ->step('0.01')
                            ->placeholder('79.00'),

                        Forms\Components\TextInput::make('commission_percentage')
                            ->label('Comissão (%)')
                            ->required()
                            ->numeric()
                            ->suffix('%')
                            ->default(3)
                            ->minValue(0)
                            ->maxValue(100)
                            ->helperText('Percentual cobrado por pedido'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Plano Ativo')
                            ->default(true)
                            ->helperText('Desative para ocultar o plano de novos cadastros'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Limites e Recursos')
                    ->schema([
                        Forms\Components\TextInput::make('max_products')
                            ->label('Máximo de Produtos')
                            ->numeric()
                            ->placeholder('Ilimitado se vazio'),

                        Forms\Components\TextInput::make('max_orders_per_month')
                            ->label('Máximo de Pedidos/Mês')
                            ->numeric()
                            ->placeholder('Ilimitado se vazio'),

                        Forms\Components\TagsInput::make('features')
                            ->label('Funcionalidades Incluídas')
                            ->placeholder('Digite e pressione Enter')
                            ->helperText('Ex: Dashboard, Relatórios, Cashback, etc')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Plano')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Starter' => 'info',
                        'Pro' => 'success',
                        'Enterprise' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('price_monthly')
                    ->label('Preço/Mês')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('commission_percentage')
                    ->label('Comissão')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_products')
                    ->label('Produtos')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : 'Ilimitado')
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_orders_per_month')
                    ->label('Pedidos/Mês')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : 'Ilimitado')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Planos Ativos')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('price_monthly', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
