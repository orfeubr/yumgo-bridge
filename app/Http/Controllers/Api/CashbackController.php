<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashbackSettings;
use Illuminate\Http\Request;

class CashbackController extends Controller
{
    /**
     * Obter saldo de cashback do cliente
     * SIMPLIFICADO: Sem sistema de tiers
     */
    public function balance(Request $request)
    {
        // 🔄 BUSCAR CUSTOMER DO TENANT (cashback está no schema do tenant, não no central)
        $centralCustomer = $request->user();
        $customer = \App\Models\Customer::where('email', $centralCustomer->email)
            ->orWhere('phone', $centralCustomer->phone)
            ->first();

        if (!$customer) {
            return response()->json([
                'balance' => 0,
                'cashback_percentage' => 0,
                'is_active' => false,
                'min_cashback_to_use' => 0,
                'total_earned' => 0,
                'total_used' => 0,
                'message' => 'Cliente não encontrado neste restaurante',
            ]);
        }

        $settings = CashbackSettings::first();

        // Percentual único para todos os clientes
        $percentage = 0;
        if ($settings && $settings->is_active) {
            $percentage = (float) $settings->bronze_percentage;
        }

        return response()->json([
            'balance' => (float) $customer->cashback_balance,
            'cashback_percentage' => $percentage,
            'is_active' => $settings?->is_active ?? false,
            'min_cashback_to_use' => (float) ($settings?->min_cashback_to_use ?? 5.00),
            'total_earned' => (float) $customer->cashbackTransactions()
                ->where('type', 'earned')
                ->sum('amount'),
            'total_used' => (float) $customer->cashbackTransactions()
                ->where('type', 'used')
                ->sum('amount'),
        ]);
    }

    /**
     * Calcular cashback que será ganho em um pedido
     * SIMPLIFICADO: Percentual único para todos
     */
    public function calculate(Request $request)
    {
        $request->validate([
            'total' => 'required|numeric|min:0',
        ]);

        // 🔄 BUSCAR CUSTOMER DO TENANT
        $centralCustomer = $request->user();
        $customer = \App\Models\Customer::where('email', $centralCustomer->email)
            ->orWhere('phone', $centralCustomer->phone)
            ->first();

        $settings = CashbackSettings::first();

        if (!$settings || !$settings->is_active) {
            return response()->json([
                'will_earn' => 0,
                'percentage' => 0,
                'message' => 'Cashback não está ativo',
            ]);
        }

        $orderTotal = $request->input('total');

        // Verificar valor mínimo do pedido
        if ($orderTotal < $settings->min_order_value_to_earn) {
            return response()->json([
                'will_earn' => 0,
                'percentage' => 0,
                'message' => "Valor mínimo para ganhar cashback: R$ " . number_format($settings->min_order_value_to_earn, 2, ',', '.'),
            ]);
        }

        // Percentual único para todos os clientes
        $percentage = (float) $settings->bronze_percentage;

        // Calcular cashback
        $willEarn = ($orderTotal * $percentage) / 100;

        // Verificar se é aniversário do cliente
        $isBirthday = false;
        if ($settings->birthday_bonus_enabled && $customer->birth_date) {
            $today = now()->format('m-d');
            $birthDay = $customer->birth_date->format('m-d');
            $isBirthday = ($today === $birthDay);

            if ($isBirthday) {
                $willEarn *= $settings->birthday_multiplier;
            }
        }

        return response()->json([
            'will_earn' => round($willEarn, 2),
            'percentage' => $percentage,
            'is_birthday_bonus' => $isBirthday ?? false,
            'message' => $isBirthday ? '🎂 Bônus de aniversário ativado!' : null,
        ]);
    }

    /**
     * Histórico de transações de cashback
     */
    public function transactions(Request $request)
    {
        // 🔄 BUSCAR CUSTOMER DO TENANT
        $centralCustomer = $request->user();
        $customer = \App\Models\Customer::where('email', $centralCustomer->email)
            ->orWhere('phone', $centralCustomer->phone)
            ->first();

        if (!$customer) {
            return response()->json([
                'data' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 20,
                    'total' => 0,
                ],
            ]);
        }

        $transactions = $customer->cashbackTransactions()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'data' => $transactions->map(fn($tx) => [
                'id' => $tx->id,
                'type' => $tx->type,
                'type_label' => $tx->type === 'credit' ? 'Ganho' : 'Usado',
                'amount' => $tx->amount,
                'description' => $tx->description,
                'order_id' => $tx->order_id,
                'expires_at' => $tx->expires_at?->format('d/m/Y'),
                'created_at' => $tx->created_at->format('d/m/Y H:i'),
            ]),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * Obter configurações de cashback do restaurante
     * SIMPLIFICADO: Sem sistema de tiers
     */
    public function settings()
    {
        $settings = CashbackSettings::first();

        if (!$settings) {
            return response()->json([
                'message' => 'Configurações de cashback não encontradas.',
            ], 404);
        }

        return response()->json([
            'is_active' => $settings->is_active,
            'percentage' => (float) $settings->bronze_percentage,
            'min_order_value_to_earn' => (float) $settings->min_order_value_to_earn,
            'min_cashback_to_use' => (float) $settings->min_cashback_to_use,
            'expiration_days' => $settings->expiration_days,
            'birthday_bonus_enabled' => $settings->birthday_bonus_enabled,
            'birthday_multiplier' => (float) $settings->birthday_multiplier,
            'referral_bonus_enabled' => $settings->referral_bonus_enabled,
            'referral_bonus_amount' => (float) $settings->referral_bonus_amount,
        ]);
    }

}
