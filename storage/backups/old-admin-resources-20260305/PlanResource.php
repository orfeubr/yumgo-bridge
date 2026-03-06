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
    
    protected static ?string $navigationGroup = 'Plataforma';
    
    protected static ?string $modelLabel = 'Plano';
    
    protected static ?string $pluralModelLabel = 'Planos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações Básicas')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->rows(3)
                            ->maxLength(500),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true),
                    ])->columns(1),
                
                Forms\Components\Section::make('Valores')
                    ->schema([
                        Forms\Components\TextInput::make('price_monthly')
                            ->label('Preço Mensal (R$)')
                            ->required()
                            ->numeric()
                            ->prefix('R$')
                            ->step(0.01),
                        
                        Forms\Components\TextInput::make('commission_percentage')
                            ->label('Comissão (%)')
                            ->required()
                            ->numeric()
                            ->suffix('%')
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100),
                    ])->columns(2),
                
                Forms\Components\Section::make('Limites')
                    ->schema([
                        Forms\Components\TextInput::make('max_products')
                            ->label('Máximo de Produtos')
                            ->numeric()
                            ->helperText('Deixe vazio para ilimitado'),
                        
                        Forms\Components\TextInput::make('max_orders_per_month')
                            ->label('Máximo de Pedidos/Mês')
                            ->numeric()
                            ->helperText('Deixe vazio para ilimitado'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Funcionalidades')
                    ->schema([
                        Forms\Components\TagsInput::make('features')
                            ->label('Features')
                            ->helperText('Pressione Enter para adicionar cada feature'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('price_monthly')
                    ->label('Preço/Mês')
                    ->money('BRL')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('commission_percentage')
                    ->label('Comissão')
                    ->suffix('%')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('max_products')
                    ->label('Produtos')
                    ->default('Ilimitado'),
                
                Tables\Columns\TextColumn::make('max_orders_per_month')
                    ->label('Pedidos/Mês')
                    ->default('Ilimitado'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueLabel('Apenas ativos')
                    ->falseLabel('Apenas inativos')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
