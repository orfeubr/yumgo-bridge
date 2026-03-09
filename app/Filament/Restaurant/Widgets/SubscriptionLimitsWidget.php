<?php

namespace App\Filament\Restaurant\Widgets;

use Filament\Widgets\Widget;

class SubscriptionLimitsWidget extends Widget
{
    protected static string $view = 'filament.restaurant.widgets.subscription-limits-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = -1; // Exibir primeiro

    /**
     * Verifica se deve exibir o widget
     */
    public static function canView(): bool
    {
        $tenant = tenancy()->tenant;

        if (!$tenant) {
            return false;
        }

        // Só exibe se tem assinatura ativa
        $subscription = $tenant->activeSubscription();

        return $subscription !== null;
    }

    /**
     * Obter estatísticas de uso
     */
    public function getStats(): array
    {
        $tenant = tenancy()->tenant;

        if (!$tenant) {
            return [];
        }

        return $tenant->usageStats();
    }

    /**
     * Obter informações da assinatura
     */
    public function getSubscriptionInfo(): array
    {
        $tenant = tenancy()->tenant;

        if (!$tenant) {
            return [];
        }

        $subscription = $tenant->activeSubscription();

        if (!$subscription) {
            return [];
        }

        return [
            'plan_name' => $subscription->plan->name,
            'status' => $subscription->status,
            'ends_at' => $subscription->ends_at,
        ];
    }
}
