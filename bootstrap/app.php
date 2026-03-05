<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/tenant.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        // Middleware customizado temporário para auth
        $middleware->alias([
            'temp.auth' => \App\Http\Middleware\TempAuthMiddleware::class,
            'block.card.data' => \App\Http\Middleware\BlockSensitiveCardData::class, // 🔒 Segurança PCI-DSS
        ]);

        // Exceções CSRF para webhooks
        $middleware->validateCsrfTokens(except: [
            'api/webhooks/*',
            'api/v1/webhooks/*',
        ]);

        // Aplicar middlewares de tenancy globalmente no grupo 'web'
        // EXCETO para domínios centrais
        $middleware->web(prepend: [
            \App\Http\Middleware\InitializeTenancyByDomainOrSkip::class,
        ]);

        // Tenancy middleware priority (when used)
        $middleware->priority([
            \App\Http\Middleware\InitializeTenancyByDomainOrSkip::class,
            // \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class, // DESABILITADO - usando apenas o customizado
            // \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class, // DESABILITADO
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 🔥 Flare - Error Monitoring (Spatie)
        \Spatie\LaravelFlare\Facades\Flare::handles($exceptions);

        // Tratar erro quando tenant não existe
        $exceptions->render(function (\Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException $e, $request) {
            return response()->view('errors.tenant-not-found', [
                'domain' => $request->getHost(),
            ], 404);
        });

        // 🔒 SEGURANÇA: Não expor stack traces em APIs (produção)
        $exceptions->render(function (\Throwable $e, $request) {
            // Se for request de API e não estiver em modo debug
            if ($request->is('api/*') && !config('app.debug')) {
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

                // Mensagens seguras sem expor internals
                $message = match($statusCode) {
                    429 => 'Muitas tentativas. Aguarde alguns minutos.',
                    401 => 'Não autenticado.',
                    403 => 'Acesso negado.',
                    404 => 'Recurso não encontrado.',
                    422 => 'Dados inválidos.',
                    default => 'Erro no servidor. Tente novamente.'
                };

                return response()->json([
                    'message' => $message,
                    'status' => $statusCode,
                ], $statusCode);
            }

            // Se for ambiente de desenvolvimento, mostrar detalhes
            return null; // Laravel vai usar o handler padrão
        });
    })->create();
