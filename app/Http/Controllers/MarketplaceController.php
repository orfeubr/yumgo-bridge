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

        // Adicionar URL e verificar status de cada restaurante
        $restaurants->each(function ($restaurant) {
            // Assumindo que o domínio é: {slug}.yumgo.com.br
            $restaurant->url = 'https://' . $restaurant->slug . '.yumgo.com.br';

            // Verificar se está aberto (usa método do model)
            $restaurant->is_open = $restaurant->isOpen();
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
