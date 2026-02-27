<?php

namespace App\Filament\Restaurant\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = '📊 Vendas dos Últimos 7 Dias';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = Trend::query(
            Order::query()->where('payment_status', 'paid')
        )
            ->between(
                start: now()->subDays(6),
                end: now(),
            )
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Pedidos',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => 'rgba(255, 107, 53, 0.1)',
                    'borderColor' => 'rgb(255, 107, 53)',
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => \Carbon\Carbon::parse($value->date)->format('d/m')),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
