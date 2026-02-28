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
     * Busca customer do tenant correspondente ao usuário logado
     * Resolve incompatibilidade de IDs entre schema central e tenant
     */
    private function getTenantCustomer($loggedUser): ?\App\Models\Customer
    {
        return \App\Models\Customer::where('email', $loggedUser->email)
            ->orWhere('phone', $loggedUser->phone)
            ->first();
    }

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
            'data' => $orders->map(fn($order) => $this->formatOrder($order, true)), // ⭐ INCLUIR ITEMS
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
        // Buscar order direto no schema do tenant
        $order = Order::with(['items.product', 'delivery'])->findOrFail($id);

        // Verificar se o pedido pertence ao cliente autenticado
        if ($order->customer_id !== $this->getTenantCustomer($request->user())?->id) {
            return response()->json(['message' => 'Pedido não encontrado.'], 404);
        }

        return response()->json($this->formatOrder($order, true));
    }

    /**
     * Criar novo pedido
     */
    public function store(Request $request)
    {
        // ===== PROTEÇÃO CONTRA SQL INJECTION E MANIPULAÇÃO DE DADOS =====
        $request->validate([
            'items' => 'required|array|min:1|max:50', // Máx 50 items
            'items.*.product_id' => 'required|integer|min:1',
            'items.*.quantity' => 'required|integer|min:1|max:50',
            'items.*.variation_id' => 'nullable|integer|min:1',
            'items.*.addons' => 'nullable|array', // ⭐ ADICIONADO
            'items.*.addons.*' => 'integer', // ⭐ ADICIONADO
            'items.*.notes' => 'nullable|string|max:500',
            'delivery_address' => 'required|string|max:255',
            'delivery_city' => 'required|string|max:100',
            'delivery_neighborhood' => 'required|string|max:100',
            'payment_method' => 'required|in:pix,credit_card,debit_card,cash',
            'use_cashback' => 'nullable|boolean', // ⭐ TOGGLE: true = usar todo saldo
            'change_for' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $customer = $request->user();

        // 🔒 VALIDAR HORÁRIO DE FUNCIONAMENTO
        $settings = \App\Models\Settings::first();
        if ($settings && !$settings->isOpenNow()) {
            $now = now();
            $dayOfWeek = strtolower($now->format('l')); // monday, tuesday...
            $dayInPortuguese = \App\Models\Settings::getDayNameInPortuguese($dayOfWeek);
            $hours = $settings->business_hours[$dayInPortuguese] ?? null;

            if ($hours && is_string($hours) && str_contains($hours, ' - ')) {
                [$open, $close] = explode(' - ', $hours);
                return response()->json([
                    'message' => "O restaurante está fechado. Horário de funcionamento hoje: " . trim($open) . " às " . trim($close) . ".",
                    'restaurant_closed' => true,
                    'open_time' => trim($open),
                    'close_time' => trim($close),
                ], 422);
            }

            return response()->json([
                'message' => 'O restaurante está fechado no momento. Pedidos não podem ser criados fora do horário de funcionamento.',
                'restaurant_closed' => true,
            ], 422);
        }

        // PROTEÇÃO: Sanitizar inputs de texto (XSS)
        $deliveryCity = htmlspecialchars(trim($request->delivery_city), ENT_QUOTES, 'UTF-8');
        $deliveryNeighborhood = htmlspecialchars(trim($request->delivery_neighborhood), ENT_QUOTES, 'UTF-8');
        $deliveryAddress = htmlspecialchars(trim($request->delivery_address), ENT_QUOTES, 'UTF-8');

        // PROTEÇÃO: Calcular cashback (sempre do banco, nunca do frontend)
        // IMPORTANTE: Customer já está na conexão correta do tenant (não usar getTenantData aqui)
        $customer->refresh(); // Garante dados atualizados do banco
        $cashbackBalance = (float) $customer->cashback_balance;

        // 🎯 TOGGLE SIMPLES: Se marcou "usar cashback" = usa TODO saldo disponível
        $useCashback = 0;
        if ($request->use_cashback === true && $cashbackBalance > 0) {
            // Usa todo saldo disponível (OrderService limitará ao total do pedido)
            $useCashback = $cashbackBalance;

            \Log::info('💰 Cliente optou por usar cashback', [
                'customer_id' => $customer->id,
                'saldo_disponivel' => $cashbackBalance,
                'sera_usado' => $useCashback,
            ]);
        }

        // PROTEÇÃO: Calcular taxa de entrega SEMPRE no backend (nunca confiar no frontend)
        \Log::info('🔍 Buscando taxa de entrega', [
            'city' => $deliveryCity,
            'neighborhood' => $deliveryNeighborhood,
        ]);

        $deliveryFee = \App\Models\Neighborhood::getFeeByName(
            $deliveryCity,
            $deliveryNeighborhood
        );

        \Log::info('💰 Taxa de entrega encontrada', ['fee' => $deliveryFee]);

        if ($deliveryFee === null) {
            \Log::warning('⚠️ Bairro não encontrado', [
                'city' => $deliveryCity,
                'neighborhood' => $deliveryNeighborhood,
            ]);

            return response()->json([
                'message' => 'Não atendemos o bairro informado. Por favor, selecione um bairro válido.',
            ], 422);
        }

        // PROTEÇÃO: Garantir que a taxa é numérica e positiva
        $deliveryFee = max(0, (float) $deliveryFee);

        try {
            // PROTEÇÃO: Usar apenas dados validados e sanitizados
            $order = $this->orderService->createOrder($customer, [
                'items' => $request->items, // Será validado no enrichItems()
                'delivery_address' => $deliveryAddress,
                'delivery_city' => $deliveryCity,
                'delivery_neighborhood' => $deliveryNeighborhood,
                'delivery_fee' => $deliveryFee, // Sempre do banco
                'payment_method' => $request->payment_method, // Validado pelo validator
                'cashback_used' => $useCashback, // ⭐ CORRIGIDO: era 'use_cashback'
                'notes' => htmlspecialchars(substr($request->notes ?? '', 0, 1000), ENT_QUOTES, 'UTF-8'),
            ]);

            $response = [
                'message' => 'Pedido criado com sucesso!',
                'order' => $this->formatOrder($order->fresh(['items.product', 'payments']), true),
            ];

            // Se for PIX, adiciona QR Code na resposta
            $payment = $order->payments()->latest()->first(); // ⭐ CORRIGIDO: era $order->payment
            if ($payment && $payment->method === 'pix' && $payment->pix_qrcode) {
                $response['payment'] = [
                    'method' => 'pix',
                    'qrcode_image' => $payment->pix_qrcode,
                    'qrcode_text' => $payment->pix_copy_paste,
                    'transaction_id' => $payment->transaction_id,
                ];
            }

            return response()->json($response, 201);
        } catch (\Exception $e) {
            // LOG SEGURO (erro sem dados sensíveis)
            \Log::error('❌ Erro ao criar pedido', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'payment_method' => $request->payment_method,
                // ⚠️ NÃO logar: trace completo (pode conter dados de request)
            ]);

            return response()->json([
                'message' => 'Erro ao criar pedido: ' . $e->getMessage(),
                'debug' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null,
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
     * Rastrear pedido em tempo real (rota pública)
     */
    public function track($orderNumber)
    {
        // Busca por order_number com items.product
        $order = Order::where('order_number', $orderNumber)
            ->with('items.product')
            ->firstOrFail();

        return response()->json($this->formatOrder($order, true));
    }

    /**
     * Formatar pedido para resposta API
     */
    private function formatOrder(Order $order, bool $includeItems = false): array
    {
        $data = [
            'id' => $order->id,
            'public_token' => $order->public_token, // ⭐ Token para URLs seguras
            'order_number' => $order->order_number,
            'status' => $order->status,
            'payment_status' => $order->payment_status, // ⭐ ADICIONADO para polling
            'status_label' => $this->getStatusLabel($order->status),
            'subtotal' => $order->subtotal,
            'delivery_fee' => $order->delivery_fee,
            'cashback_used' => $order->cashback_used,
            'total' => $order->total,
            'cashback_earned' => $order->cashback_earned,
            'payment_method' => $order->payment_method,
            'delivery_address' => $order->delivery_address,
            'created_at' => $order->created_at->toIso8601String(), // ⭐ ISO para JS
            // 🔒 Informações de pagamento
            'can_pay' => $order->canReceivePayment(),
            'is_expired' => $order->isExpired(),
            'is_paid' => $order->isPaid(),
            'restaurant_open' => $order->isRestaurantOpen(),
            'payment_blocked_reason' => $order->getPaymentBlockedReason(),
        ];

        if ($includeItems && $order->relationLoaded('items')) {
            $data['items'] = $order->items->map(fn($item) => [
                'product_name' => $item->product_name ?? ($item->product->name ?? 'Produto'),
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->subtotal,
                'notes' => $item->notes,
            ]);
        }

        return $data;
    }

    /**
     * Consultar pagamento (QR Code PIX)
     */
    public function payment(Request $request, $id)
    {
        try {
            // LOG SEGURO (dados não sensíveis)
            \Log::info('🔍 Buscando pagamento', [
                'order_id' => $id,
                'tenant_id' => tenant()->id ?? 'NULL'
                // ⚠️ NÃO logar: customer_id (LGPD)
            ]);

            // Buscar order direto no schema do tenant
            $order = Order::with('payments')->findOrFail($id);

            // Verificar se o pedido pertence ao cliente autenticado
            if ($order->customer_id !== $this->getTenantCustomer($request->user())?->id) {
                // LOG SEGURO (tentativa de acesso não autorizado - sem IDs de cliente)
                \Log::warning('⚠️ Tentativa de acesso a pedido de outro cliente', [
                    'order_id' => $id,
                    'tenant_id' => tenant()->id ?? 'NULL',
                    // ⚠️ NÃO logar: customer IDs (LGPD)
                ]);
                return response()->json([
                    'message' => 'Pedido não encontrado.',
                ], 404);
            }

            // 🔒 VALIDAR SE PEDIDO PODE RECEBER PAGAMENTO
            if (!$order->canReceivePayment()) {
                $reason = $order->getPaymentBlockedReason();
                return response()->json([
                    'message' => $reason,
                    'can_pay' => false,
                    'is_expired' => $order->isExpired(),
                    'is_paid' => $order->isPaid(),
                    'restaurant_closed' => !$order->isRestaurantOpen(),
                ], 422);
            }

        } catch (\Exception $e) {
            \Log::error('❌ Erro ao buscar pagamento', [
                'error' => $e->getMessage(),
                'order_id' => $id,
                // ⚠️ NÃO logar: trace (pode conter dados sensíveis)
            ]);
            throw $e;
        }

        $payment = $order->payments()->latest()->first();

        if (!$payment) {
            return response()->json([
                'message' => 'Pagamento não encontrado.',
            ], 404);
        }

        $data = [
            'payment_id' => $payment->id,
            'method' => $payment->method,
            'method_name' => $payment->method_name,
            'amount' => $payment->amount,
            'status' => $payment->status,
            'gateway' => $payment->gateway_name,
            'order_number' => $order->order_number,
        ];

        // Se for PIX, retorna QR Code
        if ($payment->isPix() && ($payment->pix_qrcode || $payment->pix_copy_paste)) {
            $data['pix'] = [
                'qrcode_image' => $payment->pix_qrcode, // base64
                'qrcode_text' => $payment->pix_copy_paste,
            ];
        }

        // Se tiver URL de pagamento (boleto, cartão), retorna
        if ($payment->asaas_payment_url) {
            $data['payment_url'] = $payment->asaas_payment_url;
        }

        return response()->json($data);
    }

    /**
     * Mostrar detalhes de um pedido por ORDER_NUMBER (segurança)
     */
    public function showByOrderNumber(Request $request, string $orderNumber)
    {
        // LOG SEGURO (dados não sensíveis)
        \Log::info('🔍 Buscando pedido por order_number', [
            'order_number' => $orderNumber,
            'tenant_id' => tenant()->id ?? 'NULL'
            // ⚠️ NÃO logar: customer_id (LGPD)
        ]);

        // Buscar order por order_number no schema do tenant
        $order = Order::where('order_number', $orderNumber)
            ->with(['items.product', 'delivery'])
            ->firstOrFail();

        // Verificar se o pedido pertence ao cliente autenticado
        if ($order->customer_id !== $this->getTenantCustomer($request->user())?->id) {
            \Log::warning('⚠️ Tentativa de acesso a pedido de outro cliente via order_number', [
                'order_number' => $orderNumber,
                'tenant_id' => tenant()->id ?? 'NULL',
                // ⚠️ NÃO logar: customer IDs (LGPD)
            ]);
            return response()->json(['message' => 'Pedido não encontrado.'], 404);
        }

        return response()->json($this->formatOrder($order, true));
    }

    /**
     * Mostrar detalhes de um pedido por TOKEN (segurança) - DEPRECATED
     */
    public function showByToken(Request $request, string $token)
    {
        // LOG SEGURO (dados não sensíveis)
        \Log::info('🔍 Buscando pedido por token', [
            'tenant_id' => tenant()->id ?? 'NULL'
            // ⚠️ NÃO logar: token, customer_id (LGPD)
        ]);

        // Buscar order por token no schema do tenant
        $order = Order::where('public_token', $token)
            ->with(['items.product', 'delivery'])
            ->firstOrFail();

        // Verificar se o pedido pertence ao cliente autenticado
        if ($order->customer_id !== $this->getTenantCustomer($request->user())?->id) {
            \Log::warning('⚠️ Tentativa de acesso a pedido de outro cliente via token', [
                'token' => $token,
                'tenant_id' => tenant()->id ?? 'NULL',
                // ⚠️ NÃO logar: customer IDs (LGPD)
            ]);
            return response()->json(['message' => 'Pedido não encontrado.'], 404);
        }

        return response()->json($this->formatOrder($order, true));
    }

    /**
     * Consultar pagamento por ORDER_NUMBER (QR Code PIX)
     */
    public function paymentByOrderNumber(Request $request, string $orderNumber)
    {
        try {
            // LOG SEGURO (dados não sensíveis)
            \Log::info('🔍 Buscando pagamento por order_number', [
                'order_number' => $orderNumber,
                'tenant_id' => tenant()->id ?? 'NULL'
                // ⚠️ NÃO logar: customer_id (LGPD)
            ]);

            // Buscar order por order_number no schema do tenant
            $order = Order::where('order_number', $orderNumber)
                ->with('payments')
                ->firstOrFail();

            // 🔧 CORREÇÃO: Buscar customer do tenant correspondente ao usuário logado
            $loggedUser = $request->user();
            $tenantCustomer = \App\Models\Customer::where('email', $loggedUser->email)
                ->orWhere('phone', $loggedUser->phone)
                ->first();

            // Verificar se o pedido pertence ao cliente autenticado
            if (!$tenantCustomer || $order->customer_id !== $tenantCustomer->id) {
                \Log::warning('⚠️ Tentativa de acesso a pagamento de outro cliente via order_number', [
                    'order_number' => $orderNumber,
                    'tenant_id' => tenant()->id ?? 'NULL',
                    // ⚠️ NÃO logar: customer IDs (LGPD)
                ]);
                return response()->json([
                    'message' => 'Pedido não encontrado.',
                ], 404);
            }

            // 🔒 VALIDAR SE PEDIDO PODE RECEBER PAGAMENTO
            if (!$order->canReceivePayment()) {
                $reason = $order->getPaymentBlockedReason();
                return response()->json([
                    'message' => $reason,
                    'can_pay' => false,
                    'is_expired' => $order->isExpired(),
                    'is_paid' => $order->isPaid(),
                    'restaurant_closed' => !$order->isRestaurantOpen(),
                ], 422);
            }

        } catch (\Exception $e) {
            \Log::error('❌ Erro ao buscar pagamento por order_number', [
                'error' => $e->getMessage(),
                'order_number' => $orderNumber,
                // ⚠️ NÃO logar: trace (pode conter dados sensíveis)
            ]);
            throw $e;
        }

        $payment = $order->payments()->latest()->first();

        if (!$payment) {
            return response()->json([
                'message' => 'Pagamento não encontrado.',
            ], 404);
        }

        $data = [
            'payment_id' => $payment->id,
            'method' => $payment->method,
            'method_name' => $payment->method_name,
            'amount' => $payment->amount,
            'status' => $payment->status,
            'gateway' => $payment->gateway_name,
            'order_number' => $order->order_number,
        ];

        // Se for PIX, retorna QR Code
        if ($payment->isPix() && ($payment->pix_qrcode || $payment->pix_copy_paste)) {
            $data['pix'] = [
                'qrcode_image' => $payment->pix_qrcode, // base64
                'qrcode_text' => $payment->pix_copy_paste,
            ];
        }

        // Se tiver URL de pagamento (boleto, cartão), retorna
        if ($payment->asaas_payment_url) {
            $data['payment_url'] = $payment->asaas_payment_url;
        }

        return response()->json($data);
    }

    /**
     * Consultar pagamento por TOKEN (QR Code PIX) - DEPRECATED
     */
    public function paymentByToken(Request $request, string $token)
    {
        try {
            // LOG SEGURO (dados não sensíveis)
            \Log::info('🔍 Buscando pagamento por token', [
                'tenant_id' => tenant()->id ?? 'NULL'
                // ⚠️ NÃO logar: token, customer_id (LGPD)
            ]);

            // Buscar order por token no schema do tenant
            $order = Order::where('public_token', $token)
                ->with('payments')
                ->firstOrFail();

            // Verificar se o pedido pertence ao cliente autenticado
            if ($order->customer_id !== $this->getTenantCustomer($request->user())?->id) {
                \Log::warning('⚠️ Tentativa de acesso a pagamento de outro cliente via token', [
                    'tenant_id' => tenant()->id ?? 'NULL',
                    // ⚠️ NÃO logar: token, customer IDs (LGPD)
                ]);
                return response()->json([
                    'message' => 'Pedido não encontrado.',
                ], 404);
            }

            // 🔒 VALIDAR SE PEDIDO PODE RECEBER PAGAMENTO
            if (!$order->canReceivePayment()) {
                $reason = $order->getPaymentBlockedReason();
                return response()->json([
                    'message' => $reason,
                    'can_pay' => false,
                    'is_expired' => $order->isExpired(),
                    'is_paid' => $order->isPaid(),
                    'restaurant_closed' => !$order->isRestaurantOpen(),
                ], 422);
            }

        } catch (\Exception $e) {
            \Log::error('❌ Erro ao buscar pagamento por token', [
                'error' => $e->getMessage(),
                // ⚠️ NÃO logar: trace (pode conter dados sensíveis)
            ]);
            throw $e;
        }

        $payment = $order->payments()->latest()->first();

        if (!$payment) {
            return response()->json([
                'message' => 'Pagamento não encontrado.',
            ], 404);
        }

        $data = [
            'payment_id' => $payment->id,
            'method' => $payment->method,
            'method_name' => $payment->method_name,
            'amount' => $payment->amount,
            'status' => $payment->status,
            'gateway' => $payment->gateway_name,
            'order_number' => $order->order_number,
        ];

        // Se for PIX, retorna QR Code
        if ($payment->isPix() && ($payment->pix_qrcode || $payment->pix_copy_paste)) {
            $data['pix'] = [
                'qrcode_image' => $payment->pix_qrcode, // base64
                'qrcode_text' => $payment->pix_copy_paste,
            ];
        }

        // Se tiver URL de pagamento (boleto, cartão), retorna
        if ($payment->asaas_payment_url) {
            $data['payment_url'] = $payment->asaas_payment_url;
        }

        return response()->json($data);
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
