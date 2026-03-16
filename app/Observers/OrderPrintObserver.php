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
     * Quando pedido é criado
     */
    public function created(Order $order): void
    {
        // ⭐ Dispara impressão se:
        // 1. Já está pago (PIX, cartão confirmado)
        // 2. OU está aguardando pagamento na entrega
        $shouldPrint = in_array($order->payment_status, ['paid', 'awaiting_delivery']);

        if ($shouldPrint) {
            $this->dispatchPrintEvent($order);
        }
    }

    /**
     * Quando pedido é atualizado
     */
    public function updated(Order $order): void
    {
        // Se mudou para "pago" ou "aguardando entrega", disparar impressão
        if ($order->wasChanged('payment_status')
            && in_array($order->payment_status, ['paid', 'awaiting_delivery'])) {
            $this->dispatchPrintEvent($order);
        }
    }

    /**
     * Disparar evento de impressão
     */
    private function dispatchPrintEvent(Order $order): void
    {
        try {
            $reason = match($order->payment_status) {
                'paid' => 'pedido pago',
                'awaiting_delivery' => 'aguardando pagamento na entrega (' . $order->payment_method . ')',
                default => 'status: ' . $order->payment_status
            };

            Log::info("🖨️ Disparando impressão automática para pedido #{$order->id} - Motivo: {$reason}");

            // Dispara evento WebSocket
            event(new NewOrderEvent($order));

        } catch (\Exception $e) {
            Log::error("❌ Erro ao disparar impressão do pedido #{$order->id}: " . $e->getMessage());
        }
    }
}
