<?php

namespace App\Filament\Restaurant\Pages;

use App\Models\Delivery;
use App\Models\DeliveryDriver;
use App\Models\Order;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;

class DeliveryPanel extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = '📦 Painel de Entregas';
    protected static ?string $title = 'Painel de Entregas';
    protected static ?string $navigationGroup = '🚚 Entregas';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.restaurant.pages.delivery-panel';

    public function getPendingOrders(): Collection
    {
        return Order::query()
            ->with(['customer', 'delivery.driver'])
            ->where('delivery_type', 'delivery')
            ->where('payment_status', 'paid')
            ->whereIn('status', ['confirmed', 'preparing'])
            ->whereDoesntHave('delivery', function ($query) {
                $query->whereIn('status', ['delivered', 'failed']);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getInTransitDeliveries(): Collection
    {
        return Delivery::query()
            ->with(['order.customer', 'driver'])
            ->whereIn('status', ['driver_assigned', 'picked_up', 'in_transit'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getActiveDrivers(): Collection
    {
        return DeliveryDriver::query()
            ->where('is_active', true)
            ->withCount(['deliveries' => function ($query) {
                $query->whereIn('status', ['driver_assigned', 'picked_up', 'in_transit']);
            }])
            ->orderBy('deliveries_count', 'asc')
            ->get();
    }

    #[On('assignDriver')]
    public function assignDriver(int $orderId, int $driverId): void
    {
        try {
            $order = Order::findOrFail($orderId);
            $driver = DeliveryDriver::findOrFail($driverId);

            // Verificar se já existe delivery
            $delivery = $order->delivery;

            if (!$delivery) {
                // Criar delivery
                $delivery = Delivery::create([
                    'order_id' => $order->id,
                    'driver_id' => $driverId,
                    'pickup_address' => 'Restaurante',
                    'delivery_address' => $order->delivery_address,
                    'delivery_fee' => $order->delivery_fee,
                    'status' => 'driver_assigned',
                ]);
            } else {
                // Atualizar driver
                $delivery->update([
                    'driver_id' => $driverId,
                    'status' => 'driver_assigned',
                ]);
            }

            // Atualizar status do pedido
            $order->update(['status' => 'out_for_delivery']);

            Notification::make()
                ->success()
                ->title('Entregador Atribuído!')
                ->body("Pedido #{$order->order_number} atribuído para {$driver->name}")
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Erro ao atribuir entregador')
                ->body($e->getMessage())
                ->send();
        }
    }

    #[On('updateDeliveryStatus')]
    public function updateDeliveryStatus(int $deliveryId, string $status): void
    {
        try {
            $delivery = Delivery::findOrFail($deliveryId);

            $delivery->update(['status' => $status]);

            // Atualizar timestamps
            if ($status === 'picked_up') {
                $delivery->picked_up_at = now();
                $delivery->save();
            } elseif ($status === 'delivered') {
                $delivery->delivered_at = now();
                $delivery->order->update(['status' => 'delivered']);
                $delivery->save();
            }

            Notification::make()
                ->success()
                ->title('Status Atualizado!')
                ->body("Entrega atualizada")
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Erro ao atualizar status')
                ->body($e->getMessage())
                ->send();
        }
    }
}
