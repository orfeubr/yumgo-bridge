<?php

namespace App\View\Composers;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PlatformSettingsComposer
{
    /**
     * Bind data to the view.
     *
     * ⚡ Cache por 1 hora para melhorar performance
     * Cache é limpo quando PlatformSetting é atualizado (usar observer)
     */
    public function compose(View $view): void
    {
        $platformSettings = Cache::remember('platform_settings', 3600, function () {
            return [
                'platform_name' => PlatformSetting::get('platform_name', 'YumGo'),
                'platform_logo' => PlatformSetting::get('logo'),
                'platform_favicon' => PlatformSetting::get('favicon'),
                'platform_primary_color' => PlatformSetting::get('primary_color', '#EA1D2C'),
            ];
        });

        $view->with('platformSettings', (object) $platformSettings);
    }
}
