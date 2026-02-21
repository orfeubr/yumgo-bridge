<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Listar pedidos do cliente
     */
    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $orders->map(fn($order) => $this->formatOrder($order)),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Mostrar detalhes de um pedido
     */
    public function show(Request $request, $id)
    {
        $order = $request->user()
            ->orders()
            ->with(['items.product', 'delivery'])
            ->findOrFail($id);

        return response()->json($this->formatOrder($order, true));
    }

    /**
     * Criar novo pedido
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.addons' => 'nullable|array',
            'items.*.addons.*' => 'exists:product_addons,id',
            'items.*.notes' => 'nullable|string|max:500',
            'delivery_address' => 'required|string',
            'payment_method' => 'required|in:pix,credit_card,debit_card,cash',
            'use_cashback' => 'nullable|numeric|min:0',
        ]);

        $customer = $request->user();

        // Verificar se tem cashback suficiente
        if ($request->use_cashback > $customer->cashback_balance) {
            return response()->json([
                'message' => 'Saldo de cashback insuficiente.',
            ], 422);
        }

        try {
            $order = $this->orderService->createOrder($customer, [
                'items' => $request->items,
                'delivery_address' => $request->delivery_address,
                'payment_method' => $request->payment_method,
                'use_cashback' => $request->use_cashback ?? 0,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'message' => 'Pedido criado com sucesso!',
                'order' => $this->formatOrder($order->fresh(['items.product']), true),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao criar pedido: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancelar pedido
     */
    public function cancel(Request $request, $id)
    {
        $order = $request->user()
            ->orders()
            ->findOrFail($id);

        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Apenas pedidos pendentes podem ser cancelados.',
            ], 422);
        }

        $order->update(['status' => 'canceled']);

        return response()->json([
            'message' => 'Pedido cancelado com sucesso!',
            'order' => $this->formatOrder($order),
        ]);
    }

    /**
     * Rastrear pedido em tempo real
     */
    public function track(Request $request, $id)
    {
        $order = $request->user()
            ->orders()
            ->with('delivery')
            ->findOrFail($id);

        return response()->json([
            'order_id' => $order->id,
            'status' => $order->status,
            'status_label' => $this->getStatusLabel($order->status),
            'delivery' => $order->delivery ? [
                'driver_name' => $order->delivery->driver_name,
                'driver_phone' => $order->delivery->driver_phone,
                'estimated_time' => $order->delivery->estimated_time,
                'tracking_code' => $order->delivery->tracking_code,
            ] : null,
            'timeline' => [
                ['status' => 'pending', 'completed' => true, 'time' => $order->created_at],
                ['status' => 'confirmed', 'completed' => in_array($order->status, ['confirmed', 'preparing', 'ready', 'delivering', 'delivered']), 'time' => null],
                ['status' => 'preparing', 'completed' => in_array($order->status, ['preparing', 'ready', 'delivering', 'delivered']), 'time' => null],
                ['status' => 'ready', 'completed' => in_array($order->status, ['ready', 'delivering', 'delivered']), 'time' => null],
                ['status' => 'delivering', 'completed' => in_array($order->status, ['delivering', 'delivered']), 'time' => null],
                ['status' => 'delivered', 'completed' => $order->status === 'delivered', 'time' => null],
            ],
        ]);
    }

    /**
     * Formatar pedido para resposta API
     */
    private function formatOrder(Order $order, bool $includeItems = false): array
    {
        $data = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_label' => $this->getStatusLabel($order->status),
            'subtotal' => $order->subtotal,
            'delivery_fee' => $order->delivery_fee,
            'cashback_used' => $order->cashback_used,
            'total' => $order->total,
            'cashback_earned' => $order->cashback_earned,
            'payment_method' => $order->payment_method,
            'delivery_address' => $order->delivery_address,
            'created_at' => $order->created_at->format('d/m/Y H:i'),
        ];

        if ($includeItems && $order->relationLoaded('items')) {
            $data['items'] = $order->items->map(fn($item) => [
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->subtotal,
                'notes' => $item->notes,
            ]);
        }

        return $data;
    }

    /**
     * Obter label traduzido do status
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Pendente',
            'confirmed' => 'Confirmado',
            'preparing' => 'Em Preparo',
            'ready' => 'Pronto',
            'delivering' => 'Saiu para Entrega',
            'delivered' => 'Entregue',
            'canceled' => 'Cancelado',
            default => $status,
        };
    }
}
