<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Tenant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        try {
            // Dados de Tenants
            $totalTenants = Tenant::count();
            $activeTenants = Tenant::where('status', 'active')->count();
            $trialTenants = Tenant::where('status', 'trial')->count();

            // Dados de Assinaturas (com fallback)
            $activeSubscriptions = 0;
            try {
                $activeSubscriptions = Subscription::where('status', 'active')->count();
            } catch (\Exception $e) {
                \Log::warning('Erro ao buscar assinaturas: ' . $e->getMessage());
            }

            // Dados de Receita (com fallback)
            $monthlyRevenue = 0;
            $lastMonthRevenue = 0;
            try {
                $monthlyRevenue = Invoice::where('status', 'paid')
                    ->whereMonth('paid_at', now()->month)
                    ->whereYear('paid_at', now()->year)
                    ->sum('total') ?? 0;

                $lastMonthRevenue = Invoice::where('status', 'paid')
                    ->whereMonth('paid_at', now()->subMonth()->month)
                    ->whereYear('paid_at', now()->subMonth()->year)
                    ->sum('total') ?? 0;
            } catch (\Exception $e) {
                \Log::warning('Erro ao buscar receita: ' . $e->getMessage());
            }

            $revenueChange = $lastMonthRevenue > 0
                ? round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
                : 0;

            // Dados de Faturas (com fallback)
            $pendingInvoices = 0;
            $overdueInvoices = 0;
            try {
                $pendingInvoices = Invoice::where('status', 'pending')->count();
                $overdueInvoices = Invoice::where('status', 'overdue')->count();
            } catch (\Exception $e) {
                \Log::warning('Erro ao buscar faturas: ' . $e->getMessage());
            }

            return [
                Stat::make('Total de Restaurantes', $totalTenants)
                    ->description("{$activeTenants} ativos, {$trialTenants} em trial")
                    ->descriptionIcon('heroicon-m-building-storefront')
                    ->color('success'),

                Stat::make('Assinaturas Ativas', $activeSubscriptions)
                    ->description('Gerando receita recorrente')
                    ->descriptionIcon('heroicon-m-credit-card')
                    ->color('info'),

                Stat::make('Receita do Mês', 'R$ ' . number_format($monthlyRevenue, 2, ',', '.'))
                    ->description($revenueChange >= 0 ? "+{$revenueChange}% vs mês anterior" : "{$revenueChange}% vs mês anterior")
                    ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                    ->color($revenueChange >= 0 ? 'success' : 'danger'),

                Stat::make('Faturas Pendentes', $pendingInvoices)
                    ->description($overdueInvoices > 0 ? "{$overdueInvoices} em atraso" : "Nenhuma em atraso")
                    ->descriptionIcon('heroicon-m-document-text')
                    ->color($overdueInvoices > 0 ? 'warning' : 'gray'),
            ];
        } catch (\Exception $e) {
            \Log::error('Erro no StatsOverviewWidget: ' . $e->getMessage());

            // Retorna stats vazios em caso de erro
            return [
                Stat::make('Erro', 'Não foi possível carregar os dados')
                    ->description('Verifique os logs')
                    ->color('danger'),
            ];
        }
    }
}
