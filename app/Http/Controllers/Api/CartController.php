<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * 🛒 Valida itens do carrinho e retorna preços atualizados
     *
     * POST /api/v1/cart/validate
     *
     * Recebe array de items do localStorage:
     * [
     *   {
     *     "product_id": 1,
     *     "variation_id": 2,  // opcional
     *     "quantity": 2,
     *     "price_when_added": 25.90
     *   }
     * ]
     *
     * Retorna items com preços atuais + flag 'price_changed'
     */
    public function validate(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.variation_id' => 'nullable|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price_when_added' => 'required|numeric|min:0',
        ]);

        $items = $request->input('items');
        $validatedItems = [];
        $totalPriceChanges = 0;

        foreach ($items as $item) {
            $productId = $item['product_id'];
            $variationId = $item['variation_id'] ?? null;
            $quantity = $item['quantity'];
            $priceWhenAdded = (float) $item['price_when_added'];

            // 🔒 SEGURANÇA: Buscar produto APENAS no schema do tenant atual
            // Isso previne fraude de adicionar produto de outro restaurante
            $product = Product::where('is_active', true)->find($productId);

            if (!$product) {
                // 🚨 ALERTA DE SEGURANÇA: Produto não existe neste tenant
                // Pode ser tentativa de fraude (produto de outro restaurante)
                \Log::warning('⚠️ Tentativa de validar produto inexistente no tenant', [
                    'product_id' => $productId,
                    'tenant' => tenant('id'),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                $validatedItems[] = [
                    'product_id' => $productId,
                    'variation_id' => $variationId,
                    'quantity' => $quantity,
                    'price_when_added' => $priceWhenAdded,
                    'current_price' => null,
                    'available' => false,
                    'reason' => 'Produto não disponível neste restaurante',
                    'price_changed' => false,
                ];
                continue;
            }

            // Verificar estoque
            $hasStock = true;
            if ($product->has_stock_control) {
                $hasStock = $product->stock_quantity >= $quantity;
            }

            if (!$hasStock) {
                $validatedItems[] = [
                    'product_id' => $productId,
                    'variation_id' => $variationId,
                    'quantity' => $quantity,
                    'price_when_added' => $priceWhenAdded,
                    'current_price' => null,
                    'available' => false,
                    'reason' => 'Sem estoque suficiente',
                    'price_changed' => false,
                ];
                continue;
            }

            // Determinar preço atual
            $currentPrice = $product->price;

            if ($variationId) {
                $variation = ProductVariation::where('is_active', true)
                    ->where('product_id', $productId)
                    ->find($variationId);

                if (!$variation) {
                    // Variação não existe ou foi desativada
                    $validatedItems[] = [
                        'product_id' => $productId,
                        'variation_id' => $variationId,
                        'quantity' => $quantity,
                        'price_when_added' => $priceWhenAdded,
                        'current_price' => null,
                        'available' => false,
                        'reason' => 'Variação não disponível',
                        'price_changed' => false,
                    ];
                    continue;
                }

                // Preço com variação
                if ($variation->price) {
                    $currentPrice = $variation->price;
                } elseif ($variation->price_modifier) {
                    $currentPrice = $product->price + $variation->price_modifier;
                }
            }

            // Comparar preços
            $priceChanged = abs($currentPrice - $priceWhenAdded) > 0.01; // Tolerância de 1 centavo

            if ($priceChanged) {
                $totalPriceChanges++;
            }

            $validatedItems[] = [
                'product_id' => $productId,
                'variation_id' => $variationId,
                'quantity' => $quantity,
                'price_when_added' => $priceWhenAdded,
                'current_price' => $currentPrice,
                'available' => true,
                'price_changed' => $priceChanged,
                'price_difference' => $priceChanged ? ($currentPrice - $priceWhenAdded) : 0,
                'product' => [
                    'name' => $product->name,
                    'description' => $product->description,
                    'image' => $product->image,
                ],
            ];
        }

        return response()->json([
            'items' => $validatedItems,
            'summary' => [
                'total_items' => count($validatedItems),
                'unavailable_items' => count(array_filter($validatedItems, fn($i) => !$i['available'])),
                'price_changes' => $totalPriceChanges,
                'needs_update' => $totalPriceChanges > 0,
            ],
        ]);
    }

    /**
     * 🗑️ Limpar carrinho antigo (opcional - pode ser chamado por cron)
     *
     * Clientes podem limpar carrinho manualmente no frontend
     * Mas este endpoint pode ser útil para sugerir limpeza
     */
    public function checkExpiration(Request $request)
    {
        $request->validate([
            'cart_created_at' => 'required|date',
        ]);

        $cartCreatedAt = \Carbon\Carbon::parse($request->input('cart_created_at'));
        $now = \Carbon\Carbon::now();

        // Considerar carrinho "antigo" após 24 horas
        $hoursOld = $now->diffInHours($cartCreatedAt);
        $isOld = $hoursOld >= 24;

        return response()->json([
            'is_old' => $isOld,
            'hours_old' => $hoursOld,
            'message' => $isOld
                ? 'Seu carrinho tem mais de 24 horas. Recomendamos validar os itens.'
                : 'Carrinho ainda válido.',
        ]);
    }
}
