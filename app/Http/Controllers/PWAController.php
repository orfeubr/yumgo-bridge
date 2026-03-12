<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PWAController extends Controller
{
    /**
     * Retorna manifest.json dinâmico baseado no tenant
     */
    public function manifest(Request $request)
    {
        $isTenant = tenancy()->initialized;

        if ($isTenant) {
            // PWA do Restaurante (Tenant)
            $tenant = tenant();
            $settings = \App\Models\Settings::first();

            $manifest = [
                'name' => $tenant->name . ' - Delivery',
                'short_name' => $tenant->name,
                'description' => $settings->description ?? 'Peça comida deliciosa com cashback!',
                'start_url' => '/',
                'display' => 'standalone',
                'background_color' => '#EA1D2C',
                'theme_color' => '#EA1D2C',
                'orientation' => 'portrait',
                'icons' => [
                    [
                        'src' => '/pwa-icon/192',
                        'sizes' => '192x192',
                        'type' => 'image/png',
                        'purpose' => 'any'
                    ],
                    [
                        'src' => '/pwa-icon/512',
                        'sizes' => '512x512',
                        'type' => 'image/png',
                        'purpose' => 'any maskable'
                    ]
                ],
                'categories' => ['food', 'shopping'],
                'shortcuts' => [
                    [
                        'name' => 'Meus Pedidos',
                        'url' => '/meus-pedidos',
                        'description' => 'Ver histórico de pedidos'
                    ],
                    [
                        'name' => 'Perfil',
                        'url' => '/perfil',
                        'description' => 'Ver perfil e cashback'
                    ]
                ]
            ];
        } else {
            // PWA Central (Marketplace)
            $manifest = [
                'name' => 'YumGo - Delivery com Cashback',
                'short_name' => 'YumGo',
                'description' => 'Encontre os melhores restaurantes e ganhe cashback em cada pedido!',
                'start_url' => '/',
                'display' => 'standalone',
                'background_color' => '#EA1D2C',
                'theme_color' => '#EA1D2C',
                'orientation' => 'portrait',
                'icons' => [
                    [
                        'src' => '/pwa-icon/192',
                        'sizes' => '192x192',
                        'type' => 'image/png',
                        'purpose' => 'any'
                    ],
                    [
                        'src' => '/pwa-icon/512',
                        'sizes' => '512x512',
                        'type' => 'image/png',
                        'purpose' => 'any maskable'
                    ]
                ],
                'categories' => ['food', 'shopping'],
                'shortcuts' => [
                    [
                        'name' => 'Restaurantes',
                        'url' => '/',
                        'description' => 'Ver todos os restaurantes'
                    ],
                    [
                        'name' => 'Cadastrar Restaurante',
                        'url' => '/cadastro',
                        'description' => 'Seja parceiro YumGo'
                    ]
                ]
            ];
        }

        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json');
    }

    /**
     * Retorna ícone PWA dinâmico (logo do tenant ou fallback)
     */
    public function icon(Request $request, $size)
    {
        $isTenant = tenancy()->initialized;

        // Tamanho válido
        if (!in_array($size, [192, 512])) {
            abort(404);
        }

        if ($isTenant) {
            $tenant = tenant();
            $settings = \App\Models\Settings::first();

            // 1. Tentar logo do TENANT (campo logo da tabela tenants - central)
            if ($tenant->logo) {
                $possiblePaths = [
                    storage_path('app/public/' . $tenant->logo),
                    public_path('storage/' . $tenant->logo),
                ];

                foreach ($possiblePaths as $logoPath) {
                    if (file_exists($logoPath) && is_file($logoPath)) {
                        return $this->serveImage($logoPath, $size);
                    }
                }
            }

            // 2. Tentar logo do SETTINGS (campo logo da tabela settings - tenant)
            if ($settings && $settings->logo) {
                $possiblePaths = [
                    storage_path('app/public/' . $settings->logo),
                    public_path('storage/' . $settings->logo),
                ];

                foreach ($possiblePaths as $logoPath) {
                    if (file_exists($logoPath) && is_file($logoPath)) {
                        return $this->serveImage($logoPath, $size);
                    }
                }
            }

            // Fallback: SVG com nome do restaurante
            return $this->generateSVGIcon($tenant->name, $size);
        } else {
            // Ícone central YumGo
            return $this->generateSVGIcon('YumGo', $size);
        }
    }

    /**
     * Gera ícone SVG com texto
     */
    private function generateSVGIcon($text, $size)
    {
        // Pegar iniciais (máximo 2 letras)
        $words = explode(' ', $text);
        $initials = '';

        if (count($words) >= 2) {
            $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        } else {
            $initials = strtoupper(substr($text, 0, 2));
        }

        $fontSize = $size * 0.45;
        $borderRadius = $size * 0.2;

        $svg = <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="{$size}" height="{$size}" viewBox="0 0 {$size} {$size}">
    <defs>
        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#EA1D2C;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#DC2626;stop-opacity:1" />
        </linearGradient>
    </defs>
    <rect width="{$size}" height="{$size}" rx="{$borderRadius}" fill="url(#grad)"/>
    <text x="50%" y="50%" font-size="{$fontSize}" text-anchor="middle" dominant-baseline="central" fill="white" font-family="Arial, sans-serif" font-weight="bold">{$initials}</text>
</svg>
SVG;

        return response($svg, 200)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=86400'); // Cache 1 dia
    }

    /**
     * Serve imagem existente (sem resize por enquanto)
     */
    private function serveImage($path, $size)
    {
        $mimeType = mime_content_type($path);

        return response()
            ->file($path, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=86400',
            ]);
    }
}
