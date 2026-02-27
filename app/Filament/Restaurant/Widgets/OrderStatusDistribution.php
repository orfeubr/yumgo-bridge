<?php

namespace App\Filament\Restaurant\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class OrderStatusDistribution extends ChartWidget
{
    protected static ?string $heading = '📊 Distribuição de Pedidos por Status';
    protected static ?int $sort = 6;
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $statusData = Order::query()
            ->select('status', DB::raw('COUNT(*) as count'))
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->groupBy('status')
            ->get();

        $labels = [];
        $data = [];
        $colors = [];

        $statusConfig = [
            'pending' => ['label' => 'Pendente', 'color' => 'rgba(107, 114, 128, 0.8)'],
            'confirmed' => ['label' => 'Confirmado', 'color' => 'rgba(245, 158, 11, 0.8)'],
            'preparing' => ['label' => 'Preparando', 'color' => 'rgba(59, 130, 246, 0.8)'],
            'ready' => ['label' => 'Pronto', 'color' => 'rgba(168, 85, 247, 0.8)'],
            'out_for_delivery' => ['label' => 'Em entrega', 'color' => 'rgba(20, 184, 166, 0.8)'],
            'delivered' => ['label' => 'Entregue', 'color' => 'rgba(34, 197, 94, 0.8)'],
            'cancelled' => ['label' => 'Cancelado', 'color' => 'rgba(239, 68, 68, 0.8)'],
        ];

        foreach ($statusData as $status) {
            $config = $statusConfig[$status->status] ?? ['label' => $status->status, 'color' => 'rgba(156, 163, 175, 0.8)'];
            $labels[] = $config['label'];
            $data[] = $status->count;
            $colors[] = $config['color'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pedidos',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => array_map(fn($color) => str_replace('0.8)', '1)', $color), $colors),
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
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
        ];
    }
}
