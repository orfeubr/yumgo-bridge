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
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net https://cdn.tailwindcss.com",
            "style-src-elem 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net https://cdn.tailwindcss.com", // ⭐ Para <link> de fonts
            "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net",
            "img-src 'self' data: blob: https:", // ⭐ blob: para preview de uploads
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
        $response->headers->set('X-CSP-Version', '2.0-blob-fonts'); // ⭐ Força CloudFlare limpar cache

        // ⭐ Força CloudFlare a não cachear esse header
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate', false);
        $response->headers->set('Pragma', 'no-cache', false);

        return $response;
    }
}
