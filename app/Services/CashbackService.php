<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\CashbackTransaction;
use App\Models\CashbackSettings;
use Carbon\Carbon;

class CashbackService
{
    /**
     * Calcula o cashback para um pedido
     */
    public function calculateCashback(Order $order): float
    {
        $settings = CashbackSettings::first();
        
        if (!$settings || !$settings->is_active) {
            return 0.00;
        }

        // Verifica valor mínimo do pedido
        if ($order->total < $settings->min_order_value_to_earn) {
            return 0.00;
        }

        $customer = $order->customer;
        $percentage = $this->getPercentageForTier($customer->loyalty_tier, $settings);

        // Bônus de aniversário
        if ($this->isBirthdayBonus($customer, $settings)) {
            $percentage *= $settings->birthday_multiplier;
        }

        // Calcula cashback sobre o subtotal (antes do desconto/cashback usado)
        $cashbackAmount = ($order->subtotal * $percentage) / 100;

        return round($cashbackAmount, 2);
    }

    /**
     * Adiciona cashback ganho ao saldo do cliente
     */
    public function addEarnedCashback(Order $order, float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $customer = $order->customer;
        $balanceBefore = $customer->cashback_balance;
        $customer->cashback_balance += $amount;
        $customer->save();

        // Registra transação
        $settings = CashbackSettings::first();
        $expiresAt = $settings 
            ? Carbon::now()->addDays($settings->expiration_days) 
            : Carbon::now()->addDays(180);

        CashbackTransaction::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'type' => 'earned',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $customer->cashback_balance,
            'description' => "Cashback ganho no pedido #{$order->order_number}",
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Usa cashback do cliente em um pedido
     */
    public function useCashback(Customer $customer, float $amount): bool
    {
        $settings = CashbackSettings::first();
        
        // Verifica valor mínimo
        if ($settings && $amount < $settings->min_cashback_to_use) {
            return false;
        }

        // Verifica saldo
        if ($customer->cashback_balance < $amount) {
            return false;
        }

        $balanceBefore = $customer->cashback_balance;
        $customer->cashback_balance -= $amount;
        $customer->save();

        return true;
    }

    /**
     * Registra uso de cashback
     */
    public function recordCashbackUsage(Order $order, float $amount): void
    {
        $customer = $order->customer;
        $balanceBefore = $customer->cashback_balance + $amount; // Já foi debitado

        CashbackTransaction::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'type' => 'used',
            'amount' => -$amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $customer->cashback_balance,
            'description' => "Cashback usado no pedido #{$order->order_number}",
        ]);
    }

    /**
     * Atualiza tier do cliente baseado em pedidos/gastos
     */
    public function updateCustomerTier(Customer $customer): void
    {
        $settings = CashbackSettings::first();
        
        if (!$settings) {
            return;
        }

        $totalOrders = $customer->total_orders;
        $totalSpent = $customer->total_spent;

        // Verifica tier de cima para baixo
        if ($totalOrders >= $settings->platinum_min_orders && 
            $totalSpent >= $settings->platinum_min_spent) {
            $customer->loyalty_tier = 'platinum';
        } elseif ($totalOrders >= $settings->gold_min_orders && 
                  $totalSpent >= $settings->gold_min_spent) {
            $customer->loyalty_tier = 'gold';
        } elseif ($totalOrders >= $settings->silver_min_orders && 
                  $totalSpent >= $settings->silver_min_spent) {
            $customer->loyalty_tier = 'silver';
        } else {
            $customer->loyalty_tier = 'bronze';
        }

        $customer->save();
    }

    /**
     * Expira cashback antigo
     */
    public function expireOldCashback(): void
    {
        $expiredTransactions = CashbackTransaction::where('type', 'earned')
            ->whereNull('expired_at')
            ->where('expires_at', '<', Carbon::now())
            ->get();

        foreach ($expiredTransactions as $transaction) {
            $customer = $transaction->customer;
            $balanceBefore = $customer->cashback_balance;
            $customer->cashback_balance -= $transaction->amount;
            $customer->save();

            // Marca como expirado
            $transaction->expired_at = Carbon::now();
            $transaction->save();

            // Cria transação de expiração
            CashbackTransaction::create([
                'customer_id' => $customer->id,
                'type' => 'expired',
                'amount' => -$transaction->amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $customer->cashback_balance,
                'description' => "Cashback expirado",
            ]);
        }
    }

    /**
     * Pega porcentagem do tier
     */
    private function getPercentageForTier(string $tier, CashbackSettings $settings): float
    {
        return match($tier) {
            'bronze' => $settings->bronze_percentage,
            'silver' => $settings->silver_percentage,
            'gold' => $settings->gold_percentage,
            'platinum' => $settings->platinum_percentage,
            default => $settings->bronze_percentage,
        };
    }

    /**
     * Verifica se é aniversário do cliente
     */
    private function isBirthdayBonus(Customer $customer, CashbackSettings $settings): bool
    {
        if (!$settings->birthday_bonus_enabled || !$customer->birth_date) {
            return false;
        }

        $today = Carbon::now();
        $birthDate = Carbon::parse($customer->birth_date);

        return $today->month === $birthDate->month && 
               $today->day === $birthDate->day;
    }
}
