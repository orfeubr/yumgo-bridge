<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\Product;
use App\Models\Category;
use App\Observers\OrderFiscalObserver;
use App\Observers\OrderPrintObserver;
use App\Observers\TenantObserver;
use App\Observers\TenantRecipientObserver;
use App\Observers\ProductObserver;
use App\Observers\CategoryObserver;

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

        // Registrar observer para criar recebedores Pagar.me automaticamente
        Tenant::observe(TenantRecipientObserver::class);

        // Registrar observer para emissão automática de NFC-e
        Order::observe(OrderFiscalObserver::class);

        // Registrar observer para impressão automática de pedidos
        Order::observe(OrderPrintObserver::class);

        // Registrar observer para otimizar imagens e validar ordem de produtos
        Product::observe(ProductObserver::class);

        // Registrar observer para validar ordem de categorias
        Category::observe(CategoryObserver::class);

        // Compartilhar settings da plataforma em views do marketplace
        \Illuminate\Support\Facades\View::composer(
            ['marketplace.*', 'welcome'],
            \App\View\Composers\PlatformSettingsComposer::class
        );
    }
}
