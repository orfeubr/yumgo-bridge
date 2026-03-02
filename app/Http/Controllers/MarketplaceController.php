<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    /**
     * Exibe o marketplace de restaurantes
     */
    public function index(Request $request)
    {
        // Buscar restaurantes ativos
        $query = Tenant::where('status', 'active');

        // Busca por nome
        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        // Ordenar por nome
        $restaurants = $query->orderBy('name', 'asc')->get();

        // Adicionar URL de cada restaurante
        $restaurants->each(function ($restaurant) {
            // Assumindo que o domínio é: {slug}.yumgo.com.br
            $restaurant->url = 'https://' . $restaurant->slug . '.yumgo.com.br';

            // Logo padrão se não tiver
            if (!$restaurant->logo) {
                $restaurant->logo_url = 'https://ui-avatars.com/api/?name=' . urlencode($restaurant->name) . '&size=200&background=EA1D2C&color=fff';
            } else {
                $restaurant->logo_url = asset('storage/' . $restaurant->logo);
            }
        });

        return view('marketplace', compact('restaurants'));
    }

    /**
     * Exibe página de planos
     */
    public function pricing()
    {
        return view('pricing');
    }
}
