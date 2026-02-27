<?php

namespace App\Filament\Restaurant\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class MonthlyComparisonChart extends ChartWidget
{
    protected static ?string $heading = '📈 Evolução Mensal - Últimos 6 Meses';
    protected static ?int $sort = 8;
    protected static ?string $maxHeight = '350px';
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Pedidos por mês (apenas pagos)
        $ordersData = Trend::query(
            Order::query()->where('payment_status', 'paid')
        )
            ->between(
                start: now()->subMonths(5)->startOfMonth(),
                end: now()->endOfMonth(),
            )
            ->perMonth()
            ->count();

        // Faturamento por mês (apenas pagos)
        $revenueData = Trend::query(
            Order::query()->where('payment_status', 'paid')
        )
            ->between(
                start: now()->subMonths(5)->startOfMonth(),
                end: now()->endOfMonth(),
            )
            ->perMonth()
            ->sum('total');

        return [
            'datasets' => [
                [
                    'label' => 'Pedidos',
                    'data' => $ordersData->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'yAxisID' => 'y',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Faturamento (R$)',
                    'data' => $revenueData->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                    'yAxisID' => 'y1',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $ordersData->map(fn (TrendValue $value) =>
                \Carbon\Carbon::parse($value->date)->format('M/y')
            ),
        ];
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
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Pedidos',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'beginAtZero' => true,
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Faturamento (R$)',
                    ],
                ],
            ],
        ];
    }
}
