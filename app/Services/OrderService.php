<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Customer;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderService
{
    public function __construct(
        private CashbackService $cashbackService,
        private PagarMeService $pagarmeService,
        private AsaasService $asaasService
    ) {}

    /**
     * Cria um novo pedido com cashback e pagamento integrado
     *
     * Fluxo completo:
     * 1. Sincroniza customer entre schema central e tenant
     * 2. Enriquece items com dados atualizados dos produtos
     * 3. Calcula subtotal + taxa de entrega
     * 4. Aplica cupom de desconto (se fornecido)
     * 5. Aplica cashback (se solicitado)
     * 6. Cria cobrança no gateway (Pagar.me)
     * 7. Registra pedido no banco
     *
     * @param Customer $customer Cliente (pode ser do central ou tenant)
     * @param array $data Dados do pedido
     *   - items: array [product_id, quantity, variation_id?, addons?, notes?]
     *   - delivery_address: string (obrigatório)
     *   - delivery_city: string (obrigatório)
     *   - delivery_neighborhood: string (obrigatório)
     *   - delivery_fee: float (calculado no backend)
     *   - payment_method: string (pix|credit_card|debit_card|cash)
     *   - cashback_used: float (calculado no backend)
     *   - coupon_code: string|null
     *   - notes: string|null
     *
     * @return Order Pedido criado com payment anexado
     *
     * @throws \Exception Se falhar ao criar cobrança no gateway
     * @throws \Exception Se falhar ao debitar cashback
     *
     * @see CashbackService::useCashback() Para lógica de débito de cashback
     * @see PagarMeService::createCharge() Para criação de cobrança
     */
    public function createOrder(Customer $customer, array $data): Order
    {
        \Log::info('🔍 Iniciando createOrder', ['customer_id' => $customer->id]);

        return DB::transaction(function () use ($customer, $data) {
            // 1. Sincronizar customer entre schemas
            $customer = $this->syncCustomer($customer);

            // 2. Enriquecer items e calcular subtotal
            $enrichedItems = $this->enrichItems($data['items']);
            $subtotal = $this->calculateSubtotal($enrichedItems);

            // 3. Processar cupom de desconto
            $couponResult = $this->processCouponDiscount(
                $data['coupon_code'] ?? null,
                $subtotal,
                $data['delivery_fee'] ?? 0
            );

            // 4. Aplicar cashback
            $cashbackUsed = $this->applyCashback(
                $customer,
                $data['cashback_used'] ?? 0,
                $subtotal + ($data['delivery_fee'] ?? 0) - $couponResult['discount']
            );

            // 5. Calcular totais do pedido
            $orderTotals = $this->calculateOrderTotals(
                $subtotal,
                $data['delivery_fee'] ?? 0,
                $couponResult['discount'],
                $cashbackUsed
            );

            // 6. Preparar dados do pedido
            $orderData = $this->buildOrderData($customer, $data, $orderTotals, $couponResult['code'], $cashbackUsed);

            // 7. Criar pedido
            $order = Order::create($orderData);
            \Log::info('✅ Pedido criado', ['order_id' => $order->id, 'order_number' => $order->order_number]);

            // 8. Criar itens do pedido
            foreach ($enrichedItems as $itemData) {
                $this->createOrderItem($order, $itemData);
            }
            \Log::info('✅ Items criados');

            // 9. Criar pagamento PIX (se aplicável)
            if ($data['payment_method'] === 'pix') {
                $this->createPaymentForPix($order, $data['payment_method']);
            }

            return $order;
        });
    }

    /**
     * Sincroniza customer entre schema central e tenant
     */
    private function syncCustomer(Customer $customer): Customer
    {
        $tenantCustomer = Customer::where('email', $customer->email)
            ->orWhere('phone', $customer->phone)
            ->first();

        if (!$tenantCustomer) {
            $tenantCustomer = Customer::create([
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'cpf' => $customer->cpf ?? null,
                'cashback_balance' => 0,
                'loyalty_tier' => 'bronze',
                'total_orders' => 0,
                'total_spent' => 0,
                'is_active' => true,
            ]);

            \Log::info('✨ Customer criado no tenant', [
                'central_id' => $customer->id,
                'tenant_id' => $tenantCustomer->id,
            ]);
        }

        return $tenantCustomer;
    }

    /**
     * Processa cupom de desconto e retorna dados do cupom
     */
    private function processCouponDiscount(?string $couponCode, float $subtotal, float $deliveryFee): array
    {
        if (empty($couponCode)) {
            return ['code' => null, 'discount' => 0];
        }

        $coupon = \App\Models\Coupon::active()->byCode($couponCode)->first();

        if (!$coupon) {
            return ['code' => null, 'discount' => 0];
        }

        $orderSubtotal = $subtotal + $deliveryFee;

        // Verificar valor mínimo
        if ($coupon->min_order_value && $orderSubtotal < $coupon->min_order_value) {
            \Log::warning('⚠️ Cupom não atinge valor mínimo', [
                'code' => $couponCode,
                'min_required' => $coupon->min_order_value,
                'order_total' => $orderSubtotal,
            ]);
            return ['code' => null, 'discount' => 0];
        }

        // Calcular desconto
        $discount = $coupon->type === 'percentage'
            ? ($orderSubtotal * $coupon->value) / 100
            : $coupon->value;

        // Limitar desconto ao total do pedido
        $discount = min($discount, $orderSubtotal);

        \Log::info('🎟️ Cupom aplicado', [
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'discount' => $discount,
        ]);

        return ['code' => $coupon->code, 'discount' => $discount];
    }

    /**
     * Aplica cashback e debita do saldo do cliente
     */
    private function applyCashback(Customer $customer, float $requestedAmount, float $totalBeforeCashback): float
    {
        if ($requestedAmount <= 0) {
            return 0;
        }

        // Limitar cashback ao total do pedido
        $cashbackUsed = min($requestedAmount, $totalBeforeCashback);

        if (!$this->cashbackService->useCashback($customer, $cashbackUsed)) {
            throw new \Exception('Saldo de cashback insuficiente');
        }

        \Log::info('💰 Cashback aplicado', [
            'solicitado' => $requestedAmount,
            'aplicado' => $cashbackUsed,
            'total_antes' => $totalBeforeCashback,
        ]);

        return $cashbackUsed;
    }

    /**
     * Calcula totais do pedido
     */
    private function calculateOrderTotals(float $subtotal, float $deliveryFee, float $discount, float $cashbackUsed): array
    {
        $totalBeforeCashback = $subtotal + $deliveryFee - $discount;
        $total = $totalBeforeCashback - $cashbackUsed;

        if ($total < 0) {
            throw new \Exception('Cashback superior ao total do pedido');
        }

        return [
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'discount' => $discount,
            'cashback_used' => $cashbackUsed,
            'total' => $total,
        ];
    }

    /**
     * Prepara array de dados para criação do pedido
     */
    private function buildOrderData(Customer $customer, array $data, array $totals, ?string $couponCode, float $cashbackUsed): array
    {
        $deliveryAddress = $data['delivery_address'] ?? null;
        if (is_array($deliveryAddress)) {
            $deliveryAddress = json_encode($deliveryAddress, JSON_UNESCAPED_UNICODE);
        }

        // ⭐ Define payment_status baseado no método de pagamento
        $paymentMethod = $data['payment_method'] ?? null;
        $paymentStatus = in_array($paymentMethod, ['cash', 'debit_card'])
            ? 'awaiting_delivery' // Pagar na entrega - imprime mas não pago ainda
            : 'pending'; // PIX, cartão - aguarda confirmação de pagamento

        return [
            'order_number' => $this->generateOrderNumber(),
            'customer_id' => $customer->id,
            'cash_register_id' => $data['cash_register_id'] ?? null, // ✅ Vincular ao caixa
            'subtotal' => $totals['subtotal'],
            'delivery_fee' => $totals['delivery_fee'],
            'discount' => $totals['discount'],
            'coupon_code' => $couponCode,
            'cashback_used' => $cashbackUsed,
            'total' => $totals['total'],
            'status' => 'pending',
            'payment_status' => $paymentStatus, // ⭐ 'awaiting_delivery' ou 'pending'
            'payment_method' => $paymentMethod,
            'delivery_type' => $data['delivery_type'] ?? 'delivery',
            'delivery_address' => $deliveryAddress,
            'delivery_city' => $data['delivery_city'] ?? null,
            'delivery_neighborhood' => $data['delivery_neighborhood'] ?? null,
            'customer_notes' => $data['notes'] ?? null,
            'cashback_earned' => 0,
            'cashback_percentage' => 0,
            'expires_at' => now()->endOfDay(),
        ];
    }

    /**
     * Cria pagamento PIX via Pagar.me
     */
    private function createPaymentForPix(Order $order, string $paymentMethod): void
    {
        $tenant = tenant();
        $gateway = $tenant->payment_gateway ?? 'pagarme';

        try {
            $order->load('customer');

            \Log::info('💳 Criando pagamento PIX', [
                'gateway' => $gateway,
                'order_id' => $order->id,
            ]);

            // Criar pagamento no gateway apropriado
            $payment = $gateway === 'asaas'
                ? $this->asaasService->createPayment($order, ['payment_method' => $paymentMethod])
                : $this->pagarmeService->createPayment($order, ['payment_method' => $paymentMethod]);

            // Obter QR Code do PIX
            $pixData = $this->getPixQrCode($payment['id'] ?? null, $gateway);

            // Criar registro de pagamento
            \App\Models\Payment::create([
                'order_id' => $order->id,
                'gateway' => $gateway,
                'method' => $paymentMethod,
                'transaction_id' => $payment['id'],
                'amount' => $order->total,
                'fee' => 0,
                'net_amount' => $order->total,
                'status' => 'pending',
                'pix_qrcode' => $pixData['qrcode'],
                'pix_copy_paste' => $pixData['copy_paste'],
            ]);

            \Log::info('✅ Pagamento PIX criado', [
                'order_id' => $order->id,
                'has_qrcode' => !empty($pixData['qrcode']),
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Erro ao criar pagamento PIX', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            // Fallback: criar registro pendente
            \App\Models\Payment::create([
                'order_id' => $order->id,
                'gateway' => $gateway,
                'method' => $paymentMethod,
                'transaction_id' => 'PENDING_' . $order->id,
                'amount' => $order->total,
                'fee' => 0,
                'net_amount' => $order->total,
                'status' => 'pending',
                'metadata' => json_encode(['gateway_error' => $e->getMessage()]),
            ]);

            \Log::warning('⚠️ Pagamento criado em modo FALLBACK', ['order_id' => $order->id]);
        }
    }

    /**
     * Obtém dados do QR Code PIX
     */
    private function getPixQrCode(?string $paymentId, string $gateway): array
    {
        if (!$paymentId) {
            return ['qrcode' => null, 'copy_paste' => null];
        }

        try {
            // Usar o gateway apropriado
            $qrCodeData = $gateway === 'asaas'
                ? $this->asaasService->getPixQrCode($paymentId)
                : $this->pagarmeService->getPixQrCode($paymentId);

            if ($qrCodeData && isset($qrCodeData['encodedImage'])) {
                \Log::info('✅ QR Code PIX obtido', [
                    'payment_id' => $paymentId,
                    'has_image' => true,
                ]);

                return [
                    'qrcode' => $qrCodeData['encodedImage'],
                    'copy_paste' => $qrCodeData['payload'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            \Log::warning('⚠️ Erro ao obter QR Code PIX', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
        }

        return ['qrcode' => null, 'copy_paste' => null];
    }

    /**
     * Confirma pagamento e credita cashback ao cliente
     *
     * Chamado quando pagamento é aprovado pelo gateway (webhook)
     *
     * Fluxo:
     * 1. Verifica se pagamento já não foi confirmado (idempotência)
     * 2. Atualiza status do pedido (payment_status = 'paid', status = 'confirmed')
     * 3. Calcula e credita cashback ao cliente
     * 4. Atualiza estatísticas do tenant (total_orders, total_revenue)
     *
     * ⚠️ IMPORTANTE: Cashback só é gerado APÓS confirmação de pagamento
     * ⚠️ IDEMPOTENTE: Pode ser chamado múltiplas vezes (previne duplicação)
     *
     * @param Order $order Pedido a ser confirmado
     * @return void
     *
     * @throws \Exception Se falhar ao creditar cashback
     *
     * @see CashbackService::awardCashback() Para lógica de crédito
     */
    public function confirmPayment(Order $order): void
    {
        DB::transaction(function () use ($order) {
            // ⚠️ PROTEÇÃO: Não processar se já estiver pago (evita duplicação)
            if ($order->payment_status === 'paid') {
                \Log::warning('⚠️ Tentativa de confirmar pagamento já pago', [
                    'order_number' => $order->order_number,
                    'payment_status' => $order->payment_status,
                ]);
                return;
            }

            // ⚠️ PROTEÇÃO: Não processar se pedido estiver cancelado
            if ($order->status === 'canceled') {
                \Log::error('❌ Tentativa de confirmar pagamento de pedido cancelado', [
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                ]);
                return;
            }

            // ✅ Marca pagamento como confirmado
            $order->update([
                'payment_status' => 'paid',
                'status' => 'confirmed',
            ]);

            \Log::info('✅ Pagamento confirmado', [
                'order_number' => $order->order_number,
                'total' => $order->total,
            ]);

            // 💰 CASHBACK: Só gera APÓS pagamento confirmado
            if ($order->cashback_earned == 0) {
                $cashbackEarned = $this->cashbackService->calculateCashback($order);
                if ($cashbackEarned > 0) {
                    $order->update(['cashback_earned' => $cashbackEarned]);
                    $this->cashbackService->addEarnedCashback($order, $cashbackEarned);

                    \Log::info('💰 Cashback creditado', [
                        'order_number' => $order->order_number,
                        'customer_id' => $order->customer_id,
                        'amount' => $cashbackEarned,
                    ]);
                }
            } else {
                // Se já foi calculado (raro), apenas adiciona
                $this->cashbackService->addEarnedCashback($order, $order->cashback_earned);

                \Log::info('💰 Cashback creditado (já calculado)', [
                    'order_number' => $order->order_number,
                    'amount' => $order->cashback_earned,
                ]);
            }

            // 📊 Atualiza estatísticas do cliente
            $customer = $order->customer;
            $customer->total_orders += 1;
            $customer->total_spent += $order->total;
            $customer->save();

            // ⭐ Atualiza tier do cliente (Bronze → Prata → Ouro)
            $this->cashbackService->updateCustomerTier($customer);

            \Log::info('📊 Estatísticas atualizadas', [
                'customer_id' => $customer->id,
                'total_orders' => $customer->total_orders,
                'total_spent' => $customer->total_spent,
                'tier' => $customer->loyalty_tier,
            ]);
        });
    }

    /**
     * Cancela pedido e estorna cashback
     *
     * REGRAS DE ESTORNO:
     * 1. Devolve cashback USADO pelo cliente (se houver)
     * 2. Remove cashback GANHO se pagamento foi confirmado (previne fraude)
     */
    public function cancelOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $customer = $order->customer;

            // 1️⃣ ESTORNO: Devolve cashback USADO pelo cliente
            if ($order->cashback_used > 0) {
                $balanceBefore = $customer->cashback_balance;
                $customer->cashback_balance += $order->cashback_used;
                $customer->save();

                \App\Models\CashbackTransaction::create([
                    'customer_id' => $customer->id,
                    'order_id' => $order->id,
                    'type' => 'earned',
                    'amount' => $order->cashback_used,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $customer->cashback_balance,
                    'description' => "Estorno de cashback usado - Pedido #{$order->order_number} cancelado",
                ]);

                \Log::info('💰 Cashback usado devolvido', [
                    'order_number' => $order->order_number,
                    'customer_id' => $customer->id,
                    'amount' => $order->cashback_used,
                ]);
            }

            // 2️⃣ ESTORNO: Remove cashback GANHO se pagamento foi confirmado
            // ⚠️ PROTEÇÃO: Só remove se o pedido estava PAGO (evita remover cashback não creditado)
            if ($order->cashback_earned > 0 && $order->payment_status === 'paid') {
                $balanceBefore = $customer->cashback_balance;
                $customer->cashback_balance -= $order->cashback_earned;

                // Previne saldo negativo
                if ($customer->cashback_balance < 0) {
                    $customer->cashback_balance = 0;
                }

                $customer->save();

                \App\Models\CashbackTransaction::create([
                    'customer_id' => $customer->id,
                    'order_id' => $order->id,
                    'type' => 'used',
                    'amount' => $order->cashback_earned,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $customer->cashback_balance,
                    'description' => "Estorno de cashback ganho - Pedido #{$order->order_number} cancelado após pagamento",
                ]);

                \Log::warning('⚠️ Cashback ganho removido (cancelamento pós-pagamento)', [
                    'order_number' => $order->order_number,
                    'customer_id' => $customer->id,
                    'amount' => $order->cashback_earned,
                    'payment_status' => $order->payment_status,
                ]);
            }

            // 3️⃣ Atualiza estatísticas do cliente (remove pedido das contagens)
            if ($order->payment_status === 'paid') {
                $customer->total_orders = max(0, $customer->total_orders - 1);
                $customer->total_spent = max(0, $customer->total_spent - $order->total);
                $customer->save();

                \Log::info('📊 Estatísticas do cliente atualizadas', [
                    'customer_id' => $customer->id,
                    'total_orders' => $customer->total_orders,
                    'total_spent' => $customer->total_spent,
                ]);
            }

            // 4️⃣ Marca pedido como cancelado
            $order->update([
                'status' => 'canceled',
                'payment_status' => 'canceled', // Marca pagamento também
            ]);

            \Log::info('🔴 Pedido cancelado', [
                'order_number' => $order->order_number,
                'cashback_used_returned' => $order->cashback_used,
                'cashback_earned_removed' => ($order->payment_status === 'paid' ? $order->cashback_earned : 0),
            ]);
        });
    }

    /**
     * Enriquece items com dados dos produtos e calcula preços
     * PROTEÇÃO: Ignora completamente preços/quantidades do frontend
     */
    private function enrichItems(array $items): array
    {
        $enriched = [];

        foreach ($items as $item) {
            // PROTEÇÃO: Validar e sanitizar IDs
            $productId = filter_var($item['product_id'] ?? 0, FILTER_VALIDATE_INT);
            if (!$productId || $productId <= 0) {
                throw new \Exception("ID de produto inválido");
            }

            $product = \App\Models\Product::find($productId);
            if (!$product) {
                throw new \Exception("Produto #{$productId} não encontrado");
            }

            // PROTEÇÃO: Verificar se produto está ativo
            if (!$product->is_active) {
                throw new \Exception("Produto #{$productId} não está ativo");
            }

            // PROTEÇÃO: Sempre usar preço do banco de dados
            $unitPrice = (float) $product->price;

            // Se tem variação (tamanho), ajusta preço
            if (isset($item['variation_id'])) {
                $variationId = filter_var($item['variation_id'], FILTER_VALIDATE_INT);
                if (!$variationId) {
                    throw new \Exception("ID de variação inválido");
                }

                $variation = \App\Models\ProductVariation::where('product_id', $productId)
                    ->find($variationId);

                if (!$variation) {
                    throw new \Exception("Variação não pertence ao produto");
                }

                $unitPrice = (float) $variation->getFinalPrice($product->price);
            }

            // Se é pizza meio a meio
            if (isset($item['half_and_half']) && !empty($item['half_and_half'])) {
                $secondProductId = filter_var($item['half_and_half']['product_id'] ?? 0, FILTER_VALIDATE_INT);
                if (!$secondProductId) {
                    throw new \Exception("ID do segundo sabor inválido");
                }

                $secondProduct = \App\Models\Product::find($secondProductId);
                if (!$secondProduct) {
                    throw new \Exception("Segundo sabor não encontrado");
                }

                if (!$secondProduct->is_active) {
                    throw new \Exception("Segundo sabor não está ativo");
                }

                $secondPrice = (float) $secondProduct->price;

                if (isset($item['half_and_half']['variation_id'])) {
                    $secondVarId = filter_var($item['half_and_half']['variation_id'], FILTER_VALIDATE_INT);
                    if (!$secondVarId) {
                        throw new \Exception("Variação do segundo sabor inválida");
                    }

                    $variation = \App\Models\ProductVariation::where('product_id', $secondProductId)
                        ->find($secondVarId);

                    if (!$variation) {
                        throw new \Exception("Variação não pertence ao segundo sabor");
                    }

                    $secondPrice = (float) $variation->getFinalPrice($secondProduct->price);
                }

                // Cobra pelo maior preço
                $unitPrice = max($unitPrice, $secondPrice);
            }

            // PROTEÇÃO: Validar quantidade (não confiar no frontend)
            $quantity = filter_var($item['quantity'] ?? 1, FILTER_VALIDATE_INT);
            if (!$quantity || $quantity <= 0 || $quantity > 50) {
                throw new \Exception("Quantidade inválida (min: 1, max: 50)");
            }

            // PROTEÇÃO: Sanitizar observações (prevenir XSS)
            $notes = isset($item['notes']) ? htmlspecialchars(substr($item['notes'], 0, 500), ENT_QUOTES, 'UTF-8') : null;

            $enriched[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_variation_id' => $item['variation_id'] ?? null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'addons' => [], // TODO: Validar addons posteriormente
                'half_and_half' => $item['half_and_half'] ?? null,
                'notes' => $notes,
            ];
        }

        return $enriched;
    }

    /**
     * Calcula subtotal dos itens
     */
    private function calculateSubtotal(array $items): float
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $itemTotal = $item['unit_price'] * $item['quantity'];

            // Adiciona preço dos adicionais
            if (isset($item['addons']) && is_array($item['addons'])) {
                foreach ($item['addons'] as $addonId) {
                    $addon = \App\Models\ProductAddon::find($addonId);
                    if ($addon) {
                        $itemTotal += $addon->price;
                    }
                }
            }

            $subtotal += $itemTotal;
        }

        return round($subtotal, 2);
    }

    /**
     * Cria item do pedido
     */
    private function createOrderItem(Order $order, array $data): OrderItem
    {
        $subtotal = $data['unit_price'] * $data['quantity'];

        // Adiciona preço dos adicionais
        if (isset($data['addons']) && is_array($data['addons'])) {
            foreach ($data['addons'] as $addonId) {
                $addon = \App\Models\ProductAddon::find($addonId);
                if ($addon) {
                    $subtotal += $addon->price;
                }
            }
        }

        return \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $data['product_id'],
            'product_variation_id' => $data['product_variation_id'] ?? null,
            'product_name' => $data['product_name'],
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
            'subtotal' => round($subtotal, 2),
            'addons' => json_encode($data['addons'] ?? []),
            'half_and_half' => json_encode($data['half_and_half'] ?? null),
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Gera número único do pedido
     */
    private function generateOrderNumber(): string
    {
        $date = Carbon::now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -6));
        
        return "{$date}-{$random}";
    }
}
