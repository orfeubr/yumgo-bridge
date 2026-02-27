<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\Product;
use App\Observers\OrderFiscalObserver;
use App\Observers\TenantObserver;
use App\Observers\ProductObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar observer para criar domínios automaticamente
        Tenant::observe(TenantObserver::class);

        // Registrar observer para emissão automática de NFC-e
        Order::observe(OrderFiscalObserver::class);

        // Registrar observer para otimizar imagens de produtos
        // Product::observe(ProductObserver::class); // Desabilitado temporariamente
    }
}
