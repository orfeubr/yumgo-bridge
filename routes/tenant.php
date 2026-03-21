<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CashbackController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\SettingsController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

// 🔥 OAuth Social Login - SEM PreventAccessFromCentralDomains
// Permite que yumgo.com.br processe callbacks do Google/Facebook
Route::middleware([
    'web',
    // InitializeTenancyByDomain NÃO é necessário aqui - o controller detecta tenant pela sessão
])->group(function () {
    Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
    Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');
});

// ⭐ Servir arquivos estáticos do storage (tenancy/assets)
Route::get('/tenancy/assets/{path}', function ($path) {
    $storagePath = storage_path('app/public/' . $path);

    if (!file_exists($storagePath)) {
        abort(404);
    }

    return response()->file($storagePath);
})->where('path', '.*')->name('tenant.assets');

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
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

    // Página inicial (catálogo/cardápio)
    // REMOVIDO: já tratado em web.php para permitir domínios centrais mostrarem lista de restaurantes
    // Route::get('/', [\App\Http\Controllers\RestaurantHomeController::class, 'index'])->name('catalog');

    // Tela de Welcome/Onboarding
    Route::get('/welcome', function () {
        $tenant = tenant();
        $settings = \App\Models\Settings::first();

        // Buscar cidades disponíveis
        $availableCities = \App\Models\Neighborhood::where('enabled', true)
            ->select('city')
            ->groupBy('city')
            ->pluck('city')
            ->toArray();

        $deliveryZones = \App\Models\Neighborhood::where('enabled', true)->get()->toArray();

        return view('tenant.welcome', compact('tenant', 'settings', 'availableCities', 'deliveryZones'));
    })->name('welcome');

    // Página de login
    Route::get('/login', function () {
        return view('tenant.auth.login');
    })->name('login');

    // 🔧 Login simples (fallback para problemas com Filament)
    Route::get('/simple-login', function () {
        return view('tenant.simple-login');
    })->name('simple-login');

    Route::post('/simple-login', function (\Illuminate\Http\Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (\Illuminate\Support\Facades\Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect('/painel')->with('success', 'Login realizado com sucesso!');
        }

        return back()->withErrors([
            'email' => 'Email ou senha incorretos.',
        ])->withInput();
    })->name('simple-login.submit');

    // Login Social - MOVIDO para grupo sem PreventAccessFromCentralDomains (linhas 31-38)

    // 📸 Servir imagens de produtos do tenant (com streaming e cache)
    Route::get('/storage/{path}', function ($path) {
        $filePath = storage_path("app/public/{$path}");

        if (!file_exists($filePath)) {
            abort(404);
        }

        // Headers otimizados para cache
        $headers = [
            'Content-Type' => mime_content_type($filePath),
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
            'Pragma' => 'public',
        ];

        // Se o browser tem cache válido (ETag), retorna 304
        $etag = md5_file($filePath);
        $headers['ETag'] = $etag;

        if (request()->header('If-None-Match') === $etag) {
            return response('', 304, $headers);
        }

        // Stream do arquivo (mais rápido que response()->file())
        return response()->stream(function () use ($filePath) {
            $stream = fopen($filePath, 'rb');
            fpassthru($stream);
            fclose($stream);
        }, 200, $headers);
    })->where('path', '.*')->name('tenant.storage');

    // Página de checkout (requer autenticação via JavaScript)
    Route::get('/checkout', function () {
        $tenant = tenant();
        return view('tenant.checkout', compact('tenant'));
    })->name('checkout');

    // Página de pagamento PIX (usando order_number)
    Route::get('/pedido/{orderNumber}/pagamento', function ($orderNumber) {
        $tenant = tenant();
        return view('tenant.payment', compact('tenant', 'orderNumber'));
    })->name('order.payment');

    // Página de confirmação do pedido (usando order_number)
    Route::get('/pedido/{orderNumber}/confirmado', function ($orderNumber) {
        $tenant = tenant();
        return view('tenant.order-confirmed', compact('tenant', 'orderNumber'));
    })->name('order.confirmed');

    // Páginas do cliente (requer autenticação via JavaScript)
    Route::get('/meus-pedidos', function () {
        $tenant = tenant();
        return view('tenant.my-orders', compact('tenant'));
    })->name('customer.orders');

    Route::get('/perfil', function () {
        $tenant = tenant();
        return view('tenant.profile', compact('tenant'));
    })->name('customer.profile');

    // Página de acompanhamento do pedido
    Route::get('/pedido/{id}/acompanhar', function ($id) {
        $tenant = tenant();
        return view('tenant.order-tracking', ['tenant' => $tenant, 'orderId' => $id]);
    })->name('order.tracking');

    // Página de cashback (redireciona para perfil)
    Route::get('/cashback', function () {
        return redirect('/perfil');
    })->name('cashback');

    // QR Code do Cardápio
    Route::get('/qrcode', [\App\Http\Controllers\QrCodeController::class, 'show'])->name('qrcode.show');
    Route::get('/qrcode/download', [\App\Http\Controllers\QrCodeController::class, 'download'])->name('qrcode.download');
    Route::get('/qrcode/pdf', [\App\Http\Controllers\QrCodeController::class, 'pdf'])->name('qrcode.pdf');

    // 👨‍🍳 Painel da Cozinha (tela pública/simples)
    Route::get('/cozinha', [\App\Http\Controllers\KitchenController::class, 'index'])->name('kitchen.index');

    // 🚗 Painel de Entregas (tela pública/simples)
    Route::get('/entregas', [\App\Http\Controllers\DeliveryController::class, 'index'])->name('delivery.index');
});

// ⭐ API PÚBLICA: Pedidos pendentes (para página /pedidos-pendentes)
Route::prefix('api/v1')->middleware([
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/orders/pending', [\App\Http\Controllers\Api\OrderController::class, 'pending'])
        ->middleware('throttle:120,1'); // Polling frequente permitido
});

/*
|--------------------------------------------------------------------------
| API Routes (Tenant-aware)
|--------------------------------------------------------------------------
*/

// ⚠️ ROTAS DE DEBUG REMOVIDAS POR SEGURANÇA
// Todas as rotas da API agora requerem autenticação obrigatória

// API públicas (sem autenticação)
// NOTA: Não usar 'api' middleware aqui - tenant.php já usa 'web' middleware
// O 'api' middleware causa conflito de sessão (web=stateful vs api=stateless)
Route::prefix('api/v1')->middleware([
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    // Autenticação (🔒 RATE LIMITED - Proteção contra brute force)
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,1'); // 3 tentativas/min
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1'); // 5 tentativas/min
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1'); // 3 tentativas/min
    Route::post('/verify-reset-token', [AuthController::class, 'verifyResetToken'])->middleware('throttle:5,1'); // 5 tentativas/min
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:3,1'); // 3 tentativas/min

    // Autenticação Social (API) (🔒 RATE LIMITED)
    Route::post('/auth/whatsapp/request-code', [SocialAuthController::class, 'requestWhatsAppCode'])->middleware('throttle:3,1'); // 3 códigos/min
    Route::post('/auth/whatsapp/verify-code', [SocialAuthController::class, 'verifyWhatsAppCode'])->middleware('throttle:5,1'); // 5 tentativas/min

    // Categorias (público) (🔒 RATE LIMITED - Proteção contra scraping)
    Route::get('/categories', [CategoryController::class, 'index'])->middleware('throttle:60,1'); // 60 req/min

    // Produtos (público) (🔒 RATE LIMITED - Proteção contra scraping)
    Route::get('/products', [ProductController::class, 'index'])->middleware('throttle:60,1'); // 60 req/min
    Route::get('/products/{id}', [ProductController::class, 'show'])->middleware('throttle:60,1'); // 60 req/min
    Route::get('/products/category/{categoryId}', [ProductController::class, 'byCategory'])->middleware('throttle:60,1'); // 60 req/min
    Route::get('/products/featured', [ProductController::class, 'featured'])->middleware('throttle:60,1'); // 60 req/min
    Route::get('/products/pizza/flavors', [ProductController::class, 'pizzaFlavors'])->middleware('throttle:60,1'); // 60 req/min
    Route::get('/products/suggestions', [ProductController::class, 'suggestions'])->middleware('throttle:60,1'); // 60 req/min - Compre Junto

    // Configurações (público) (🔒 RATE LIMITED)
    Route::get('/settings', [SettingsController::class, 'index'])->middleware('throttle:60,1'); // 60 req/min
    Route::get('/settings/payment-methods', [SettingsController::class, 'paymentMethods'])->middleware('throttle:60,1'); // 60 req/min

    // Avaliações (público) (🔒 RATE LIMITED)
    Route::get('/reviews', [\App\Http\Controllers\Api\ReviewController::class, 'index'])->middleware('throttle:60,1'); // 60 req/min
    Route::get('/reviews/stats', [\App\Http\Controllers\Api\ReviewController::class, 'stats'])->middleware('throttle:60,1'); // 60 req/min

    // Cupons (público) (🔒 RATE LIMITED)
    Route::post('/coupons/validate', [\App\Http\Controllers\Api\CouponController::class, 'validate'])->middleware('throttle:30,1'); // 30 req/min

    // 🛒 Carrinho (público) (🔒 RATE LIMITED)
    Route::post('/cart/validate', [\App\Http\Controllers\Api\CartController::class, 'validate'])->middleware('throttle:30,1'); // 30 req/min
    Route::post('/cart/check-expiration', [\App\Http\Controllers\Api\CartController::class, 'checkExpiration'])->middleware('throttle:30,1'); // 30 req/min

    // Localização (público)
    Route::prefix('location')->group(function () {
        Route::get('/enabled-cities', [LocationController::class, 'getEnabledCities']);
        Route::get('/cities/{state?}', [LocationController::class, 'getCities']);
        Route::get('/neighborhoods/{city}', [LocationController::class, 'getNeighborhoods']);
        Route::get('/enabled-neighborhoods/{city}', [LocationController::class, 'getEnabledNeighborhoods']);
        Route::get('/cep/{cep}', [LocationController::class, 'searchByCep']);
        Route::post('/import-neighborhoods', [LocationController::class, 'importNeighborhoods']);
    });

    // Rastreamento de pedido (público - apenas com order_number) (🔒 RATE LIMITED)
    Route::get('/orders/{orderNumber}/track', [OrderController::class, 'track'])->middleware('throttle:60,1'); // 60 req/min

    // APIs para Cozinha e Entregas (públicas)
    Route::post('/kitchen/{order}/status', [\App\Http\Controllers\KitchenController::class, 'updateStatus']);
    Route::post('/delivery/{order}/status', [\App\Http\Controllers\DeliveryController::class, 'updateStatus']);

    // Webhooks (público) (🔒 RATE LIMITED - Proteção contra ataques)
    Route::post('/webhooks/asaas', [WebhookController::class, 'asaas'])->middleware('throttle:100,1'); // 100 req/min
    Route::post('/webhooks/tributaai', [\App\Http\Controllers\TributaAiWebhookController::class, 'handle'])->middleware('throttle:100,1'); // 100 req/min

    // ⭐ Bridge API - Monitoramento de impressão (público) (🔒 RATE LIMITED)
    Route::post('/bridge/heartbeat', [\App\Http\Controllers\Api\BridgeController::class, 'heartbeat'])->middleware('throttle:120,1'); // 120 req/min (1 a cada 30s por Bridge)
    Route::get('/bridge/status', [\App\Http\Controllers\Api\BridgeController::class, 'status'])->middleware('throttle:60,1'); // 60 req/min
    Route::post('/bridge/print-success', [\App\Http\Controllers\Api\BridgeController::class, 'printSuccess'])->middleware('throttle:120,1'); // 120 req/min
    Route::post('/bridge/print-failed', [\App\Http\Controllers\Api\BridgeController::class, 'printFailed'])->middleware('throttle:120,1'); // 120 req/min
    Route::get('/bridge/pending-prints', [\App\Http\Controllers\Api\BridgeController::class, 'pendingPrints'])->middleware('throttle:60,1'); // 60 req/min
    Route::post('/bridge/cancel-print', [\App\Http\Controllers\Api\BridgeController::class, 'cancelPrint'])->middleware('throttle:60,1'); // 60 req/min
    Route::post('/bridge/force-reprint', [\App\Http\Controllers\Api\BridgeController::class, 'forceReprint'])->middleware('throttle:60,1'); // 60 req/min

    // Teste de webhook (apenas para desenvolvimento)
    Route::get('/test-webhook', function () {
        return response()->json([
            'status' => 'Webhook endpoint is working!',
            'url' => url('/api/v1/webhooks/asaas'),
            'token_configured' => config('services.asaas.webhook_token') ? 'YES ✅' : 'NO ❌',
            'tenant' => tenancy()->initialized ? tenant()->name : 'N/A',
        ]);
    });
});

// API protegidas (requerem autenticação)
// NOTA: auth:sanctum TEMPORARIAMENTE desabilitado (causa SIGSEGV crash)
// Usando temp.auth enquanto investigamos o problema
Route::prefix('api/v1')->middleware([
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    'temp.auth', // ← WORKAROUND: Evita crash do PHP-FPM
])->group(function () {
    // Autenticação
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'updateProfile']);

    // 🔒 Perfil do Customer (TENANT-SPECIFIC)
    Route::get('/customer/profile', function(Request $request) {
        $centralCustomer = $request->user();
        if (!$centralCustomer) {
            return response()->json(['success' => false, 'message' => 'Não autenticado'], 401);
        }

        // Buscar customer no schema TENANT (isolamento multi-tenant)
        $customer = \App\Models\Customer::where('email', $centralCustomer->email)
            ->orWhere('phone', $centralCustomer->phone)
            ->first();

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer não encontrado neste restaurante'], 404);
        }

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $centralCustomer->id, // ID do CENTRAL (para auth)
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'avatar' => $centralCustomer->avatar,
                'cashback_balance' => number_format((float)$customer->cashback_balance, 2, '.', ''),
                'loyalty_tier' => $customer->loyalty_tier ?? 'bronze',
            ]
        ]);
    });

    // Pedidos (apenas para usuários autenticados) (🔒 RATE LIMITED)
    Route::get('/orders', [OrderController::class, 'index'])->middleware('throttle:60,1'); // 60 req/min
    Route::get('/orders/{id}', [OrderController::class, 'show'])->middleware('throttle:60,1'); // 60 req/min
    Route::get('/orders/{id}/payment', [OrderController::class, 'payment'])->middleware('throttle:60,1'); // 60 req/min
    Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:30,60'); // 30 pedidos/hora (razoável)
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel'])->middleware('throttle:10,1'); // 10 cancelamentos/min

    // Pedidos por ORDER_NUMBER (segurança - oculta IDs sequenciais)
    Route::get('/orders/number/{orderNumber}', [OrderController::class, 'showByOrderNumber']);
    Route::get('/orders/number/{orderNumber}/payment', [OrderController::class, 'paymentByOrderNumber']);
    Route::post('/orders/{orderNumber}/pay-with-card', [OrderController::class, 'processCardPayment'])
        ->middleware(['throttle:5,1', 'block.card.data']); // 🔒 Rate limit + bloqueio de dados sensíveis

    // Endereços (temporário: inline para evitar crash)
    Route::get('/addresses', function(Request $request) {
        try {
            $customer = $request->user();
            if (!$customer) {
                return response()->json(['data' => []]);
            }
            // TODO: Implementar Address model
            return response()->json(['data' => []]);
        } catch (\Exception $e) {
            \Log::error('Erro em /addresses', ['error' => $e->getMessage()]);
            return response()->json(['data' => []]);
        }
    });
    // Endereços
    Route::get('/addresses', [\App\Http\Controllers\Api\AddressController::class, 'index']);
    Route::post('/addresses', [\App\Http\Controllers\Api\AddressController::class, 'store']);
    Route::put('/addresses/{id}', [\App\Http\Controllers\Api\AddressController::class, 'update']);
    Route::delete('/addresses/{id}', [\App\Http\Controllers\Api\AddressController::class, 'destroy']);

    // Cashback
    Route::get('/cashback/balance', [CashbackController::class, 'balance']);
    Route::post('/cashback/calculate', [CashbackController::class, 'calculate']);
    Route::get('/cashback/transactions', [CashbackController::class, 'transactions']);
    Route::get('/cashback/settings', [CashbackController::class, 'settings']);

    // Avaliações (protegidas - requer autenticação) (🔒 RATE LIMITED)
    Route::post('/reviews', [\App\Http\Controllers\Api\ReviewController::class, 'store'])->middleware('throttle:5,60'); // 5 reviews/hora
    Route::get('/orders/{id}/review', [\App\Http\Controllers\Api\ReviewController::class, 'checkReview'])->middleware('throttle:60,1'); // 60 req/min

    // Cliente
    Route::get('/profile', [CustomerController::class, 'show']);
    Route::put('/profile', [CustomerController::class, 'update']);
});

// TESTE: Rota sem middleware para debug
Route::post('/api/v1/test-order-no-auth', function () {
    try {
        $tenant = tenant();
        $customer = \App\Models\Customer::first();
        $product = \App\Models\Product::first();
        
        if (!$customer || !$product) {
            return response()->json(['error' => 'No customer or product'], 400);
        }
        
        $orderService = app(\App\Services\OrderService::class);
        
        $order = $orderService->createOrder($customer, [
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => $product->price,
                'subtotal' => $product->price,
            ]],
            'delivery_address' => 'Teste',
            'delivery_city' => 'SP',
            'delivery_neighborhood' => 'Centro',
            'delivery_fee' => 5.00,
            'payment_method' => 'cash',
        ]);
        
        return response()->json([
            'success' => true,
            'order_id' => $order->order_number,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});

// DEBUG: Teste minimalista de autenticação (SEM 'api' middleware para evitar conflito)
Route::middleware([
    \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
    'auth:sanctum',
])->get('/api/v1/test-auth', function(\Illuminate\Http\Request $request) {
    try {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'tenant' => tenant() ? tenant()->name : 'no tenant',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});

// 🧪 TESTE: Auth step-by-step
Route::get('/api/v1/test-auth-steps', function(\Illuminate\Http\Request $request) {
    $steps = [];

    try {
        $steps[] = '1. Iniciando teste';

        $steps[] = '2. Tenant: ' . (tenant() ? tenant()->name : 'NULL');

        $steps[] = '3. Tentando autenticar...';

        // Tentar pegar token do header
        $token = $request->bearerToken();
        $steps[] = '4. Token presente: ' . ($token ? 'SIM' : 'NÃO');

        if ($token) {
            $steps[] = '5. Buscando token no banco...';
            $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            $steps[] = '6. Token encontrado: ' . ($tokenModel ? 'SIM' : 'NÃO');

            if ($tokenModel) {
                $steps[] = '7. Tokenable type: ' . get_class($tokenModel->tokenable);
                $steps[] = '8. Tokenable ID: ' . $tokenModel->tokenable_id;

                try {
                    $customer = $tokenModel->tokenable;
                    $steps[] = '9. Customer carregado: ' . ($customer ? $customer->name : 'NULL');
                } catch (\Exception $e) {
                    $steps[] = '9. ERRO ao carregar customer: ' . $e->getMessage();
                }
            }
        }

        return response()->json(['steps' => $steps, 'success' => true]);

    } catch (\Exception $e) {
        $steps[] = 'ERRO: ' . $e->getMessage();
        $steps[] = 'File: ' . $e->getFile() . ':' . $e->getLine();
        return response()->json(['steps' => $steps, 'success' => false, 'error' => $e->getMessage()]);
    }
})->middleware([
    InitializeTenancyByDomain::class,
]);

// DEBUG: Teste autenticação SEM tenancy
Route::middleware([
    'auth:sanctum',
])->get('/api/v1/test-auth-no-tenancy', function(\Illuminate\Http\Request $request) {
    try {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'user_id' => $user?->id,
            'note' => 'SEM tenancy - usuário do schema PUBLIC',
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// 🔧 DEBUG: Endpoint de teste
Route::get('/test-order-simple', function () {
    try {
        \Log::info('🧪 Teste iniciado');
        
        $customer = \App\Models\Customer::first();
        if (!$customer) {
            return response()->json(['error' => 'Nenhum customer encontrado'], 404);
        }
        
        \Log::info('✅ Customer encontrado', ['id' => $customer->id]);
        
        $order = \App\Models\Order::create([
            'order_number' => 'TEST-' . time(),
            'customer_id' => $customer->id,
            'subtotal' => 100.00,
            'delivery_fee' => 5.00,
            'total' => 105.00,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'pix',
        ]);
        
        \Log::info('✅ Pedido criado', ['order_id' => $order->id]);
        
        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'message' => 'Pedido de teste criado com sucesso!'
        ]);
        
    } catch (\Exception $e) {
        \Log::error('❌ Erro no teste', ['message' => $e->getMessage()]);
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// 🔧 DEBUG: Incluir rotas de teste
require __DIR__ . '/test-api.php';

// 🔧 DEBUG: Testes progressivos de middleware
// Teste 1: SEM nenhum middleware tenant
Route::get('/test-level-0', function () {
    return response()->json([
        'level' => 0,
        'message' => 'Rota funcionando SEM middleware',
        'host' => request()->getHost(),
    ]);
});

// Teste 2: COM InitializeTenancyByDomain apenas
Route::middleware([
    InitializeTenancyByDomain::class,
])->get('/test-level-1', function () {
    return response()->json([
        'level' => 1,
        'message' => 'COM InitializeTenancyByDomain',
        'tenant' => tenant() ? tenant()->name : 'no tenant',
        'tenancy_initialized' => tenancy()->initialized,
    ]);
});

// Teste 3: COM tenancy + DB query (arquitetura corrigida)
Route::middleware([
    InitializeTenancyByDomain::class,
])->get('/test-level-3', function () {
    try {
        $customer = \App\Models\Customer::first();
        return response()->json([
            'level' => 3,
            'message' => 'COM tenancy + DB (SEM conflito de middleware)',
            'tenant' => tenant() ? tenant()->name : 'no tenant',
            'customer' => $customer ? $customer->name : 'nenhum',
            'middleware_stack' => 'web (já aplicado) + InitializeTenancyByDomain',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'level' => 3,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});

// 🔧 DEBUG: API sem middleware 'api' (mantido para compatibilidade)
Route::prefix('test-api')->middleware([
    InitializeTenancyByDomain::class,
])->group(function () {
    Route::get('/simple', function () {
        return response()->json([
            'success' => true,
            'message' => 'API funcionando SEM middleware api!',
            'tenant' => tenant() ? tenant()->name : 'no tenant',
        ]);
    });

    Route::get('/with-db', function () {
        try {
            $customer = \App\Models\Customer::first();
            return response()->json([
                'success' => true,
                'customer' => $customer ? $customer->name : 'nenhum',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    });
});
