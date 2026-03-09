<?php

namespace App\Filament\Restaurant\Pages;

use App\Models\Order;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

class DeliveryPanel extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = '🚗 Entregas';
    protected static ?string $title = 'Painel de Entregas';
    protected static ?int $navigationSort = 11;
    protected static ?string $navigationGroup = 'Operações';
    protected static string $view = 'filament.restaurant.pages.delivery-panel';
    protected static ?string $pollingInterval = '10s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->whereIn('status', ['ready', 'out_for_delivery'])
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
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->description(fn (Order $record): string => $record->customer->phone ?? ''),

                Tables\Columns\TextColumn::make('delivery_address')
                    ->label('Endereço')
                    ->wrap()
                    ->limit(50),

                Tables\Columns\TextColumn::make('delivery_neighborhood')
                    ->label('Bairro')
                    ->badge()
                    ->color('info'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ready' => '📦 Pronto',
                        'out_for_delivery' => '🚗 Em Entrega',
                        'delivered' => '✅ Entregue',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'ready',
                        'info' => 'out_for_delivery',
                        'primary' => 'delivered',
                    ]),

                Tables\Columns\TextColumn::make('total')
                    ->label('Valor')
                    ->money('BRL')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Hora')
                    ->dateTime('H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('start_delivery')
                    ->label('Iniciar Entrega')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->size('lg')
                    ->visible(fn (Order $record) => $record->status === 'ready')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'out_for_delivery']);
                        Notification::make()
                            ->success()
                            ->title('🚗 Entrega iniciada!')
                            ->send();
                    }),

                Tables\Actions\Action::make('mark_delivered')
                    ->label('Marcar como Entregue')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->size('lg')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar Entrega')
                    ->modalDescription('Confirme que o pedido foi entregue ao cliente.')
                    ->visible(fn (Order $record) => $record->status === 'out_for_delivery')
                    ->action(function (Order $record) {
                        $record->update([
                            'status' => 'delivered',
                            'delivered_at' => now(),
                        ]);
                        Notification::make()
                            ->success()
                            ->title('✅ Pedido entregue com sucesso!')
                            ->send();
                    }),

                Tables\Actions\Action::make('view_map')
                    ->label('Ver Mapa')
                    ->icon('heroicon-o-map')
                    ->color('gray')
                    ->url(fn (Order $record): string =>
                        "https://www.google.com/maps/search/?api=1&query=" .
                        urlencode($record->delivery_address . ', ' . $record->delivery_neighborhood)
                    )
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc')
            ->poll('10s');
    }
}
