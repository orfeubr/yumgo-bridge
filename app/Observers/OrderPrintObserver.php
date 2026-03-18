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
        // ⭐ Regra de impressão (invertida para segurança):
        // IMPRIME se:
        //   1. JÁ PAGO (qualquer método) → Imprime
        //   2. NÃO é pagamento online → Imprime (cobre TODOS os métodos presenciais atuais e futuros)
        // NÃO IMPRIME se:
        //   3. É PIX/Cartão online E ainda não está pago

        $isOnlinePayment = in_array($order->payment_method, ['pix', 'credit_card', 'debit_card']);

        $shouldPrint =
            // Opção 1: Já está pago (qualquer método, inclusive online)
            $order->payment_status === 'paid'
            // Opção 2: NÃO é pagamento online (cobre cash, debit_on_delivery, credit_on_delivery, etc)
            || !$isOnlinePayment;

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
