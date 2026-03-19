<?php

namespace App\Observers;

use App\Models\Settings;
use Illuminate\Support\Facades\Cache;

/**
 * Observer para limpar cache quando Settings é alterado
 */
class SettingsObserver
{
    /**
     * Limpa cache do tenant
     */
    private function clearTenantCache(): void
    {
        if (tenancy()->initialized) {
            $tenantId = tenant('id');
            Cache::forget("tenant_{$tenantId}_common_data");
        }
    }

    /**
     * Handle the Settings "created" event.
     */
    public function created(Settings $settings): void
    {
        // ⭐ Sincronizar logo do marketplace com Tenant
        $this->syncTenantLogo($settings);

        $this->clearTenantCache();
    }

    /**
     * Handle the Settings "updated" event.
     */
    public function updated(Settings $settings): void
    {
        // ⭐ Sincronizar logo do marketplace com Tenant
        $this->syncTenantLogo($settings);

        $this->clearTenantCache();
    }

    /**
     * Sincroniza logo do marketplace (tenant_logo) com o Tenant
     */
    private function syncTenantLogo(Settings $settings): void
    {
        // Verifica se tenant_logo foi alterado
        if ($settings->wasChanged('tenant_logo') || $settings->wasRecentlyCreated) {
            $tenant = tenancy()->tenant;

            if ($tenant) {
                $tenant->logo = $settings->getAttribute('tenant_logo');
                $tenant->saveQuietly(); // Save sem disparar observers

                \Log::info('🔄 SettingsObserver: Logo sincronizado com Tenant', [
                    'tenant_id' => $tenant->id,
                    'logo' => $settings->getAttribute('tenant_logo') ?: 'NULL (removido)',
                ]);
            }
        }
    }

    /**
     * Handle the Settings "deleted" event.
     */
    public function deleted(Settings $settings): void
    {
        $this->clearTenantCache();
    }

    /**
     * Handle the Settings "restored" event.
     */
    public function restored(Settings $settings): void
    {
        $this->clearTenantCache();
    }

    /**
     * Handle the Settings "force deleted" event.
     */
    public function forceDeleted(Settings $settings): void
    {
        $this->clearTenantCache();
    }
}
