<?php

namespace App\Filament\Restaurant\Widgets;

use App\Models\Order;
use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopProductsChart extends ChartWidget
{
    protected static ?string $heading = '🏆 Top 10 Produtos Mais Vendidos';
    protected static ?int $sort = 5;
    protected static ?string $maxHeight = '350px';
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Busca os 10 produtos mais vendidos (apenas pedidos pagos)
        $topProducts = OrderItem::query()
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid')
            ->whereDate('orders.created_at', '>=', now()->subDays(30))
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->with('product')
            ->get();

        $labels = [];
        $quantities = [];
        $revenues = [];

        foreach ($topProducts as $item) {
            $product = $item->product;
            $labels[] = $product ? $product->name : 'N/A';
            $quantities[] = $item->total_quantity;

            // Calcula faturamento por produto (apenas pedidos pagos)
            $revenue = OrderItem::query()
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('order_items.product_id', $item->product_id)
                ->where('orders.payment_status', 'paid')
                ->whereDate('orders.created_at', '>=', now()->subDays(30))
                ->sum(DB::raw('order_items.quantity * order_items.unit_price'));

            $revenues[] = $revenue;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Quantidade Vendida',
                    'data' => $quantities,
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
                    'borderColor' => [
                        'rgba(234, 29, 44, 1)',
                        'rgba(255, 165, 0, 1)',
                        'rgba(34, 197, 94, 1)',
                        'rgba(59, 130, 246, 1)',
                        'rgba(168, 85, 247, 1)',
                        'rgba(236, 72, 153, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(20, 184, 166, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(107, 114, 128, 1)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y', // Barras horizontais
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
