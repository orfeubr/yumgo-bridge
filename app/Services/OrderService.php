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
        private CashbackService $cashbackService
    ) {}

    /**
     * Cria um novo pedido
     */
    public function createOrder(Customer $customer, array $data): Order
    {
        return DB::transaction(function () use ($customer, $data) {
            // Calcula subtotal
            $subtotal = $this->calculateSubtotal($data['items']);

            // Valida cashback usado
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
                'delivery_type' => $data['delivery_type'] ?? 'delivery',
                'delivery_address' => $data['delivery_address'] ?? null,
                'customer_notes' => $data['notes'] ?? null,
            ]);

            // Cria itens do pedido
            foreach ($data['items'] as $itemData) {
                $this->createOrderItem($order, $itemData);
            }

            // Registra uso de cashback
            if ($cashbackUsed > 0) {
                $this->cashbackService->recordCashbackUsage($order, $cashbackUsed);
            }

            // Calcula cashback ganho
            $cashbackEarned = $this->cashbackService->calculateCashback($order);
            $settings = \App\Models\CashbackSettings::first();
            
            $order->update([
                'cashback_earned' => $cashbackEarned,
                'cashback_percentage' => $settings 
                    ? $this->cashbackService->getPercentageForTier($customer->loyalty_tier, $settings) 
                    : 0,
            ]);

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

            // Adiciona cashback ganho
            if ($order->cashback_earned > 0) {
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
     * Calcula subtotal dos itens
     */
    private function calculateSubtotal(array $items): float
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $itemTotal = $item['unit_price'] * $item['quantity'];
            
            // Adiciona preço dos adicionais
            if (isset($item['addons']) && is_array($item['addons'])) {
                foreach ($item['addons'] as $addon) {
                    $itemTotal += ($addon['price'] * ($addon['quantity'] ?? 1));
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
            foreach ($data['addons'] as $addon) {
                $subtotal += ($addon['price'] * ($addon['quantity'] ?? 1));
            }
        }

        return OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $data['product_id'],
            'product_variation_id' => $data['product_variation_id'] ?? null,
            'product_name' => $data['product_name'],
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
            'subtotal' => round($subtotal, 2),
            'addons' => $data['addons'] ?? null,
            'half_and_half' => $data['half_and_half'] ?? null,
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
