<?php

namespace App\Filament\Restaurant\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Restaurant\Widgets\RestaurantStatsWidget;
use App\Filament\Restaurant\Widgets\SalesRevenueChart;
use App\Filament\Restaurant\Widgets\OrdersChart;
use App\Filament\Restaurant\Widgets\LatestOrders;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            RestaurantStatsWidget::class,
            SalesRevenueChart::class,
            OrdersChart::class,
            LatestOrders::class,
        ];
    }
}
