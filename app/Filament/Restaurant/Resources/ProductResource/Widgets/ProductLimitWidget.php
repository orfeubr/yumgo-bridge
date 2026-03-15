<?php

namespace App\Filament\Restaurant\Resources\ProductResource\Widgets;

use Filament\Widgets\Widget;

class ProductLimitWidget extends Widget
{
    protected static string $view = 'filament.restaurant.widgets.product-limit-widget';

    protected int | string | array $columnSpan = 'full';

    public function getUsageData(): array
    {
        $tenant = tenancy()->tenant;
        $stats = $tenant->usageStats();
        $plan = $tenant->activeSubscription()?->plan;

        return [
            'current' => $stats['products']['current'],
            'limit' => $stats['products']['limit'],
            'percentage' => $stats['products']['percentage'],
            'plan_name' => $plan?->name ?? 'Sem plano',
            'can_create' => $tenant->canCreateProduct(),
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
        // ⚠️ CORREÇÃO: canView() é chamado ANTES dos middlewares
        // Não executar se tenancy não estiver inicializado
        if (!tenancy()->initialized) {
            return false;
        }

        $widget = new static();
        return $widget->shouldShowWidget();
    }
}
