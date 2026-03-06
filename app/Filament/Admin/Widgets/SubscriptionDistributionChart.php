<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Plan;
use App\Models\Subscription;
use Filament\Widgets\ChartWidget;

class SubscriptionDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'Distribuição de Assinaturas por Plano';

    protected static ?int $sort = 4;

    protected static ?string $pollingInterval = null;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        try {
            $plans = Plan::withCount(['subscriptions' => function ($query) {
                $query->where('status', 'active');
            }])->get();

            // Se não houver planos, retorna dados vazios
            if ($plans->isEmpty()) {
                return [
                    'datasets' => [
                        [
                            'label' => 'Sem dados',
                            'data' => [],
                            'backgroundColor' => [],
                        ],
                    ],
                    'labels' => [],
                ];
            }

            $labels = [];
            $data = [];
            $colors = [];

            foreach ($plans as $plan) {
                $labels[] = $plan->name;
                $data[] = $plan->subscriptions_count ?? 0;

                // Define cores baseadas no nome do plano
                $colors[] = match ($plan->name) {
                    'Starter' => 'rgba(59, 130, 246, 0.8)', // blue
                    'Pro' => 'rgba(34, 197, 94, 0.8)', // green
                    'Enterprise' => 'rgba(251, 191, 36, 0.8)', // yellow
                    default => 'rgba(107, 114, 128, 0.8)', // gray
                };
            }

            return [
                'datasets' => [
                    [
                        'label' => 'Assinaturas Ativas',
                        'data' => $data,
                        'backgroundColor' => $colors,
                    ],
                ],
                'labels' => $labels,
            ];
        } catch (\Exception $e) {
            \Log::error('Erro no SubscriptionDistributionChart: ' . $e->getMessage());

            return [
                'datasets' => [
                    [
                        'label' => 'Erro',
                        'data' => [0],
                        'backgroundColor' => ['rgba(239, 68, 68, 0.8)'],
                    ],
                ],
                'labels' => ['Erro ao carregar dados'],
            ];
        }
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
