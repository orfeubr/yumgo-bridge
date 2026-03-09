<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class UpdateTenantStatsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('🔄 Iniciando atualização de estatísticas dos tenants...');

        $tenants = Tenant::where('status', 'active')->get();

        foreach ($tenants as $tenant) {
            try {
                // Inicializar tenancy para acessar orders do schema correto
                tenancy()->initialize($tenant);

                // Contar total de pedidos pagos
                $totalOrders = Order::where('payment_status', 'paid')->count();

                // Contar pedidos dos últimos 30 dias
                $totalOrders30d = Order::where('payment_status', 'paid')
                    ->where('created_at', '>=', now()->subDays(30))
                    ->count();

                // TODO: Quando implementar sistema de reviews, adicionar:
                // $avgRating = Review::where('tenant_id', $tenant->id)->avg('rating');
                // $totalReviews = Review::where('tenant_id', $tenant->id)->count();

                tenancy()->end();

                // Atualizar tenant no schema central
                $tenant->update([
                    'total_orders' => $totalOrders,
                    'total_orders_30d' => $totalOrders30d,
                    // 'avg_rating' => $avgRating ?? 0.00,
                    // 'total_reviews' => $totalReviews ?? 0,
                    'stats_updated_at' => now(),
                ]);

                Log::info("✅ Estatísticas atualizadas: {$tenant->name} ({$totalOrders} pedidos, {$totalOrders30d} últimos 30d)");

            } catch (\Exception $e) {
                Log::error("❌ Erro ao atualizar estatísticas do tenant {$tenant->name}: " . $e->getMessage());

                // Garantir que tenancy seja finalizado em caso de erro
                tenancy()->end();
            }
        }

        Log::info('✅ Atualização de estatísticas concluída!');
    }
}
