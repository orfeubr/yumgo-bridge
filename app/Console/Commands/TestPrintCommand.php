<?php

namespace App\Console\Commands;

use App\Events\NewOrderEvent;
use App\Models\Order;
use App\Models\Tenant;
use Illuminate\Console\Command;

class TestPrintCommand extends Command
{
    protected $signature = 'test:print {order_id}';
    protected $description = 'Testar impressão de um pedido específico';

    public function handle()
    {
        $orderId = $this->argument('order_id');

        // Inicializar tenant
        $tenant = Tenant::find('marmitariadagi');
        tenancy()->initialize($tenant);

        // Buscar pedido
        $order = Order::find($orderId);

        if (!$order) {
            $this->error("Pedido #{$orderId} não encontrado!");
            return 1;
        }

        $this->info("📋 Pedido: #{$order->order_number}");
        $this->info("💰 Total: R$ " . number_format($order->total, 2, ',', '.'));
        $this->info("🏪 Tenant: " . tenant('id'));
        $this->info("");

        // Disparar evento
        $this->info("🚀 Disparando evento NewOrderEvent...");
        event(new NewOrderEvent($order));

        $this->info("✅ Evento disparado!");
        $this->info("🖨️  Verifique se imprimiu no Bridge!");

        return 0;
    }
}
