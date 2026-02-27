<?php

namespace App\Filament\Restaurant\Pages;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ManageStock extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Gerenciar Estoque';
    protected static ?string $title = 'Gerenciamento de Estoque';
    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.restaurant.pages.manage-stock';

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query()->with('category')->orderBy('name'))
            ->columns([
                TextColumn::make('category.name')
                    ->label('Categoria')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Produto')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Product $record): string =>
                        $record->is_pizza ? '🍕 Pizza Personalizável' : ''
                    ),

                IconColumn::make('has_stock_control')
                    ->label('Controla?')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->action(
                        fn (Product $record) => $record->update([
                            'has_stock_control' => !$record->has_stock_control
                        ])
                    )
                    ->tooltip('Clique para ativar/desativar'),

                TextInputColumn::make('stock_quantity')
                    ->label('Estoque Atual')
                    ->type('number')
                    ->rules(['integer', 'min:0'])
                    ->disabled(fn (Product $record): bool => !$record->has_stock_control)
                    ->getStateUsing(fn (Product $record): string =>
                        $record->has_stock_control ? (string)($record->stock_quantity ?? 0) : 'N/A'
                    )
                    ->updateStateUsing(function (Product $record, $state) {
                        if ($record->has_stock_control) {
                            $record->update(['stock_quantity' => $state]);

                            Notification::make()
                                ->success()
                                ->title('Estoque atualizado')
                                ->body("{$record->name}: {$state} unidades")
                                ->send();
                        }
                    }),

                TextInputColumn::make('min_stock_alert')
                    ->label('Estoque Mínimo')
                    ->type('number')
                    ->rules(['integer', 'min:0'])
                    ->disabled(fn (Product $record): bool => !$record->has_stock_control)
                    ->getStateUsing(fn (Product $record): string =>
                        $record->has_stock_control ? (string)($record->min_stock_alert ?? 0) : 'N/A'
                    )
                    ->updateStateUsing(function (Product $record, $state) {
                        if ($record->has_stock_control) {
                            $record->update(['min_stock_alert' => $state]);
                        }
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (Product $record): string =>
                        $record->has_stock_control
                            ? ($record->stock_quantity === 0
                                ? 'Esgotado'
                                : ($record->stock_quantity <= ($record->min_stock_alert ?? 0)
                                    ? 'Estoque Baixo'
                                    : 'Disponível'))
                            : 'Ilimitado'
                    )
                    ->color(fn (Product $record): string =>
                        $record->has_stock_control
                            ? ($record->stock_quantity === 0
                                ? 'danger'
                                : ($record->stock_quantity <= ($record->min_stock_alert ?? 0)
                                    ? 'warning'
                                    : 'success'))
                            : 'gray'
                    ),

                TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL')
                    ->sortable(),
            ])
            ->bulkActions([
                BulkAction::make('activateStockControl')
                    ->label('Ativar Controle')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Collection $records) {
                        $records->each->update(['has_stock_control' => true]);

                        Notification::make()
                            ->success()
                            ->title('Controle ativado')
                            ->body($records->count() . ' produtos')
                            ->send();
                    }),

                BulkAction::make('adjustStock')
                    ->label('Ajustar Estoque')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->form([
                        TextInput::make('adjustment')
                            ->label('Ajuste (+ ou -)')
                            ->helperText('Positivo adiciona, negativo subtrai')
                            ->numeric()
                            ->required(),
                    ])
                    ->action(function (Collection $records, array $data) {
                        $adjustment = $data['adjustment'];
                        $updated = 0;

                        $records->each(function ($record) use ($adjustment, &$updated) {
                            if ($record->has_stock_control) {
                                $newStock = max(0, $record->stock_quantity + $adjustment);
                                $record->update(['stock_quantity' => $newStock]);
                                $updated++;
                            }
                        });

                        Notification::make()
                            ->success()
                            ->title('Estoque ajustado')
                            ->body("{$updated} produtos atualizados")
                            ->send();
                    }),
            ])
            ->emptyStateHeading('Nenhum produto encontrado')
            ->emptyStateIcon('heroicon-o-cube');
    }
}
