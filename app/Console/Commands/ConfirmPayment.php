<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Console\Command;

class ConfirmPayment extends Command
{
    protected $signature = 'payment:confirm {tenant} {order_id}';
    protected $description = 'Confirma um pagamento manualmente (para testes)';

    public function handle(OrderService $orderService)
    {
        $tenantId = $this->argument('tenant');
        $orderId = $this->argument('order_id');

        $this->info("🔍 Procurando tenant: {$tenantId}...");

        $tenant = \App\Models\Tenant::find($tenantId);
        if (!$tenant) {
            $this->error("❌ Tenant '{$tenantId}' não encontrado!");
            return 1;
        }

        $this->info("✅ Tenant encontrado: {$tenant->name}");

        // Inicializa tenant
        tenancy()->initialize($tenant);

        $this->info("🔍 Procurando pedido #{$orderId}...");

        $order = Order::with('payments')->find($orderId);
        if (!$order) {
            $this->error("❌ Pedido #{$orderId} não encontrado!");
            return 1;
        }

        $this->info("✅ Pedido encontrado: {$order->order_number}");
        $this->info("   Cliente: {$order->customer->name}");
        $this->info("   Total: R$ " . number_format($order->total, 2, ',', '.'));
        $this->info("   Status: {$order->status}");
        $this->info("   Status Pagamento: {$order->payment_status}");

        $payment = $order->payments()->latest()->first();
        if (!$payment) {
            $this->error("❌ Nenhum pagamento encontrado para este pedido!");
            return 1;
        }

        $this->info("💳 Pagamento: {$payment->method_name}");
        $this->info("   Status: {$payment->status}");
        $this->info("   Transaction ID: {$payment->transaction_id}");

        if ($payment->status === 'confirmed') {
            $this->warn("⚠️ Pagamento já está confirmado!");
            return 0;
        }

        if (!$this->confirm('🤔 Deseja confirmar este pagamento?')) {
            $this->info('Cancelado.');
            return 0;
        }

        // Confirma pagamento
        $payment->update([
            'status' => 'confirmed',
            'paid_at' => now(),
        ]);

        // Confirma pedido e adiciona cashback
        $orderService->confirmPayment($order);

        $this->info('✅ Pagamento confirmado com sucesso!');
        $this->info('💰 Cashback adicionado: R$ ' . number_format($order->cashback_earned, 2, ',', '.'));
        $this->info('📦 Status do pedido atualizado para: confirmed');

        return 0;
    }
}
