<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Tenant;
use App\Services\OrderService;

class CancelExpiredOrders extends Command
{
    protected $signature = 'orders:cancel-expired {--tenant=}';
    protected $description = 'Cancela pedidos não pagos expirados (dia anterior ou após fechamento)';

    public function __construct(
        private OrderService $orderService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $tenantId = $this->option('tenant');

        if ($tenantId) {
            // Processar apenas um tenant específico
            $tenant = Tenant::find($tenantId);
            if (!$tenant) {
                $this->error("Tenant {$tenantId} não encontrado");
                return 1;
            }
            $this->processTenant($tenant);
        } else {
            // Processar todos os tenants ativos
            $tenants = Tenant::where('status', 'active')->get();
            $this->info("Processando {$tenants->count()} tenants...");
            
            foreach ($tenants as $tenant) {
                $this->processTenant($tenant);
            }
        }

        $this->info('✅ Processamento concluído!');
        return 0;
    }

    private function processTenant(Tenant $tenant): void
    {
        $this->info("📍 Tenant: {$tenant->name}");
        
        try {
            tenancy()->initialize($tenant);

            // Buscar pedidos não pagos que devem ser cancelados
            $orders = Order::where('payment_status', 'pending')
                ->where('status', '!=', 'cancelled')
                ->where(function($query) {
                    // Pedidos de dias anteriores
                    $query->whereDate('created_at', '<', today())
                        // OU pedidos expirados
                        ->orWhere(function($q) {
                            $q->whereNotNull('expires_at')
                              ->where('expires_at', '<', now());
                        });
                })
                ->get();

            if ($orders->isEmpty()) {
                $this->line("  ℹ️  Nenhum pedido para cancelar");
                tenancy()->end();
                return;
            }

            $cancelledCount = 0;
            $cashbackRefunded = 0;

            foreach ($orders as $order) {
                try {
                    // Devolver cashback usado (se houver)
                    if ($order->cashback_used > 0) {
                        $customer = $order->customer;
                        if ($customer) {
                            $tenantData = $customer->getOrCreateTenantRelation($tenant->id);
                            $tenantData->cashback_balance += $order->cashback_used;
                            $tenantData->save();

                            // Registrar transação
                            \App\Models\CashbackTransaction::create([
                                'customer_id' => $customer->id,
                                'order_id' => $order->id,
                                'type' => 'refund',
                                'amount' => $order->cashback_used,
                                'balance_before' => $tenantData->cashback_balance - $order->cashback_used,
                                'balance_after' => $tenantData->cashback_balance,
                                'description' => "Devolução automática - Pedido #{$order->order_number} cancelado por expiração",
                            ]);

                            $cashbackRefunded += $order->cashback_used;
                        }
                    }

                    // Cancelar pedido
                    $order->update([
                        'status' => 'cancelled',
                        'internal_notes' => ($order->internal_notes ?? '') . "\n[" . now()->format('Y-m-d H:i:s') . "] Cancelado automaticamente por expiração (não pago)",
                    ]);

                    $cancelledCount++;
                    
                    $this->line("  ✅ Cancelado: #{$order->order_number} (criado em {$order->created_at->format('d/m/Y H:i')})");

                } catch (\Exception $e) {
                    $this->error("  ❌ Erro ao cancelar #{$order->order_number}: {$e->getMessage()}");
                }
            }

            $this->info("  📊 Total cancelado: {$cancelledCount} pedidos");
            if ($cashbackRefunded > 0) {
                $this->info("  💰 Cashback devolvido: R$ " . number_format($cashbackRefunded, 2, ',', '.'));
            }

            tenancy()->end();

        } catch (\Exception $e) {
            $this->error("  ❌ Erro no tenant {$tenant->name}: {$e->getMessage()}");
            tenancy()->end();
        }
    }
}
