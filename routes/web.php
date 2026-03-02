<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

// Teste simples
Route::get('/test-central', function () {
    return response()->json([
        'message' => 'Central route working!',
        'host' => request()->getHost(),
        'is_central' => request()->getHost() === 'food.yumgo.com.br',
        'tenancy_initialized' => tenancy()->initialized ?? 'unknown',
    ]);
});

// Página inicial - Marketplace OU cardápio do tenant
Route::get('/', function (Illuminate\Http\Request $request) {
    $centralDomains = config('tenancy.central_domains');
    $currentHost = $request->getHost();

    \Log::info('ROOT ROUTE - Processando requisição', [
        'host' => $currentHost,
        'central_domains' => $centralDomains,
        'is_central' => in_array($currentHost, $centralDomains),
    ]);

    // Se for domínio central, mostra marketplace de restaurantes
    if (in_array($currentHost, $centralDomains)) {
        return app(\App\Http\Controllers\MarketplaceController::class)->index($request);
    }

    // Se for domínio de tenant, inicializa tenancy e mostra cardápio
    $domain = \Stancl\Tenancy\Database\Models\Domain::where('domain', $currentHost)->first();

    if (!$domain) {
        \Log::warning('Domínio não encontrado, retornando 404', ['host' => $currentHost]);
        abort(404, 'Restaurante não encontrado');
    }

    // Inicializa tenancy
    tenancy()->initialize($domain->tenant);

    // Chama o controller do restaurante
    return app(\App\Http\Controllers\RestaurantHomeController::class)->index();
});

// Página de planos e preços
Route::get('/planos', [\App\Http\Controllers\MarketplaceController::class, 'pricing'])
    ->name('pricing');

// Página seja parceiro (redireciona para planos por enquanto)
Route::get('/parceiro', function () {
    return redirect('/planos');
})->name('partner');

// Rota para lista de restaurantes (legacy - redireciona para home)
Route::get('/restaurantes', function () {
    return redirect('/');
})->name('restaurants.list');

Route::get('/test-deliverypro', function () {
    return response()->json([
        'project' => 'DeliveryPro',
        'app_name' => config('app.name'),
        'path' => base_path(),
        'database' => config('database.default'),
        'packages' => [
            'filament' => class_exists('Filament\\Filament') ? 'installed' : 'not installed',
            'tenancy' => class_exists('Stancl\\Tenancy\\Tenancy') ? 'installed' : 'not installed',
        ],
    ]);
});

// FALLBACK: Rota POST para login quando JavaScript não funciona
Route::post('/admin/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $credentials = [
        'email' => $request->input('email'),
        'password' => $request->input('password'),
    ];

    $remember = $request->boolean('remember', false);

    if (Auth::guard('platform')->attempt($credentials, $remember)) {
        $request->session()->regenerate();
        return redirect()->intended('/admin');
    }

    throw ValidationException::withMessages([
        'email' => 'As credenciais fornecidas não correspondem aos nossos registros.',
    ]);
})->name('admin.login.fallback');

Route::get('/debug-tenant', function () {
    $info = [
        'domain' => request()->getHost(),
        'tenancy_initialized' => tenancy()->initialized,
    ];

    if (tenancy()->initialized) {
        $tenant = tenant();
        $info['tenant_id'] = $tenant->id;
        $info['tenant_name'] = $tenant->name;
        $info['users_count'] = \App\Models\User::count();
        $info['users'] = \App\Models\User::select('id', 'email', 'name')->get();
    }

    return response()->json($info, 200, [], JSON_PRETTY_PRINT);
});

// ========================================
// 🔔 WEBHOOK CENTRAL - ASAAS
// ========================================
// Recebe webhooks de TODOS os restaurantes
// Não precisa de tenancy inicializado
Route::post('/api/webhooks/asaas', [\App\Http\Controllers\CentralWebhookController::class, 'asaas'])
    ->name('webhooks.asaas.central');

// Webhook para eventos de CONTA (aprovação/rejeição)
Route::post('/api/webhooks/asaas/account', [\App\Http\Controllers\CentralWebhookController::class, 'asaasAccountWebhook'])
    ->name('webhooks.asaas.account');

// ===== PAGAR.ME WEBHOOKS (Principal) =====
// Webhook global Pagar.me
Route::post('/api/webhooks/pagarme', [\App\Http\Controllers\PagarMeWebhookController::class, 'handle'])
    ->name('webhooks.pagarme');

// Webhooks específicos por método
Route::post('/api/webhooks/pagarme/pix', [\App\Http\Controllers\PagarMeWebhookController::class, 'pix'])
    ->name('webhooks.pagarme.pix');

Route::post('/api/webhooks/pagarme/card', [\App\Http\Controllers\PagarMeWebhookController::class, 'card'])
    ->name('webhooks.pagarme.card');

// Teste do webhook (GET)
Route::get('/api/webhooks/asaas/test', function () {
    return response()->json([
        'status' => 'OK',
        'message' => '✅ Webhook Central está acessível!',
        'url' => url('/api/webhooks/asaas'),
        'method' => 'POST',
        'events' => [
            'PAYMENT_RECEIVED',
            'PAYMENT_CONFIRMED',
            'PAYMENT_OVERDUE',
            'PAYMENT_DELETED',
        ],
        'info' => 'Configure esta URL no painel do Asaas (Webhooks)',
    ]);
});

// Rotas de teste
Route::get('/test-login', function () {
    return view('test-tenant-login');
});

Route::post('/test-login', function (Illuminate\Http\Request $request) {
    $credentials = $request->only('email', 'password');
    
    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect('/test-login')->with('success', 'Login OK! User: ' . Auth::user()->name);
    }
    
    return back()->with('error', 'Credenciais incorretas');
});

Route::post('/test-logout', function (Illuminate\Http\Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/test-login')->with('success', 'Logout OK');
});

// Teste de login direto (sem Livewire)
Route::get('/test-painel-login', function () {
    return view('test-painel-login');
});

Route::post('/test-painel-login', function (Illuminate\Http\Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $credentials = [
        'email' => $request->input('email'),
        'password' => $request->input('password'),
    ];

    \Log::info('=== TEST PAINEL LOGIN ===', [
        'email' => $credentials['email'],
        'tenant_initialized' => tenancy()->initialized,
        'session_before' => session()->getId(),
    ]);

    if (Auth::guard('web')->attempt($credentials, true)) {
        $request->session()->regenerate();

        \Log::info('Login SUCCESS', [
            'user_id' => Auth::guard('web')->user()->id,
            'session_after' => session()->getId(),
        ]);

        return redirect('/painel');
    }

    return back()->withErrors(['email' => 'Credenciais inválidas']);
});

// PWA Manifest dinâmico (usa logo do restaurante)
Route::get('/manifest.json', function () {
    $settings = \App\Models\Settings::first();
    $tenant = tenant();

    $logoUrl = $settings && $settings->logo
        ? route('stancl.tenancy.asset', ['path' => $settings->logo])
        : asset('favicon.ico');

    $manifest = [
        'name' => $settings->restaurant_name ?? $tenant->name ?? config('app.name'),
        'short_name' => $settings->restaurant_name ?? $tenant->name ?? config('app.name'),
        'description' => 'Peça delivery online',
        'start_url' => '/',
        'display' => 'standalone',
        'background_color' => '#ffffff',
        'theme_color' => '#EA1D2C',
        'orientation' => 'portrait',
        'icons' => [
            [
                'src' => $logoUrl,
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any maskable'
            ],
            [
                'src' => $logoUrl,
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any maskable'
            ]
        ]
    ];

    return response()->json($manifest);
});

// Service Worker
Route::get('/sw.js', function () {
    return response()->file(public_path('sw.js'), [
        'Content-Type' => 'application/javascript',
        'Service-Worker-Allowed' => '/'
    ]);
});

// Diagnóstico completo de sessão e cookies
Route::get('/diagnostico-sessao', function (Illuminate\Http\Request $request) {
    $sessionDriver = config('session.driver');
    $sessionDomain = config('session.domain');
    $sessionCookie = config('session.cookie');

    $info = [
        'dominio_atual' => $request->getHost(),
        'url_completa' => $request->fullUrl(),
        'tenant_inicializado' => tenancy()->initialized,
        'tenant_id' => tenancy()->initialized ? tenant('id') : null,
        'tenant_name' => tenancy()->initialized ? tenant('name') : null,

        'configuracao_sessao' => [
            'driver' => $sessionDriver,
            'domain' => $sessionDomain ?: '(vazio)',
            'cookie_name' => $sessionCookie,
            'lifetime' => config('session.lifetime'),
            'secure' => config('session.secure'),
            'same_site' => config('session.same_site'),
        ],

        'cookies_recebidos' => $request->cookies->all(),
        'session_id' => $request->session()->getId(),
        'session_data' => $request->session()->all(),
        'csrf_token' => csrf_token(),

        'headers' => [
            'user-agent' => $request->header('User-Agent'),
            'referer' => $request->header('Referer'),
            'cookie' => $request->header('Cookie'),
        ],
    ];

    return response()->json($info, 200, [], JSON_PRETTY_PRINT);
});

// 🔧 DEBUG: Rota de teste simples
Route::get('/debug-test', function () {
    return response()->json([
        'success' => true,
        'message' => 'Rota web funcionando!',
        'php_version' => PHP_VERSION,
        'memory' => memory_get_usage(true) / 1024 / 1024 . ' MB',
    ]);
});

// 🔧 DEBUG: Teste com banco de dados (verifica contexto do tenant)
Route::get('/debug-db', function () {
    try {
        $info = [
            'host' => request()->getHost(),
            'tenancy_initialized' => tenancy()->initialized,
        ];

        if (tenancy()->initialized) {
            $tenant = tenant();
            $info['tenant_id'] = $tenant->id;
            $info['tenant_name'] = $tenant->name;
            $info['customer_count'] = \App\Models\Customer::count();
        } else {
            // Se não estiver no contexto de tenant, lista tenants do PUBLIC schema
            $info['tenant_name'] = 'CENTRAL (sem tenant inicializado)';
            $info['all_tenants'] = \App\Models\Tenant::pluck('name', 'id');
        }

        return response()->json($info);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});

// 🔐 ADMINER - Gerenciador de Banco de Dados (APENAS PAINEL ADMIN CENTRAL)
Route::get('/admin/database', [\App\Http\Controllers\AdminerController::class, 'index'])
    ->middleware(['web'])
    ->name('admin.database');

// 🔥 FLARE - Teste de Error Monitoring
Route::get('/test-flare', function () {
    // Adiciona contexto antes do erro (usando helper flare())
    if (function_exists('flare')) {
        flare()->context('test_mode', true);
        flare()->context('environment', config('app.env'));

        if (tenancy()->initialized) {
            $tenant = tenant();
            flare()->context('tenant_id', $tenant->id);
            flare()->context('tenant_slug', $tenant->slug);
            flare()->context('tenant_name', $tenant->name);
        }
    }

    throw new \Exception('🔥 Teste Flare - Multi-Tenant Error Monitoring (' . (tenancy()->initialized ? tenant('name') : 'Central') . ')');
});
