<?php

namespace App\Filament\Restaurant\Resources\OrderResource\Widgets;

use Filament\Widgets\Widget;

class OrderLimitWidget extends Widget
{
    protected static string $view = 'filament.restaurant.widgets.order-limit-widget';

    protected int | string | array $columnSpan = 'full';

    public function getUsageData(): array
    {
        $tenant = tenancy()->tenant;
        $stats = $tenant->usageStats();
        $plan = $tenant->activeSubscription()?->plan;

        return [
            'current' => $stats['orders_this_month']['current'],
            'limit' => $stats['orders_this_month']['limit'],
            'percentage' => $stats['orders_this_month']['percentage'],
            'plan_name' => $plan?->name ?? 'Sem plano',
            'can_create' => $tenant->canCreateOrder(),
        ];
    }

    public function shouldShowWidget(): bool
    {
        $data = $this->getUsageData();

        // Só mostra se tiver limite definido (não ilimitado)
        return $data['limit'] !== 'Ilimitado';
    }

    public static function canView(): bool
    {
        $widget = new static();
        return $widget->shouldShowWidget();
    }
}
