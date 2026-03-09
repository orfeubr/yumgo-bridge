<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Compartilhar platformSettings com TODAS as views
        View::composer('*', function ($view) {
            $platformSettings = (object)[
                'platform_name' => config('app.name', 'YumGo'),
                'platform_logo' => file_exists(public_path('logo.png')) ? true : false,
            ];

            $view->with('platformSettings', $platformSettings);
        });
    }
}
