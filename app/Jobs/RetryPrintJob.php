<?php

namespace App\Jobs;

use App\Events\NewOrderEvent;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RetryPrintJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $orderId,
        public int $attempt
    ) {
    }

    /**
     * Executa retry de impressão
     */
    public function handle(): void
    {
        $order = Order::find($this->orderId);

        if (!$order) {
            \Log::warning("RetryPrintJob: Pedido {$this->orderId} não encontrado");
            return;
        }

        // Se já foi impresso com sucesso, cancelar retry
        if ($order->print_status === 'printed') {
            \Log::info("RetryPrintJob: Pedido #{$order->order_number} já foi impresso");
            return;
        }

        // Máximo de 3 tentativas automáticas
        if ($this->attempt > 3) {
            \Log::warning("RetryPrintJob: Pedido #{$order->order_number} atingiu máximo de tentativas (3)");
            $order->update([
                'print_error' => "Falha após 3 tentativas automáticas. Ação manual necessária."
            ]);
            return;
        }

        \Log::info("RetryPrintJob: Tentando reimprimir pedido #{$order->order_number} (tentativa {$this->attempt}/3)");

        // Disparar evento de reimpressão
        event(new NewOrderEvent($order, true)); // true = forceReprint

        // Se falhar novamente, agendar próxima tentativa
        // Delay progressivo: 1min, 2min, 3min
        if ($this->attempt < 3) {
            $delayMinutes = $this->attempt + 1;
            self::dispatch($this->orderId, $this->attempt + 1)
                ->delay(now()->addMinutes($delayMinutes));

            \Log::info("RetryPrintJob: Próxima tentativa agendada para " . now()->addMinutes($delayMinutes)->format('H:i'));
        }
    }
}
