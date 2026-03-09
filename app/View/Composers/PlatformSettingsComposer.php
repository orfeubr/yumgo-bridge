<?php

namespace App\View\Composers;

use App\Models\PlatformSetting;
use Illuminate\View\View;

class PlatformSettingsComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $platformSettings = [
            'platform_name' => PlatformSetting::get('platform_name', 'YumGo'),
            'platform_logo' => PlatformSetting::get('logo'),
            'platform_favicon' => PlatformSetting::get('favicon'),
            'platform_primary_color' => PlatformSetting::get('primary_color', '#EA1D2C'),
        ];

        $view->with('platformSettings', (object) $platformSettings);
    }
}
