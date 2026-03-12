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
        $this->clearTenantCache();
    }

    /**
     * Handle the Settings "updated" event.
     */
    public function updated(Settings $settings): void
    {
        $this->clearTenantCache();
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
