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
        private PagarMeService $pagarmeService
        // AsaasService removido - usar apenas Pagar.me
    ) {}

    /**
     * Cria um novo pedido
     */
    public function createOrder(Customer $customer, array $data): Order
    {
        \Log::info('🔍 Iniciando createOrder', ['customer_id' => $customer->id]);

        return DB::transaction(function () use ($customer, $data) {
            // 🔧 PROTEÇÃO: Garantir que temos customer do TENANT (não do central)
            // Se o customer veio do login central, buscar/criar correspondente no tenant
            $tenantCustomer = Customer::where('email', $customer->email)
                ->orWhere('phone', $customer->phone)
                ->first();

            if (!$tenantCustomer) {
                // Criar customer no tenant se não existir
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
                    'name' => $tenantCustomer->name,
                ]);
            }

            // Usar customer do tenant daqui pra frente
            $customer = $tenantCustomer;

            // Enriquecer items com dados dos produtos
            $enrichedItems = $this->enrichItems($data['items']);

            // Calcula subtotal
            $subtotal = $this->calculateSubtotal($enrichedItems);

            // 🎟️ PROCESSAR CUPOM DE DESCONTO
            $deliveryFee = $data['delivery_fee'] ?? 0;
            $couponDiscount = 0;
            $couponCode = null;

            if (!empty($data['coupon_code'])) {
                $coupon = \App\Models\Coupon::active()
                    ->byCode($data['coupon_code'])
                    ->first();

                if ($coupon) {
                    $orderSubtotal = $subtotal + $deliveryFee;

                    // Verificar valor mínimo
                    if (!$coupon->min_order_value || $orderSubtotal >= $coupon->min_order_value) {
                        // Calcular desconto
                        if ($coupon->type === 'percentage') {
                            $couponDiscount = ($orderSubtotal * $coupon->value) / 100;
                        } else {
                            $couponDiscount = $coupon->value;
                        }

                        // Limita desconto ao total do pedido
                        $couponDiscount = min($couponDiscount, $orderSubtotal);
                        $couponCode = $coupon->code;

                        \Log::info('🎟️ Cupom aplicado', [
                            'code' => $couponCode,
                            'type' => $coupon->type,
                            'value' => $coupon->value,
                            'discount' => $couponDiscount,
                        ]);
                    } else {
                        \Log::warning('⚠️ Cupom não atinge valor mínimo', [
                            'code' => $data['coupon_code'],
                            'min_required' => $coupon->min_order_value,
                            'order_total' => $orderSubtotal,
                        ]);
                    }
                }
            }

            // Calcula total ANTES do cashback (subtotal + entrega - cupom)
            $discount = $couponDiscount;
            $totalBeforeCashback = $subtotal + $deliveryFee - $discount;

            // Cashback usado (limita ao total para não ficar negativo)
            $cashbackUsed = $data['cashback_used'] ?? 0;
            if ($cashbackUsed > 0) {
                // 🎯 Limita cashback ao total do pedido (não pode ficar negativo)
                $cashbackUsed = min($cashbackUsed, $totalBeforeCashback);

                if (!$this->cashbackService->useCashback($customer, $cashbackUsed)) {
                    throw new \Exception('Saldo de cashback insuficiente');
                }

                \Log::info('💰 Cashback aplicado', [
                    'solicitado' => $data['cashback_used'],
                    'aplicado' => $cashbackUsed,
                    'total_antes' => $totalBeforeCashback,
                ]);
            }

            // Calcula total final
            $total = $totalBeforeCashback - $cashbackUsed;

            if ($total < 0) {
                $total = 0;
            }

            // Define expiração do pedido (final do dia)
            $expiresAt = now()->endOfDay();

            // Processar delivery_address (converter array para JSON se necessário)
            $deliveryAddress = $data['delivery_address'] ?? null;
            if (is_array($deliveryAddress)) {
                $deliveryAddress = json_encode($deliveryAddress, JSON_UNESCAPED_UNICODE);
            }

            // Cria pedido
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $customer->id,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'discount' => $discount,
                'coupon_code' => $couponCode, // ⭐ Código do cupom
                'cashback_used' => $cashbackUsed,
                'total' => $total,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $data['payment_method'] ?? null,
                'delivery_type' => $data['delivery_type'] ?? 'delivery',
                'delivery_address' => $deliveryAddress,
                'delivery_city' => $data['delivery_city'] ?? null,
                'delivery_neighborhood' => $data['delivery_neighborhood'] ?? null,
                'customer_notes' => $data['notes'] ?? null,
                'cashback_earned' => 0,
                'cashback_percentage' => 0,
                'expires_at' => $expiresAt,
            ]);

            \Log::info('✅ Pedido criado', ['order_id' => $order->id, 'order_number' => $order->order_number]);

            // Cria itens do pedido
            foreach ($enrichedItems as $itemData) {
                $this->createOrderItem($order, $itemData);
            }

            \Log::info('✅ Items criados');

            // 🎟️ Incrementar contador de uso do cupom
            if ($couponCode) {
                \App\Models\Coupon::where('code', $couponCode)->increment('usage_count');
                \Log::info('🎟️ Contador do cupom incrementado', ['code' => $couponCode]);
            }

            // ⭐ PAGAMENTO: Criar cobrança apenas para PIX (cartão será processado na página de pagamento)
            if ($data['payment_method'] === 'pix') {
                try {
                    // PROTEÇÃO: Garantir que customer tem relação carregada
                    $order->load('customer');

                    // Determinar qual gateway usar (Pagar.me por padrão)
                    $tenant = tenant();
                    $gateway = $tenant->payment_gateway ?? 'pagarme';

                    \Log::info('💳 Criando pagamento PIX', [
                        'gateway' => $gateway,
                        'method' => $data['payment_method'],
                        'tenant_id' => $tenant->id,
                    ]);

                    // Criar pagamento no Pagar.me
                    $payment = $this->pagarmeService->createPayment($order, [
                        'payment_method' => $data['payment_method']
                    ]);

                    // Se for PIX, buscar QR Code (segunda chamada necessária no Pagar.me)
                    $pixQrCode = null;
                    $pixCopyPaste = null;
                    $pixExpiresAt = null;

                    if ($data['payment_method'] === 'pix' && isset($payment['id'])) {
                        // Pagar.me precisa de segunda chamada para obter QR Code
                        $qrCodeData = $this->pagarmeService->getPixQrCode($payment['id']);
                        if ($qrCodeData && isset($qrCodeData['encodedImage'])) {
                            $pixQrCode = $qrCodeData['encodedImage'];
                            $pixCopyPaste = $qrCodeData['payload'] ?? null;
                            $pixExpiresAt = isset($qrCodeData['expirationDate'])
                                ? Carbon::parse($qrCodeData['expirationDate'])
                                : null;
                        }

                        \Log::info('✅ QR Code PIX obtido', [
                            'gateway' => $gateway,
                            'payment_id' => $payment['id'],
                            'has_image' => !empty($pixQrCode),
                            'has_payload' => !empty($pixCopyPaste),
                        ]);
                    }

                    $paymentRecord = \App\Models\Payment::create([
                        'order_id' => $order->id,
                        'gateway' => $gateway,
                        'method' => $data['payment_method'],
                        'transaction_id' => $payment['id'],
                        'amount' => $order->total,
                        'fee' => 0,
                        'net_amount' => $order->total,
                        'status' => 'pending',
                        'pix_qrcode' => $pixQrCode,
                        'pix_copy_paste' => $pixCopyPaste,
                        // Pagar.me não usa payment_url como Asaas
                    ]);

                    \Log::info('✅ Payment criado', [
                        'payment_id' => $paymentRecord->id,
                        'has_qrcode' => !empty($paymentRecord->pix_qrcode),
                        'has_code' => !empty($paymentRecord->pix_copy_paste),
                    ]);

                    \Log::info('✅ Pagamento criado', ['gateway' => $gateway]);
                } catch (\Exception $e) {
                    \Log::error('❌ Erro ao criar pagamento (PEDIDO CRIADO, pagamento pendente)', [
                        'gateway' => $gateway ?? 'unknown',
                        'error' => $e->getMessage(),
                        'order_id' => $order->id,
                        'payment_method' => $data['payment_method'],
                    ]);

                    // FALLBACK: Criar registro de pagamento pendente mesmo com erro no gateway
                    // O pedido já foi criado, apenas marca pagamento como pendente
                    \App\Models\Payment::create([
                        'order_id' => $order->id,
                        'gateway' => $gateway ?? 'pagarme',
                        'method' => $data['payment_method'],
                        'transaction_id' => 'PENDING_' . $order->id,
                        'amount' => $order->total,
                        'fee' => 0,
                        'net_amount' => $order->total,
                        'status' => 'pending',
                        'metadata' => json_encode(['gateway_error' => $e->getMessage()]),
                    ]);

                    \Log::warning('⚠️ Pagamento criado em modo FALLBACK (manual)', [
                        'order_id' => $order->id,
                    ]);

                    // NÃO lançar exceção - pedido foi criado com sucesso
                    // throw $e;
                }
            }

            return $order;
        });
    }

    /**
     * Confirma pagamento e adiciona cashback
     *
     * ⚠️ IMPORTANTE: Cashback só é gerado APÓS confirmação de pagamento
     * Este método deve ser chamado apenas quando o pagamento for APROVADO
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
