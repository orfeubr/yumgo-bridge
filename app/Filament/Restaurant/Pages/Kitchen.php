<?php

namespace App\Filament\Restaurant\Pages;

use App\Models\Order;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

class Kitchen extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-fire';
    protected static ?string $navigationLabel = '👨‍🍳 Cozinha';
    protected static ?string $title = 'Painel da Cozinha';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = '📦 Operações';

    protected static string $view = 'filament.restaurant.pages.kitchen';

    // Atualizar automaticamente a cada 10 segundos
    protected static ?string $pollingInterval = '10s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready'])
                    ->where('payment_status', 'paid')
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('#')
                    ->badge()
                    ->color('primary')
                    ->size(Tables\Columns\TextColumn\TextColumnSize::Large)
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_summary')
                    ->label('Itens')
                    ->html()
                    ->getStateUsing(function (Order $record): string {
                        $items = $record->items->map(function ($item) {
                            return "<div class='text-sm'><strong>{$item->quantity}x</strong> {$item->product_name}</div>";
                        })->implode('');
                        return $items;
                    }),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => '⏳ Pendente',
                        'confirmed' => '✅ Confirmado',
                        'preparing' => '👨‍🍳 Preparando',
                        'ready' => '📦 Pronto',
                        'out_for_delivery' => '🚗 Saiu para Entrega',
                        'delivered' => '✅ Entregue',
                        'cancelled' => '❌ Cancelado',
                        default => $state,
                    })
                    ->colors([
                        'secondary' => 'pending',
                        'warning' => 'confirmed',
                        'primary' => 'preparing',
                        'success' => 'ready',
                        'info' => 'out_for_delivery',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Hora')
                    ->dateTime('H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('preparation_time_left')
                    ->label('Tempo')
                    ->badge()
                    ->color(fn (Order $record) => $record->created_at->diffInMinutes(now()) > 30 ? 'danger' : 'success')
                    ->getStateUsing(fn (Order $record) => $record->created_at->diffInMinutes(now()) . 'min'),
            ])
            ->actions([
                Tables\Actions\Action::make('confirm')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->status === 'pending')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'confirmed']);
                        Notification::make()
                            ->success()
                            ->title('Pedido confirmado!')
                            ->send();
                    }),

                Tables\Actions\Action::make('start_preparing')
                    ->label('Começar Preparo')
                    ->icon('heroicon-o-fire')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->status === 'confirmed')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'preparing']);
                        Notification::make()
                            ->success()
                            ->title('Pedido em preparo!')
                            ->send();
                    }),

                Tables\Actions\Action::make('mark_ready')
                    ->label('Marcar como Pronto')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->status === 'preparing')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'ready']);
                        Notification::make()
                            ->success()
                            ->title('Pedido pronto para entrega!')
                            ->send();
                    }),

                Tables\Actions\Action::make('out_for_delivery')
                    ->label('Saiu para Entrega')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->status === 'ready')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'out_for_delivery']);
                        Notification::make()
                            ->success()
                            ->title('Pedido saiu para entrega!')
                            ->send();
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc')
            ->poll('10s'); // Atualizar automaticamente
    }
}
