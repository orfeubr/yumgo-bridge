<?php

namespace App\Filament\Pages;

use App\Filament\Admin\Widgets\LatestTenantsWidget;
use App\Filament\Admin\Widgets\RevenueChart;
use App\Filament\Admin\Widgets\StatsOverviewWidget;
use App\Filament\Admin\Widgets\SubscriptionDistributionChart;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            RevenueChart::class,
            LatestTenantsWidget::class,
            SubscriptionDistributionChart::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }
}
