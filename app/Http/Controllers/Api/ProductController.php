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
            ->where('is_active', true);

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

        if (!$product->is_active) {
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
            ->where('is_active', true)
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
            ->where('is_active', true)
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
            'filling' => $product->filling,
            'price' => $product->price,
            'image' => $product->image,
            'is_featured' => $product->is_featured,
            'is_active' => $product->is_active,
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

    /**
     * Listar sabores de pizza (para seleção meio a meio)
     * MOSTRA RECHEIO DESTACADO NO SCROLL
     */
    public function pizzaFlavors(Request $request)
    {
        $query = Product::with('category')
            ->whereNotNull('pizza_config')
            ->where('is_active', true);

        // Busca por nome ou recheio
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('filling', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Ordenação
        $query->orderBy('name');

        // Paginação para scroll infinito
        $perPage = $request->get('per_page', 20);
        $flavors = $query->paginate($perPage);

        return response()->json([
            'data' => $flavors->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,

                // INGREDIENTES EM DESTAQUE (visível antes de clicar)
                'ingredients' => $product->filling ?? 'Ingredientes não informados',
                'ingredients_short' => $this->shortenText($product->filling, 60),

                'price' => $product->price,
                'price_formatted' => 'R$ ' . number_format($product->price, 2, ',', '.'),
                'image' => $product->image,
                'image_thumb' => $product->image, // TODO: implementar thumbs
                'description' => $product->description,
                'category' => [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'icon' => $product->category->icon ?? '🍕',
                ],
            ]),
            'pagination' => [
                'current_page' => $flavors->currentPage(),
                'last_page' => $flavors->lastPage(),
                'per_page' => $flavors->perPage(),
                'total' => $flavors->total(),
                'has_more' => $flavors->hasMorePages(),
            ],
        ]);
    }

    /**
     * Encurtar texto para preview
     */
    private function shortenText(?string $text, int $maxLength = 60): string
    {
        if (!$text || strlen($text) <= $maxLength) {
            return $text ?? '';
        }

        return substr($text, 0, $maxLength) . '...';
    }
}
