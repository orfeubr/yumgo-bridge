<?php

namespace App\View\Composers;

use App\Models\Settings;
use App\Models\Category;
use App\Models\Neighborhood;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * View Composer para cachear dados comuns do tenant
 *
 * Reduz queries em ~50% nas páginas mais acessadas
 * Cache: 1 hora (limpo automaticamente em observers)
 */
class TenantDataComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        // Verifica se tenancy está ativa
        if (!tenancy()->initialized) {
            return;
        }

        $tenantId = tenant('id');

        // Cache por 1 hora (3600 segundos)
        $cacheKey = "tenant_{$tenantId}_common_data";

        $tenantData = Cache::remember($cacheKey, 3600, function () {
            return [
                // Settings do restaurante
                'settings' => $this->getSettings(),

                // Categorias (para menu de navegação)
                'categories' => $this->getCategories(),

                // Zonas de entrega ativas
                'deliveryZones' => $this->getDeliveryZones(),

                // Status (aberto/fechado)
                'isOpen' => $this->getIsOpen(),

                // Timestamp do cache
                'cached_at' => now()->toDateTimeString(),
            ];
        });

        // ✅ Adicionar $tenantData sem sobrescrever variáveis do controller
        $viewData = ['tenantData' => (object) $tenantData];

        // ⚠️ FALLBACK: Se controller NÃO passou $settings, usar do cache
        // (alguns controllers já passam $settings com dados completos - não sobrescrever!)
        $existingData = $view->getData();
        if (!isset($existingData['settings'])) {
            $viewData['settings'] = $this->convertToSettingsObject($tenantData['settings']);
        }

        // ⚠️ FALLBACK: Se controller NÃO passou $categories, usar do cache
        // (RestaurantHomeController passa com eager loading - não sobrescrever!)
        if (!isset($existingData['categories'])) {
            $viewData['categories'] = collect($tenantData['categories']);
        }

        $view->with($viewData);
    }

    /**
     * Converte settings simplificado para objeto completo (compatibilidade)
     */
    private function convertToSettingsObject(?object $settings): ?object
    {
        if (!$settings) {
            return null;
        }

        // Converte de volta para formato que as views esperam
        return (object) [
            'restaurant_name' => $settings->name ?? null,
            'logo' => $settings->logo ?? null,
            'primary_color' => $settings->primary_color ?? '#EA1D2C',
            'phone' => $settings->phone ?? null,
            'email' => $settings->email ?? null,
            'address' => $settings->address ?? null,
            'min_order_value' => $settings->min_order_value ?? 0,
            'delivery_fee' => $settings->delivery_fee ?? 0,
        ];
    }

    /**
     * Busca settings do tenant
     */
    private function getSettings(): ?object
    {
        try {
            $settings = Settings::current();

            if (!$settings) {
                return null;
            }

            return (object) [
                'name' => $settings->restaurant_name,
                'logo' => $settings->logo,
                'primary_color' => $settings->primary_color ?? '#EA1D2C',
                'phone' => $settings->phone,
                'email' => $settings->email,
                'address' => $settings->address,
                'min_order_value' => $settings->min_order_value ?? 0,
                'delivery_fee' => $settings->delivery_fee ?? 0,
            ];
        } catch (\Exception $e) {
            \Log::warning('TenantDataComposer: Erro ao buscar settings', [
                'tenant' => tenant('id'),
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Busca categorias ativas
     * Retorna array (para ser cacheável) - será convertido para Collection na view
     */
    private function getCategories(): array
    {
        try {
            return Category::query()
                ->select('id', 'name', 'slug', 'icon')
                ->orderBy('order')
                ->get()
                ->map(fn($cat) => (object) [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'icon' => $cat->icon,
                ])
                ->toArray();
        } catch (\Exception $e) {
            \Log::warning('TenantDataComposer: Erro ao buscar categorias', [
                'tenant' => tenant('id'),
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Busca zonas de entrega ativas
     */
    private function getDeliveryZones(): array
    {
        try {
            return Neighborhood::query()
                ->where('enabled', true)
                ->select('id', 'name', 'delivery_fee')
                ->orderBy('name')
                ->get()
                ->map(fn($zone) => (object) [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'delivery_fee' => $zone->delivery_fee,
                ])
                ->toArray();
        } catch (\Exception $e) {
            // Tabela pode não existir em alguns tenants
            return [];
        }
    }

    /**
     * Verifica se restaurante está aberto
     */
    private function getIsOpen(): bool
    {
        try {
            $settings = Settings::current();

            if (!$settings) {
                return true; // Fallback: considerar aberto
            }

            // Se não tem horário configurado, considerar sempre aberto
            if (empty($settings->business_hours)) {
                return true;
            }

            // Lógica simplificada - pode ser melhorada depois
            $now = now();
            $dayOfWeek = strtolower($now->format('l')); // monday, tuesday, etc

            $hours = $settings->business_hours[$dayOfWeek] ?? null;

            if (!$hours || !$hours['is_open']) {
                return false;
            }

            $currentTime = $now->format('H:i');
            $openTime = $hours['open_time'] ?? '08:00';
            $closeTime = $hours['close_time'] ?? '22:00';

            return $currentTime >= $openTime && $currentTime <= $closeTime;

        } catch (\Exception $e) {
            return true; // Fallback: considerar aberto
        }
    }
}
