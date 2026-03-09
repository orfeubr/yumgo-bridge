<?php

namespace App\Filament\Admin\Resources\SubscriptionResource\Widgets;

use App\Models\Subscription;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SubscriptionStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Total de assinaturas ativas
        $activeCount = Subscription::where('status', 'active')->count();

        // Total de assinaturas em trial
        $trialCount = Subscription::where('status', 'trialing')->count();

        // Total de assinaturas atrasadas
        $pastDueCount = Subscription::where('status', 'past_due')->count();

        // MRR (Monthly Recurring Revenue) - Receita Recorrente Mensal
        $mrr = Subscription::whereIn('status', ['active', 'trialing'])
            ->sum('amount');

        // Taxa de conversão Trial → Pago
        $totalTrialEnded = Subscription::where('status', 'active')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now())
            ->count();

        $conversionRate = $trialCount > 0
            ? round(($totalTrialEnded / ($totalTrialEnded + $trialCount)) * 100, 1)
            : 0;

        return [
            Stat::make('Assinaturas Ativas', $activeCount)
                ->description('Clientes pagantes')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success')
                ->chart([7, 12, 18, 25, 30, 35, $activeCount]),

            Stat::make('MRR (Receita Mensal)', 'R$ ' . number_format($mrr, 2, ',', '.'))
                ->description('Receita recorrente')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Em Trial', $trialCount)
                ->description('Testando gratuitamente')
                ->descriptionIcon('heroicon-o-clock')
                ->color('info')
                ->chart([3, 5, 8, 12, 15, 18, $trialCount]),

            Stat::make('Atrasadas', $pastDueCount)
                ->description($pastDueCount > 0 ? 'Requer atenção!' : 'Tudo em dia')
                ->descriptionIcon($pastDueCount > 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check')
                ->color($pastDueCount > 0 ? 'danger' : 'success'),
        ];
    }
}
