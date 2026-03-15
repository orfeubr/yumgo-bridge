<?php

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\WeeklyMenuResource\Pages;
use App\Models\Product;
use App\Models\WeeklyMenu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WeeklyMenuResource extends Resource
{
    protected static ?string $model = WeeklyMenu::class;

    protected static ?string $slug = 'cardapio-semanal';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Cardápio Semanal';

    protected static ?string $modelLabel = 'Cardápio Semanal';

    protected static ?string $pluralModelLabel = 'Cardápios Semanais';

    protected static ?string $navigationGroup = '🍕 Cardápio';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Cardápio')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex: Cardápio da Semana - Fevereiro'),

                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->rows(3)
                            ->placeholder('Descrição opcional do cardápio'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true)
                            ->helperText('Apenas um cardápio pode estar ativo por vez'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('starts_at')
                                    ->label('Data de Início')
                                    ->helperText('Opcional - deixe vazio para sem limite'),

                                Forms\Components\DatePicker::make('ends_at')
                                    ->label('Data de Término')
                                    ->helperText('Opcional - deixe vazio para sem limite'),
                            ]),
                    ]),

                Forms\Components\Section::make('📅 Produtos por Dia da Semana')
                    ->description('Selecione os produtos disponíveis em cada dia')
                    ->schema([
                        Forms\Components\Tabs::make('days')
                            ->tabs([
                                Forms\Components\Tabs\Tab::make('🔵 Segunda-feira')
                                    ->icon('heroicon-o-calendar')
                                    ->schema([
                                        Forms\Components\Placeholder::make('monday_help')
                                            ->label('')
                                            ->content('💡 Dica: Use "Marcar todos" ou "Desmarcar todos" acima da lista para selecionar rapidamente!'),

                                        Forms\Components\CheckboxList::make('monday_products')
                                            ->label('Produtos Disponíveis')
                                            ->options(Product::query()->where('is_active', true)->pluck('name', 'id'))
                                            ->searchable()
                                            ->columns(3)
                                            ->gridDirection('row')
                                            ->bulkToggleable()
                                            ->noSearchResultsMessage('Nenhum produto encontrado'),
                                    ]),

                                Forms\Components\Tabs\Tab::make('🟢 Terça-feira')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('tuesday_products')
                                            ->label('Produtos Disponíveis')
                                            ->options(Product::query()->where('is_active', true)->pluck('name', 'id'))
                                            ->searchable()
                                            ->columns(3)
                                            ->bulkToggleable(),
                                    ]),

                                Forms\Components\Tabs\Tab::make('🟡 Quarta-feira')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('wednesday_products')
                                            ->label('Produtos Disponíveis')
                                            ->options(Product::query()->where('is_active', true)->pluck('name', 'id'))
                                            ->searchable()
                                            ->columns(3)
                                            ->bulkToggleable(),
                                    ]),

                                Forms\Components\Tabs\Tab::make('🟠 Quinta-feira')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('thursday_products')
                                            ->label('Produtos Disponíveis')
                                            ->options(Product::query()->where('is_active', true)->pluck('name', 'id'))
                                            ->searchable()
                                            ->columns(3)
                                            ->bulkToggleable(),
                                    ]),

                                Forms\Components\Tabs\Tab::make('🔴 Sexta-feira')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('friday_products')
                                            ->label('Produtos Disponíveis')
                                            ->options(Product::query()->where('is_active', true)->pluck('name', 'id'))
                                            ->searchable()
                                            ->columns(3)
                                            ->bulkToggleable(),
                                    ]),

                                Forms\Components\Tabs\Tab::make('🟣 Sábado')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('saturday_products')
                                            ->label('Produtos Disponíveis')
                                            ->options(Product::query()->where('is_active', true)->pluck('name', 'id'))
                                            ->searchable()
                                            ->columns(3)
                                            ->bulkToggleable(),
                                    ]),

                                Forms\Components\Tabs\Tab::make('🟤 Domingo')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('sunday_products')
                                            ->label('Produtos Disponíveis')
                                            ->options(Product::query()->where('is_active', true)->pluck('name', 'id'))
                                            ->searchable()
                                            ->columns(3)
                                            ->bulkToggleable(),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),
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

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Início')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Sem limite'),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Término')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Sem limite'),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Total de Itens')
                    ->counts('items')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Ativo')
                    ->placeholder('Todos')
                    ->trueLabel('Apenas ativos')
                    ->falseLabel('Apenas inativos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('preview')
                    ->label('Visualizar')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (WeeklyMenu $record): string => Pages\PreviewWeeklyMenu::getUrl(['record' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListWeeklyMenus::route('/'),
            'create' => Pages\CreateWeeklyMenu::route('/create'),
            'edit' => Pages\EditWeeklyMenu::route('/{record}/edit'),
            'preview' => Pages\PreviewWeeklyMenu::route('/{record}/preview'),
        ];
    }
}
