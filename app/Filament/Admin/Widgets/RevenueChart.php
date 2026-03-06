<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Receita Mensal (Últimos 6 meses)';

    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        try {
            $data = [];
            $labels = [];

            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);

                $revenue = Invoice::where('status', 'paid')
                    ->whereMonth('paid_at', $date->month)
                    ->whereYear('paid_at', $date->year)
                    ->sum('total') ?? 0;

                $labels[] = $date->translatedFormat('M/y');
                $data[] = round($revenue, 2);
            }

            return [
                'datasets' => [
                    [
                        'label' => 'Receita (R$)',
                        'data' => $data,
                        'borderColor' => 'rgb(34, 197, 94)',
                        'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                        'fill' => true,
                        'tension' => 0.3,
                    ],
                ],
                'labels' => $labels,
            ];
        } catch (\Exception $e) {
            \Log::error('Erro no RevenueChart: ' . $e->getMessage());

            return [
                'datasets' => [
                    [
                        'label' => 'Erro',
                        'data' => [0, 0, 0, 0, 0, 0],
                    ],
                ],
                'labels' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
            ];
        }
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "R$ " + value.toLocaleString("pt-BR", {minimumFractionDigits: 2}); }',
                    ],
                ],
            ],
        ];
    }
}
