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
     * SIMPLIFICADO: Todos os clientes ganham o mesmo percentual
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

        // Usa bronze_percentage como percentual único para todos
        $percentage = (float) $settings->bronze_percentage;

        // Bônus de aniversário
        $customer = $order->customer;
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

        // Verifica saldo
        if ($customer->cashback_balance < $amount) {
            \Log::warning('❌ Saldo de cashback insuficiente', [
                'customer_id' => $customer->id,
                'saldo_disponivel' => $customer->cashback_balance,
                'tentou_usar' => $amount,
            ]);
            return false;
        }

        // ⭐ CORREÇÃO: Verifica valor mínimo APENAS se saldo > mínimo
        // Se cliente tem menos que o mínimo, pode usar TODO o saldo
        if ($settings && $settings->min_cashback_to_use > 0) {
            $customerBalance = $customer->cashback_balance;

            // Se saldo é MAIOR que mínimo, validar valor usado
            if ($customerBalance >= $settings->min_cashback_to_use) {
                if ($amount < $settings->min_cashback_to_use) {
                    \Log::warning('⚠️ Valor abaixo do mínimo para usar cashback', [
                        'customer_id' => $customer->id,
                        'minimo_configurado' => $settings->min_cashback_to_use,
                        'tentou_usar' => $amount,
                        'saldo_disponivel' => $customerBalance,
                    ]);
                    return false;
                }
            }
            // Se saldo < mínimo, permite usar todo o saldo (não valida mínimo)
        }

        $balanceBefore = $customer->cashback_balance;
        $customer->cashback_balance -= $amount;
        $customer->save();

        \Log::info('✅ Cashback usado com sucesso', [
            'customer_id' => $customer->id,
            'valor_usado' => $amount,
            'saldo_antes' => $balanceBefore,
            'saldo_depois' => $customer->cashback_balance,
        ]);

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
     * DESABILITADO: Sistema de tiers removido (todos ganham mesmo percentual)
     */
    public function updateCustomerTier(Customer $customer): void
    {
        // Sistema de tiers desabilitado - não faz nada
        return;
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
     * Pega porcentagem do cashback (única para todos os clientes)
     */
    public function getPercentage(CashbackSettings $settings): float
    {
        return (float) $settings->bronze_percentage;
    }

    /**
     * Verifica se é aniversário do cliente
     */
    public function isBirthdayBonus(Customer $customer, CashbackSettings $settings): bool
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
