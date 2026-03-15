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
        // ✅ Verificar status de aprovação do restaurante
        $tenant = tenant();

        if ($tenant->approval_status === 'pending_approval') {
            return view('tenant.pending-approval', [
                'tenant' => $tenant,
            ]);
        }

        if ($tenant->approval_status === 'rejected') {
            return view('tenant.rejected', [
                'tenant' => $tenant,
                'reason' => $tenant->rejection_reason,
            ]);
        }

        // ✅ Apenas restaurantes aprovados continuam

        // Verificar se há cardápio semanal ativo
        $activeMenu = WeeklyMenu::getActive();
        $allMenuProductIds = null;
        $todayProductIds = null;
        $hasMenuFilter = false;

        if ($activeMenu) {
            // ✅ REGRA: Produto DEVE estar no cardápio para aparecer
            $today = WeeklyMenu::getCurrentDayOfWeek();

            // 1️⃣ Buscar TODOS produtos do cardápio semanal (qualquer dia)
            $allMenuProductIds = $activeMenu->items()
                ->where('is_available', true)
                ->pluck('product_id')
                ->unique()
                ->toArray();

            // 2️⃣ Buscar produtos disponíveis HOJE (para remover greyscale)
            $todayProductIds = $activeMenu->items()
                ->where('day_of_week', $today)
                ->where('is_available', true)
                ->pluck('product_id')
                ->toArray();

            // Filtrar por produtos no cardápio
            $hasMenuFilter = true;
        } else {
            // ⚠️ OBRIGATÓRIO: Sem cardápio ativo = loja vazia
            // Força restaurante a criar e gerenciar cardápio semanal
            $hasMenuFilter = true;
            $allMenuProductIds = []; // Array vazio = sem produtos
            $todayProductIds = [];
        }

        // Query base para produtos ativos com estoque
        $productQuery = function ($query) use ($allMenuProductIds, $hasMenuFilter) {
            $query->where('is_active', true)
                  ->where(function ($q) {
                      $q->where('has_stock_control', false)
                        ->orWhere(function ($sq) {
                            $sq->where('has_stock_control', true)
                               ->where('stock_quantity', '>', 0);
                        });
                  });

            // Se há cardápio ativo, filtrar por TODOS produtos do cardápio
            if ($hasMenuFilter) {
                // Se vazio, whereIn([]) não retorna nada = correto!
                $query->whereIn('id', $allMenuProductIds ?: [0]); // 0 = ID inexistente
            }
        };

        // ⚠️ DESABILITADO: Modo preview removido
        // Se não há produtos no cardápio, loja fica vazia (sem preview)
        $previewMode = false;

        if (false) {
            // Preview mode desabilitado
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
                ->when($hasMenuFilter, function ($query) use ($allMenuProductIds) {
                    $query->whereIn('id', $allMenuProductIds ?: [0]);
                })
                ->with('category')
                ->orderBy('name', 'asc')
                ->get();
        }

        // Configurações de pizzas (para JavaScript) - filtradas pelo cardápio se houver
        // ⚠️ Filtrar por pizza_config não nulo (produtos que têm configuração de pizza)
        $pizzaConfigs = Product::where('is_active', true)
            ->whereNotNull('pizza_config') // ✅ Identifica pizzas pela configuração JSONB
            ->where(function ($q) {
                $q->where('has_stock_control', false)
                  ->orWhere(function ($sq) {
                      $sq->where('has_stock_control', true)
                         ->where('stock_quantity', '>', 0);
                  });
            })
            ->when($hasMenuFilter, function ($query) use ($allMenuProductIds) {
                $query->whereIn('id', $allMenuProductIds ?: [0]); // Se vazio, ID 0 não existe = nada
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
        $settings = null;
        $isOpen = true;

        try {
            $settings = Settings::current();
            $isOpen = $settings->isOpenNow();
        } catch (\Exception $e) {
            // Fallback se tabela settings não existir (continua com valores padrão)
        }

        // Determinar motivo se loja está vazia (não aplicável em preview mode)
        $emptyReason = null;
        if (!$previewMode && $allProducts->isEmpty()) {
            if (!$isOpen) {
                $emptyReason = 'closed'; // Fora do horário
            } elseif (!$activeMenu) {
                $emptyReason = 'no_weekly_menu'; // Cardápio semanal não cadastrado
            } elseif ($hasMenuFilter && empty($todayProductIds)) {
                $emptyReason = 'no_menu'; // Cardápio não cadastrado para hoje
            } else {
                $emptyReason = 'no_products'; // Nenhum produto cadastrado
            }
        }

        // Obter horário de abertura para hoje
        $now = Carbon::now();
        $dayOfWeek = strtolower($now->format('l'));
        $todayHours = $settings?->business_hours[$dayOfWeek] ?? null;
        $openTime = $todayHours['open'] ?? null;
        $closeTime = $todayHours['close'] ?? null;

        // Zonas de entrega (bairros habilitados com taxas)
        try {
            $deliveryZones = \App\Models\Neighborhood::where('is_active', true)
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
        } catch (\Exception $e) {
            // Fallback se tabela neighborhoods não existir
            $deliveryZones = [];
        }

        $allowDelivery = $settings->allow_delivery ?? true;
        $allowPickup = $settings->allow_pickup ?? true;
        $minimumOrderValue = $settings->minimum_order_value ?? 0;

        // Reviews
        $averageRating = \App\Models\Review::public()->avg('rating');
        $totalReviews = \App\Models\Review::public()->count();
        $recentReviews = \App\Models\Review::with(['customer', 'order'])
            ->public()
            ->whereNotNull('comment')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

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
            'tenant' => $tenant, // ✅ Passando tenant para a view
            'averageRating' => $averageRating ? round($averageRating, 1) : null,
            'totalReviews' => $totalReviews,
            'recentReviews' => $recentReviews,
        ]);
    }
}
