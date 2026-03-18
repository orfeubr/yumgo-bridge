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
        // ⭐ Regra de impressão:
        // 1. JÁ PAGO (qualquer método) → Imprime
        // 2. PAGAMENTO NA ENTREGA (qualquer status) → Imprime
        // 3. Caso contrário (PIX/Cartão online não pago) → NÃO imprime

        $shouldPrint =
            // Opção 1: Já está pago (qualquer método)
            $order->payment_status === 'paid'
            // Opção 2: Pagamento na entrega (dinheiro, débito/crédito presencial, PIX presencial)
            || in_array($order->payment_method, ['cash', 'debit_on_delivery', 'credit_on_delivery', 'pix_on_delivery']);

        if ($shouldPrint) {
            $this->dispatchPrintEvent($order);
        } else {
            Log::info("⏸️ Pedido #{$order->id} não será impresso ainda (aguardando pagamento online: {$order->payment_method})");
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
