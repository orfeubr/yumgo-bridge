<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;

class RestaurantPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('restaurant')
            ->path('painel')
            ->login(\App\Filament\Restaurant\Pages\Auth\Login::class)
            ->authGuard('web')
            ->brandName('Painel do Restaurante')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::Orange,
                'success' => Color::Green,
                'warning' => Color::Amber,
                'danger' => Color::Red,
            ])
            // Removido temporariamente - CSS causando 404
            // ->renderHook(
            //     'panels::styles.after',
            //     fn () => '<link rel="stylesheet" href="' . asset('css/restaurant-theme.css') . '">'
            // )
            ->discoverResources(in: app_path('Filament/Restaurant/Resources'), for: 'App\\Filament\\Restaurant\\Resources')
            ->discoverPages(in: app_path('Filament/Restaurant/Pages'), for: 'App\\Filament\\Restaurant\\Pages')
            ->discoverWidgets(in: app_path('Filament/Restaurant/Widgets'), for: 'App\\Filament\\Restaurant\\Widgets')
            ->widgets([
                \App\Filament\Restaurant\Widgets\SubscriptionLimitsWidget::class,
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                \App\Http\Middleware\InitializeTenancyByDomainOrSkip::class, // Deve executar ANTES de StartSession
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->databaseNotifications()
            ->sidebarCollapsibleOnDesktop()
            ->plugins([
                FilamentShieldPlugin::make()
            ])
            ->renderHook('panels::body.end', fn () => view('filament.hooks.csrf-refresh'));
    }
}
