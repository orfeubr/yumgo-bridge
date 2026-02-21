<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Listar todos os produtos
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'variations', 'addons'])
            ->where('is_available', true);

        // Busca por nome
        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        // Ordenação
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $products->map(fn($product) => $this->formatProduct($product)),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Mostrar um produto específico
     */
    public function show($id)
    {
        $product = Product::with(['category', 'variations', 'addons'])
            ->findOrFail($id);

        if (!$product->is_available) {
            return response()->json([
                'message' => 'Produto não disponível no momento.',
            ], 404);
        }

        return response()->json($this->formatProduct($product));
    }

    /**
     * Produtos por categoria
     */
    public function byCategory($categoryId)
    {
        $products = Product::with(['category', 'variations', 'addons'])
            ->where('category_id', $categoryId)
            ->where('is_available', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $products->map(fn($product) => $this->formatProduct($product)),
        ]);
    }

    /**
     * Produtos em destaque
     */
    public function featured()
    {
        $products = Product::with(['category', 'variations', 'addons'])
            ->where('is_featured', true)
            ->where('is_available', true)
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json([
            'data' => $products->map(fn($product) => $this->formatProduct($product)),
        ]);
    }

    /**
     * Formatar produto para resposta API
     */
    private function formatProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'image' => $product->image,
            'is_featured' => $product->is_featured,
            'is_available' => $product->is_available,
            'category' => [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'icon' => $product->category->icon,
            ],
            'variations' => $product->variations->map(fn($v) => [
                'id' => $v->id,
                'name' => $v->name,
                'price_modifier' => $v->price_modifier,
                'final_price' => $product->price + $v->price_modifier,
            ]),
            'addons' => $product->addons->map(fn($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'price' => $a->price,
                'max_quantity' => $a->max_quantity,
            ]),
            'pizza_config' => $product->pizza_config,
            'marmitex_config' => $product->marmitex_config,
        ];
    }
}
