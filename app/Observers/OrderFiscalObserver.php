<?php

namespace App\Observers;

use App\Jobs\EmitirNFCeJob;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderFiscalObserver
{
    /**
     * Handle the Order "updated" event.
     * Emite NFC-e automaticamente quando o pagamento é confirmado
     */
    public function updated(Order $order): void
    {
        // Verificar se o status de pagamento mudou para 'paid'
        if ($order->wasChanged('payment_status') && $order->payment_status === 'paid') {
            $this->emitFiscalNote($order);
        }
    }

    /**
     * Emitir nota fiscal para o pedido (assíncrono via Job)
     */
    private function emitFiscalNote(Order $order): void
    {
        try {
            $tenant = tenant();

            // Verificar se o tenant tem emissão fiscal ativa
            if (!$tenant || !$tenant->certificate_a1) {
                Log::info('🧾 Nota fiscal não emitida: Certificado A1 não configurado', [
                    'order_id' => $order->id,
                ]);
                return;
            }

            // Verificar se já existe nota fiscal para este pedido
            if ($order->fiscalNote) {
                Log::info('🧾 Nota fiscal já existe para este pedido', [
                    'order_id' => $order->id,
                    'fiscal_note_id' => $order->fiscalNote->id,
                ]);
                return;
            }

            Log::info('📋 Despachando Job para emissão de NFC-e', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'tenant_id' => $tenant->id,
            ]);

            // Despachar Job para fila (assíncrono)
            EmitirNFCeJob::dispatch($order->id, $tenant->id)
                ->onQueue('nfce')
                ->delay(now()->addSeconds(5)); // Delay de 5 segundos para garantir que o pedido está salvo

        } catch (\Exception $e) {
            Log::error('❌ Erro ao despachar Job de NFC-e', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

}
