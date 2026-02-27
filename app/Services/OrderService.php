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
        private AsaasService $asaasService
    ) {}

    /**
     * Cria um novo pedido
     */
    public function createOrder(Customer $customer, array $data): Order
    {
        \Log::info('🔍 Iniciando createOrder', ['customer_id' => $customer->id]);

        return DB::transaction(function () use ($customer, $data) {
            // Enriquecer items com dados dos produtos
            $enrichedItems = $this->enrichItems($data['items']);

            // Calcula subtotal
            $subtotal = $this->calculateSubtotal($enrichedItems);

            // Cashback usado (se o cliente quiser usar saldo)
            $cashbackUsed = $data['cashback_used'] ?? 0;
            if ($cashbackUsed > 0) {
                if (!$this->cashbackService->useCashback($customer, $cashbackUsed)) {
                    throw new \Exception('Saldo de cashback insuficiente');
                }
            }

            // Calcula total
            $deliveryFee = $data['delivery_fee'] ?? 0;
            $discount = $data['discount'] ?? 0;
            $total = $subtotal + $deliveryFee - $discount - $cashbackUsed;

            if ($total < 0) {
                $total = 0;
            }

            // Cria pedido
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $customer->id,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'discount' => $discount,
                'cashback_used' => $cashbackUsed,
                'total' => $total,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $data['payment_method'] ?? null,
                'delivery_type' => $data['delivery_type'] ?? 'delivery',
                'delivery_address' => $data['delivery_address'] ?? null,
                'delivery_city' => $data['delivery_city'] ?? null,
                'delivery_neighborhood' => $data['delivery_neighborhood'] ?? null,
                'customer_notes' => $data['notes'] ?? null,
                'cashback_earned' => 0,
                'cashback_percentage' => 0,
            ]);

            \Log::info('✅ Pedido criado', ['order_id' => $order->id, 'order_number' => $order->order_number]);

            // Cria itens do pedido
            foreach ($enrichedItems as $itemData) {
                $this->createOrderItem($order, $itemData);
            }

            \Log::info('✅ Items criados');

            // Se for pagamento online (PIX ou cartão), criar cobrança no Asaas
            if (in_array($data['payment_method'], ['pix', 'credit_card', 'debit_card'])) {
                try {
                    \Log::info('💳 Criando pagamento Asaas', ['method' => $data['payment_method']]);

                    // PROTEÇÃO: Garantir que customer tem relação carregada
                    $order->load('customer');

                    $payment = $this->asaasService->createPayment($order, [
                        'payment_method' => $data['payment_method']
                    ]);

                    // Se for PIX, buscar QR Code (segunda chamada necessária)
                    $pixQrCode = null;
                    $pixCopyPaste = null;
                    $pixExpiresAt = null;

                    if ($data['payment_method'] === 'pix' && isset($payment['id'])) {
                        $qrCodeData = $this->asaasService->getPixQrCode($payment['id']);
                        if ($qrCodeData && isset($qrCodeData['encodedImage'])) {
                            $pixQrCode = $qrCodeData['encodedImage'];
                            $pixCopyPaste = $qrCodeData['payload'] ?? null;
                            $pixExpiresAt = isset($qrCodeData['expirationDate'])
                                ? Carbon::parse($qrCodeData['expirationDate'])
                                : null;

                            \Log::info('✅ QR Code PIX obtido', [
                                'payment_id' => $payment['id'],
                                'has_image' => !empty($pixQrCode),
                                'has_payload' => !empty($pixCopyPaste),
                            ]);
                        }
                    }

                    $paymentRecord = \App\Models\Payment::create([
                        'order_id' => $order->id,
                        'gateway' => 'asaas',
                        'method' => $data['payment_method'],
                        'transaction_id' => $payment['id'],
                        'amount' => $order->total,
                        'fee' => 0,
                        'net_amount' => $order->total,
                        'status' => 'pending',
                        'pix_qrcode' => $pixQrCode,
                        'pix_copy_paste' => $pixCopyPaste,
                        'asaas_payment_url' => $payment['invoiceUrl'] ?? null,
                    ]);

                    \Log::info('✅ Payment criado', [
                        'payment_id' => $paymentRecord->id,
                        'has_qrcode' => !empty($paymentRecord->pix_qrcode),
                        'has_code' => !empty($paymentRecord->pix_copy_paste),
                    ]);

                    \Log::info('✅ Pagamento Asaas criado');
                } catch (\Exception $e) {
                    \Log::error('❌ Erro ao criar pagamento Asaas (PEDIDO CRIADO, pagamento pendente)', [
                        'error' => $e->getMessage(),
                        'order_id' => $order->id,
                        'payment_method' => $data['payment_method'],
                    ]);

                    // FALLBACK: Criar registro de pagamento pendente mesmo sem Asaas
                    // O pedido já foi criado, apenas marca pagamento como pendente
                    \App\Models\Payment::create([
                        'order_id' => $order->id,
                        'gateway' => 'asaas',
                        'method' => $data['payment_method'],
                        'transaction_id' => 'PENDING_' . $order->id,
                        'amount' => $order->total,
                        'fee' => 0,
                        'net_amount' => $order->total,
                        'status' => 'pending',
                        'metadata' => json_encode(['asaas_error' => $e->getMessage()]),
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
     */
    public function confirmPayment(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'confirmed',
            ]);

            // Calcula e adiciona cashback ganho (se ainda não calculado)
            if ($order->cashback_earned == 0) {
                $cashbackEarned = $this->cashbackService->calculateCashback($order);
                if ($cashbackEarned > 0) {
                    $order->update(['cashback_earned' => $cashbackEarned]);
                    $this->cashbackService->addEarnedCashback($order, $cashbackEarned);
                }
            } else {
                // Se já foi calculado, apenas adiciona
                $this->cashbackService->addEarnedCashback($order, $order->cashback_earned);
            }

            // Atualiza estatísticas do cliente
            $customer = $order->customer;
            $customer->total_orders += 1;
            $customer->total_spent += $order->total;
            $customer->save();

            // Atualiza tier do cliente
            $this->cashbackService->updateCustomerTier($customer);
        });
    }

    /**
     * Cancela pedido e devolve cashback usado
     */
    public function cancelOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            // Devolve cashback usado
            if ($order->cashback_used > 0) {
                $customer = $order->customer;
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
                    'description' => "Devolução de cashback - Pedido #{$order->order_number} cancelado",
                ]);
            }

            $order->update([
                'status' => 'canceled',
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
