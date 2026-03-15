<?php

namespace App\Models\Concerns;

use App\Models\Subscription;

trait HasSubscriptionLimits
{
    /**
     * Obter assinatura ativa do tenant
     */
    public function activeSubscription(): ?Subscription
    {
        return Subscription::where('tenant_id', $this->id)
            ->whereIn('status', ['active', 'trialing'])
            ->with('plan')
            ->first();
    }

    /**
     * Verificar se tem assinatura ativa
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }

    /**
     * Verificar se pode criar mais produtos
     */
    public function canCreateProduct(): bool
    {
        $subscription = $this->activeSubscription();

        if (!$subscription) {
            return false;
        }

        $plan = $subscription->plan;
        $maxProducts = $plan->max_products ?? null;

        // Se ilimitado (null), pode criar
        if ($maxProducts === null) {
            return true;
        }

        // ✅ SOLUÇÃO LIMPA: Usar Model (respeita tenancy automático)
        $wasInitialized = tenancy()->initialized;

        if (!$wasInitialized) {
            tenancy()->initialize($this);
        }

        $currentCount = \App\Models\Product::count();

        if (!$wasInitialized) {
            tenancy()->end();
        }

        return $currentCount < $maxProducts;
    }

    /**
     * Verificar se pode criar mais pedidos este mês
     */
    public function canCreateOrder(): bool
    {
        $subscription = $this->activeSubscription();

        if (!$subscription) {
            return false;
        }

        $plan = $subscription->plan;
        $maxOrders = $plan->max_orders_per_month ?? null;

        // Se ilimitado (null), pode criar
        if ($maxOrders === null) {
            return true;
        }

        // ✅ SOLUÇÃO LIMPA: Usar Model (respeita tenancy automático)
        $wasInitialized = tenancy()->initialized;

        if (!$wasInitialized) {
            tenancy()->initialize($this);
        }

        $currentCount = \App\Models\Order::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        if (!$wasInitialized) {
            tenancy()->end();
        }

        return $currentCount < $maxOrders;
    }

    /**
     * Verificar se tem acesso a uma feature específica
     */
    public function hasFeature(string $feature): bool
    {
        $subscription = $this->activeSubscription();

        if (!$subscription) {
            return false;
        }

        $plan = $subscription->plan;
        $features = $plan->features ?? [];

        // Features podem ser array simples ou associativo
        if (is_array($features)) {
            return in_array($feature, $features) || isset($features[$feature]);
        }

        return false;
    }

    /**
     * Obter nome do plano atual
     */
    public function planName(): string
    {
        $subscription = $this->activeSubscription();
        return $subscription?->plan->name ?? 'Sem plano';
    }

    /**
     * Obter limites do plano atual
     */
    public function planLimits(): array
    {
        $subscription = $this->activeSubscription();

        if (!$subscription) {
            return [
                'products' => 0,
                'orders_per_month' => 0,
                'features' => [],
            ];
        }

        $plan = $subscription->plan;

        return [
            'products' => $plan->max_products ?? 'Ilimitado',
            'orders_per_month' => $plan->max_orders_per_month ?? 'Ilimitado',
            'features' => $plan->features ?? [],
        ];
    }

    /**
     * Obter uso atual vs limites
     */
    public function usageStats(): array
    {
        $subscription = $this->activeSubscription();

        if (!$subscription) {
            return [
                'products' => ['current' => 0, 'limit' => 0, 'percentage' => 0],
                'orders_this_month' => ['current' => 0, 'limit' => 0, 'percentage' => 0],
            ];
        }

        // ✅ SOLUÇÃO LIMPA: Usar Models (respeitam tenancy automático)
        $wasInitialized = tenancy()->initialized;

        if (!$wasInitialized) {
            tenancy()->initialize($this);
        }

        $productsCount = \App\Models\Product::count();
        $ordersCount = \App\Models\Order::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        if (!$wasInitialized) {
            tenancy()->end();
        }

        $plan = $subscription->plan;
        $maxProducts = $plan->max_products ?? null;
        $maxOrders = $plan->max_orders_per_month ?? null;

        return [
            'products' => [
                'current' => $productsCount,
                'limit' => $maxProducts ?? 'Ilimitado',
                'percentage' => $maxProducts ? round(($productsCount / $maxProducts) * 100) : 0,
            ],
            'orders_this_month' => [
                'current' => $ordersCount,
                'limit' => $maxOrders ?? 'Ilimitado',
                'percentage' => $maxOrders ? round(($ordersCount / $maxOrders) * 100) : 0,
            ],
        ];
    }
}
