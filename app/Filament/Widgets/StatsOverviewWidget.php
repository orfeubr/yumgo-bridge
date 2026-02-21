<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Tenant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::whereJsonContains('data->status', 'active')->count();
        $trialTenants = Tenant::whereJsonContains('data->status', 'trial')->count();

        $activeSubscriptions = Subscription::where('status', 'active')->count();

        $monthlyRevenue = Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total');

        $lastMonthRevenue = Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now()->subMonth()->month)
            ->whereYear('paid_at', now()->subMonth()->year)
            ->sum('total');

        $revenueChange = $lastMonthRevenue > 0
            ? round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        $pendingInvoices = Invoice::where('status', 'pending')->count();
        $overdueInvoices = Invoice::where('status', 'overdue')->count();

        return [
            Stat::make('Total de Restaurantes', $totalTenants)
                ->description("{$activeTenants} ativos, {$trialTenants} em trial")
                ->descriptionIcon('heroicon-m-building-storefront')
                ->chart([7, 12, 15, 18, 22, 25, $totalTenants])
                ->color('success'),

            Stat::make('Assinaturas Ativas', $activeSubscriptions)
                ->description('Gerando receita recorrente')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('info'),

            Stat::make('Receita do Mês', 'R$ ' . number_format($monthlyRevenue, 2, ',', '.'))
                ->description($revenueChange >= 0 ? "+{$revenueChange}% vs mês anterior" : "{$revenueChange}% vs mês anterior")
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart([$lastMonthRevenue, $monthlyRevenue])
                ->color($revenueChange >= 0 ? 'success' : 'danger'),

            Stat::make('Faturas Pendentes', $pendingInvoices)
                ->description("{$overdueInvoices} em atraso")
                ->descriptionIcon('heroicon-m-document-text')
                ->color($overdueInvoices > 0 ? 'warning' : 'gray'),
        ];
    }
}
