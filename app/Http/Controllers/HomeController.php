<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Página principal do marketplace (listagem de restaurantes)
     */
    public function index(Request $request)
    {
        $query = Tenant::query()
            ->where('status', 'active')
            ->with('domains')
            ->orderBy('name', 'asc');

        // Busca por nome
        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Busca por categoria (implementar depois quando tiver categorias)
        // if ($category = $request->get('category')) {
        //     $query->whereHas('categories', fn($q) => $q->where('slug', $category));
        // }

        $restaurants = $query->paginate(12);

        return view('home', [
            'restaurants' => $restaurants,
            'search' => $search ?? '',
        ]);
    }

    /**
     * Landing page para restaurantes (marketing)
     */
    public function paraRestaurantes()
    {
        $plans = \App\Models\Plan::where('is_active', true)
            ->orderBy('price_monthly', 'asc')
            ->get();

        return view('para-restaurantes', [
            'plans' => $plans,
        ]);
    }
}
