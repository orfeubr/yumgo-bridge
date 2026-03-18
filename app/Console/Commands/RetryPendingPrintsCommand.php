<?php

namespace App\Console\Commands;

use App\Events\NewOrderEvent;
use App\Models\Order;
use App\Models\Tenant;
use Illuminate\Console\Command;

class RetryPendingPrintsCommand extends Command
{
    protected $signature = 'orders:retry-pending-prints
                          {--minutes=5 : Minutos de espera antes de retry}';

    protected $description = 'Reimprimir pedidos pendentes há muito tempo (evento disparado mas Bridge não respondeu)';

    public function handle()
    {
        $minutesThreshold = (int) $this->option('minutes');

        $this->info("🔍 Procurando pedidos pendentes há mais de {$minutesThreshold} minutos...");

        $tenants = Tenant::all();
        $totalRetried = 0;

        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);

            // Buscar pedidos:
            // - print_status = 'pending' (nunca imprimiu)
            // - criados há mais de X minutos
            // - EXCETO: PIX/Cartão online ainda não pagos
            $pendingOrders = Order::where('print_status', 'pending')
                ->where('created_at', '<=', now()->subMinutes($minutesThreshold))
                ->where(function($query) {
                    // Opção 1: Já está pago (qualquer método)
                    $query->where('payment_status', 'paid')
                        // Opção 2: Pagamento na entrega (dinheiro, débito presencial, etc)
                        ->orWhereIn('payment_method', ['cash', 'debit_on_delivery', 'credit_on_delivery', 'pix_on_delivery'])
                        // Opção 3: Status que não precisa esperar pagamento
                        ->orWhereIn('payment_status', ['awaiting_delivery', 'delivered']);
                })
                ->get();

            if ($pendingOrders->isEmpty()) {
                continue;
            }

            $this->warn("📍 {$tenant->name}: {$pendingOrders->count()} pedidos pendentes");

            foreach ($pendingOrders as $order) {
                $ageMinutes = $order->created_at->diffInMinutes(now());

                $this->line("   🖨️  Pedido #{$order->order_number} (criado há {$ageMinutes} min)");

                // Marca como printing antes de tentar
                $order->markPrinting();

                // Disparar evento de impressão
                event(new NewOrderEvent($order, true)); // true = forceReprint

                $totalRetried++;
            }

            tenancy()->end();
        }

        if ($totalRetried > 0) {
            $this->info("✅ {$totalRetried} pedidos reimpressos com sucesso!");
        } else {
            $this->info("✅ Nenhum pedido pendente encontrado");
        }

        return 0;
    }
}
