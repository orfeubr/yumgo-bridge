<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // CSP: Permitir WebSocket, scripts, styles, etc
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://assets.pagar.me https://unpkg.com https://static.cloudflareinsights.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data: https:",
            "connect-src 'self' https://api.pagar.me https://cloudflareinsights.com wss://ws.yumgo.com.br wss://yumgo.com.br ws://localhost:8081", // ⭐ WebSocket URLs!
        ]);

        \Log::info('🔒 CSP Middleware executado', [
            'url' => $request->fullUrl(),
            'csp_length' => strlen($csp),
            'has_wss' => str_contains($csp, 'wss://'),
        ]);

        $response->headers->set('Content-Security-Policy', $csp);

        // 🔍 DEBUG: Header adicional para confirmar que Laravel está enviando correto
        $response->headers->set('X-CSP-Has-WebSocket', str_contains($csp, 'wss://') ? 'YES' : 'NO');
        $response->headers->set('X-CSP-Length', strlen($csp));

        return $response;
    }
}
