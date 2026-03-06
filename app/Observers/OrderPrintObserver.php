<?php

namespace App\Observers;

use App\Events\NewOrderEvent;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

/**
 * Observer para disparar impressão automática de pedidos
 */
class OrderPrintObserver
{
    /**
     * Quando pedido é criado e já está pago
     */
    public function created(Order $order): void
    {
        if ($order->payment_status === 'paid') {
            $this->dispatchPrintEvent($order);
        }
    }

    /**
     * Quando pedido é atualizado
     */
    public function updated(Order $order): void
    {
        // Se mudou para "pago", disparar impressão
        if ($order->wasChanged('payment_status') && $order->payment_status === 'paid') {
            $this->dispatchPrintEvent($order);
        }
    }

    /**
     * Disparar evento de impressão
     */
    private function dispatchPrintEvent(Order $order): void
    {
        try {
            Log::info("Disparando impressão automática para pedido #{$order->id}");

            // Dispara evento WebSocket
            event(new NewOrderEvent($order));

        } catch (\Exception $e) {
            Log::error("Erro ao disparar impressão do pedido #{$order->id}: " . $e->getMessage());
        }
    }
}
