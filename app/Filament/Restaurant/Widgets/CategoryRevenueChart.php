<?php

namespace App\Filament\Restaurant\Widgets;

use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CategoryRevenueChart extends ChartWidget
{
    protected static ?string $heading = '🍕 Faturamento por Categoria (Últimos 30 dias)';
    protected static ?int $sort = 7;
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Busca faturamento por categoria (apenas pedidos pagos)
        $categoryRevenue = OrderItem::query()
            ->select('categories.name', DB::raw('SUM(order_items.quantity * order_items.unit_price) as revenue'))
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid')
            ->whereDate('orders.created_at', '>=', now()->subDays(30))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $labels = $categoryRevenue->pluck('name')->toArray();
        $revenues = $categoryRevenue->pluck('revenue')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Faturamento (R$)',
                    'data' => $revenues,
                    'backgroundColor' => [
                        'rgba(234, 29, 44, 0.8)',
                        'rgba(255, 165, 0, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(20, 184, 166, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(107, 114, 128, 0.8)',
                    ],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                ],
            ],
        ];
    }
}
