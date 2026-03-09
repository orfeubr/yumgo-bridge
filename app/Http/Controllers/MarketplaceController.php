<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Plan;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    /**
     * Exibe o marketplace de restaurantes (página principal)
     */
    public function index(Request $request)
    {
        $query = Tenant::where('status', 'active')
            ->where('approval_status', 'approved')
            ->with('domains');

        // Busca por nome
        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        // Geolocalização do cliente (se disponível)
        $clientLat = $request->input('lat');
        $clientLon = $request->input('lon');

        // Paginação
        $restaurants = $query->orderBy('name', 'asc')->paginate(12);

        // Adicionar informações extras
        $restaurants->getCollection()->transform(function ($restaurant) use ($clientLat, $clientLon) {
            // URL do restaurante (preferir slug-based domain ao invés de UUID)
            $domain = $restaurant->domains->first(function($d) {
                // Filtrar: rejeitar domínios que começam com UUID
                return !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\./', $d->domain);
            });

            // Fallback: se não encontrar slug-based, usa qualquer um
            if (!$domain) {
                $domain = $restaurant->domains->first();
            }

            $restaurant->url = $domain ? 'https://' . $domain->domain : null;

            // Status de abertura
            $restaurant->is_open = $restaurant->isOpen();

            // URL da logo
            $restaurant->logo_url = $restaurant->logo
                ? asset('storage/' . $restaurant->logo)
                : asset('images/default-restaurant.svg');

            // Calcular distância e taxa de entrega se tiver localização
            if ($clientLat && $clientLon && $restaurant->latitude && $restaurant->longitude) {
                $distance = \App\Services\GeolocationService::calculateDistance(
                    $clientLat,
                    $clientLon,
                    $restaurant->latitude,
                    $restaurant->longitude
                );

                $deliveryInfo = \App\Services\GeolocationService::getDeliveryFee($restaurant, $distance);

                $restaurant->distance = $distance;
                $restaurant->distance_formatted = \App\Services\GeolocationService::formatDistance($distance);
                $restaurant->delivery_fee = $deliveryInfo['fee'];
                $restaurant->delivery_fee_formatted = \App\Services\GeolocationService::formatDeliveryFee($deliveryInfo);
                $restaurant->is_free_delivery = $deliveryInfo['is_free'];
                $restaurant->delivers = $deliveryInfo['delivers'] ?? true;
            } else {
                // Valores padrão se não tiver localização
                $restaurant->distance = null;
                $restaurant->distance_formatted = null;
                $restaurant->delivery_fee = 5.00;
                $restaurant->delivery_fee_formatted = 'R$ 5,00';
                $restaurant->is_free_delivery = false;
                $restaurant->delivers = true;
            }

            return $restaurant;
        });

        // ===== MAIS PEDIDOS (Top 5 por volume últimos 30 dias) =====
        $mostOrdered = Tenant::where('status', 'active')
            ->where('approval_status', 'approved')
            ->with('domains')
            ->orderBy('total_orders_30d', 'desc')
            ->limit(5)
            ->get();

        // Processar informações extras (mesmo tratamento dos restaurantes principais)
        $mostOrdered->transform(function ($restaurant) use ($clientLat, $clientLon) {
            $domain = $restaurant->domains->first(function($d) {
                return !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\./', $d->domain);
            });
            if (!$domain) {
                $domain = $restaurant->domains->first();
            }
            $restaurant->url = $domain ? 'https://' . $domain->domain : null;
            $restaurant->is_open = $restaurant->isOpen();
            $restaurant->logo_url = $restaurant->logo ? asset('storage/' . $restaurant->logo) : asset('images/default-restaurant.svg');

            if ($clientLat && $clientLon && $restaurant->latitude && $restaurant->longitude) {
                $distance = \App\Services\GeolocationService::calculateDistance($clientLat, $clientLon, $restaurant->latitude, $restaurant->longitude);
                $deliveryInfo = \App\Services\GeolocationService::getDeliveryFee($restaurant, $distance);
                $restaurant->distance = $distance;
                $restaurant->distance_formatted = \App\Services\GeolocationService::formatDistance($distance);
                $restaurant->delivery_fee = $deliveryInfo['fee'];
                $restaurant->delivery_fee_formatted = \App\Services\GeolocationService::formatDeliveryFee($deliveryInfo);
                $restaurant->is_free_delivery = $deliveryInfo['is_free'];
                $restaurant->delivers = $deliveryInfo['delivers'] ?? true;
            } else {
                $restaurant->distance = null;
                $restaurant->distance_formatted = null;
                $restaurant->delivery_fee = 5.00;
                $restaurant->delivery_fee_formatted = 'R$ 5,00';
                $restaurant->is_free_delivery = false;
                $restaurant->delivers = true;
            }

            return $restaurant;
        });

        return view('marketplace.index', [
            'restaurants' => $restaurants,
            'mostOrdered' => $mostOrdered,
            'search' => $request->search ?? '',
            'hasLocation' => $clientLat && $clientLon,
            'platformSettings' => (object)[
                'platform_name' => config('app.name', 'YumGo'),
                'platform_logo' => null,
            ],
        ]);
    }

    /**
     * Landing page para restaurantes (marketing/vendas)
     */
    public function paraRestaurantes()
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('price_monthly', 'asc')
            ->get();

        return view('marketplace.para-restaurantes', [
            'plans' => $plans,
        ]);
    }

    /**
     * Página de planos (legado - redireciona para /para-restaurantes)
     */
    public function pricing()
    {
        return redirect()->route('para-restaurantes');
    }
}
