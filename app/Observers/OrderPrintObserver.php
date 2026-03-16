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
        // 1. Já está pago (PIX instantâneo, cartão aprovado na hora)
        // 2. OU é pagamento na entrega (cash, debit_card)
        $shouldPrint = $order->payment_status === 'paid'
            || in_array($order->payment_method, ['cash', 'debit_card']);

        if ($shouldPrint) {
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
            $reason = $order->payment_status === 'paid'
                ? 'pedido pago'
                : 'pagamento na entrega (' . $order->payment_method . ')';

            Log::info("🖨️ Disparando impressão automática para pedido #{$order->id} - Motivo: {$reason}");

            // Dispara evento WebSocket
            event(new NewOrderEvent($order));

        } catch (\Exception $e) {
            Log::error("❌ Erro ao disparar impressão do pedido #{$order->id}: " . $e->getMessage());
        }
    }
}
