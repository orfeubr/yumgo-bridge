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
            ->with('domains');

        // Busca por nome
        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        // Paginação
        $restaurants = $query->orderBy('name', 'asc')->paginate(12);

        // Adicionar informações extras
        $restaurants->each(function ($restaurant) {
            // URL do restaurante
            $domain = $restaurant->domains->first();
            $restaurant->url = $domain ? 'https://' . $domain->domain : null;

            // Status de abertura
            $restaurant->is_open = $restaurant->isOpen();

            // URL da logo
            $restaurant->logo_url = $restaurant->logo
                ? asset('storage/' . $restaurant->logo)
                : asset('images/default-restaurant.png');
        });

        return view('marketplace.index', [
            'restaurants' => $restaurants,
            'search' => $request->search ?? '',
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
