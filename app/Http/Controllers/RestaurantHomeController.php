<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\WeeklyMenu;
use App\Models\Settings;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RestaurantHomeController extends Controller
{
    public function index()
    {
        // Verificar se há cardápio semanal ativo
        $activeMenu = WeeklyMenu::getActive();
        $todayProductIds = null;
        $hasMenuFilter = false;

        if ($activeMenu) {
            // Pegar apenas os produtos disponíveis para hoje
            $today = WeeklyMenu::getCurrentDayOfWeek();
            $todayProductIds = $activeMenu->items()
                ->where('day_of_week', $today)
                ->where('is_available', true)
                ->pluck('product_id')
                ->toArray();

            // Se há cardápio ativo, SEMPRE filtra (mesmo que vazio)
            // Array vazio = nenhum produto hoje = loja vazia
            $hasMenuFilter = true;
        }

        // Query base para produtos ativos com estoque
        $productQuery = function ($query) use ($todayProductIds, $hasMenuFilter) {
            $query->where('is_active', true)
                  ->where(function ($q) {
                      $q->where('has_stock_control', false)
                        ->orWhere(function ($sq) {
                            $sq->where('has_stock_control', true)
                               ->where('stock_quantity', '>', 0);
                        });
                  });

            // Se há cardápio ativo, filtrar pelos produtos do dia
            if ($hasMenuFilter) {
                // Se $todayProductIds vazio, whereIn([]) não retorna nada = correto!
                $query->whereIn('id', $todayProductIds ?: [0]); // 0 = ID inexistente
            }
        };

        // Se há cardápio mas está vazio para hoje, buscar TODOS os produtos (modo "visualização")
        $previewMode = $hasMenuFilter && empty($todayProductIds);

        if ($previewMode) {
            // Modo preview: mostrar todos os produtos mas marcados como indisponíveis
            $categories = Category::with(['products' => function ($query) {
                $query->where('is_active', true)
                      ->with(['variations' => function ($q) {
                          $q->where('is_active', true)->orderBy('price_modifier');
                      }])
                      ->orderBy('order', 'asc')
                      ->orderBy('name', 'asc');
            }])
            ->where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc')
            ->get()
            ->filter(function ($category) {
                return $category->products->count() > 0;
            });

            $allProducts = Product::where('is_active', true)
                ->with('category')
                ->orderBy('name', 'asc')
                ->get();
        } else {
            // Modo normal: mostrar apenas produtos disponíveis
            $categories = Category::with(['products' => function ($query) use ($productQuery) {
                $productQuery($query);
                $query->with(['variations' => function ($q) {
                      $q->where('is_active', true)->orderBy('price_modifier');
                  }])
                  ->orderBy('order', 'asc')
                  ->orderBy('name', 'asc');
            }])
            ->where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc')
            ->get()
            ->filter(function ($category) {
                return $category->products->count() > 0;
            });

            $allProducts = Product::where('is_active', true)
                ->where(function ($q) {
                    $q->where('has_stock_control', false)
                      ->orWhere(function ($sq) {
                          $sq->where('has_stock_control', true)
                             ->where('stock_quantity', '>', 0);
                      });
                })
                ->when($hasMenuFilter, function ($query) use ($todayProductIds) {
                    $query->whereIn('id', $todayProductIds ?: [0]);
                })
                ->with('category')
                ->orderBy('name', 'asc')
                ->get();
        }

        // Configurações de pizzas (para JavaScript) - filtradas pelo cardápio se houver
        $pizzaConfigs = Product::where('is_active', true)
            ->where('is_pizza', true)
            ->where(function ($q) {
                $q->where('has_stock_control', false)
                  ->orWhere(function ($sq) {
                      $sq->where('has_stock_control', true)
                         ->where('stock_quantity', '>', 0);
                  });
            })
            ->when($hasMenuFilter, function ($query) use ($todayProductIds) {
                $query->whereIn('id', $todayProductIds ?: [0]); // Se vazio, ID 0 não existe = nada
            })
            ->get()
            ->mapWithKeys(function ($product) {
                return [
                    $product->id => [
                        'name' => $product->name,
                        'ingredients' => $product->description ?? '',
                        'allows_half_and_half' => $product->allows_half_and_half ?? true,
                        'available_sizes' => $product->available_sizes ?? ['small', 'medium', 'large', 'family'],
                        'available_borders' => $product->available_borders ?? ['none', 'catupiry', 'cheddar', 'chocolate'],
                        'size_prices' => $product->size_prices ?? [],
                        'border_prices' => $product->border_prices ?? [],
                    ]
                ];
            });

        // Verificar horário de funcionamento
        $settings = Settings::current();
        $isOpen = $settings->isOpenNow();

        // Determinar motivo se loja está vazia (não aplicável em preview mode)
        $emptyReason = null;
        if (!$previewMode && $allProducts->isEmpty()) {
            if (!$isOpen) {
                $emptyReason = 'closed'; // Fora do horário
            } elseif ($hasMenuFilter && empty($todayProductIds)) {
                $emptyReason = 'no_menu'; // Cardápio não cadastrado para hoje
            } else {
                $emptyReason = 'no_products'; // Nenhum produto cadastrado
            }
        }

        // Obter horário de abertura para hoje
        $now = Carbon::now();
        $dayOfWeek = strtolower($now->format('l'));
        $todayHours = $settings->business_hours[$dayOfWeek] ?? null;
        $openTime = $todayHours['open'] ?? null;
        $closeTime = $todayHours['close'] ?? null;

        // Zonas de entrega (bairros habilitados com taxas)
        $deliveryZones = \App\Models\Neighborhood::where('enabled', true)
            ->orderBy('city')
            ->orderBy('name')
            ->get()
            ->groupBy('city')
            ->map(function ($neighborhoods) {
                return $neighborhoods->map(function ($n) {
                    return [
                        'id' => $n->id,
                        'name' => $n->name,
                        'city' => $n->city,
                        'fee' => (float) $n->delivery_fee,
                        'time' => $n->delivery_time,
                    ];
                })->toArray();
            })
            ->toArray();

        $allowDelivery = $settings->allow_delivery ?? true;
        $allowPickup = $settings->allow_pickup ?? true;
        $minimumOrderValue = $settings->minimum_order_value ?? 0;

        return view('restaurant-home', [
            'categories' => $categories,
            'allProducts' => $allProducts,
            'pizzaConfigs' => $pizzaConfigs,
            'activeMenu' => $activeMenu,
            'currentDay' => $activeMenu ? WeeklyMenu::getCurrentDayOfWeek() : null,
            'isOpen' => $isOpen,
            'emptyReason' => $emptyReason,
            'openTime' => $openTime,
            'closeTime' => $closeTime,
            'previewMode' => $previewMode,
            'deliveryZones' => $deliveryZones,
            'allowDelivery' => $allowDelivery,
            'allowPickup' => $allowPickup,
            'minimumOrderValue' => $minimumOrderValue,
            'todayProductIds' => $todayProductIds ?? [],
            'settings' => $settings,
        ]);
    }
}
