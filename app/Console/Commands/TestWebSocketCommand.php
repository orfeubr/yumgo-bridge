<?php

namespace App\Console\Commands;

use App\Events\NewOrderEvent;
use App\Models\Order;
use Illuminate\Console\Command;

class TestWebSocketCommand extends Command
{
    protected $signature = 'test:websocket {tenant_id}';
    protected $description = 'Testa envio de evento WebSocket para o Bridge';

    public function handle()
    {
        $tenantId = $this->argument('tenant_id');

        // Inicializar tenancy
        $tenant = \App\Models\Tenant::find($tenantId);
        if (!$tenant) {
            $this->error("Tenant {$tenantId} não encontrado!");
            return 1;
        }

        tenancy()->initialize($tenant);

        // Buscar último pedido PAGO
        $order = Order::where('payment_status', 'paid')
            ->with(['items.product', 'customer'])
            ->latest()
            ->first();

        if (!$order) {
            $this->error("Nenhum pedido pago encontrado!");
            return 1;
        }

        $this->info("Disparando evento WebSocket...");
        $this->info("Tenant: {$tenant->id}");
        $this->info("Canal: restaurant.{$tenant->id}");
        $this->info("Pedido: #{$order->order_number}");

        // Disparar evento
        event(new NewOrderEvent($order));

        $this->info("✅ Evento disparado! Verifique o Bridge.");

        return 0;
    }
}
