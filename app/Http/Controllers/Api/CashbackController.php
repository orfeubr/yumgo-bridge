<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashbackSettings;
use Illuminate\Http\Request;

class CashbackController extends Controller
{
    /**
     * Obter saldo de cashback do cliente
     */
    public function balance(Request $request)
    {
        $customer = $request->user();
        $settings = CashbackSettings::first();

        // Buscar percentual de cashback do tier do cliente
        $percentage = 0;
        if ($settings && $settings->is_active) {
            $tierField = $customer->loyalty_tier . '_percentage';
            $percentage = $settings->$tierField ?? 0;
        }

        return response()->json([
            'balance' => $customer->cashback_balance,
            'loyalty_tier' => $customer->loyalty_tier,
            'tier_label' => $this->getTierLabel($customer->loyalty_tier),
            'cashback_percentage' => $percentage,
            'is_active' => $settings?->is_active ?? false,
            'min_cashback_to_use' => $settings?->min_cashback_to_use ?? 5.00,
            'next_tier' => $this->getNextTier($customer->loyalty_tier),
            'total_earned' => $customer->cashbackTransactions()
                ->where('type', 'credit')
                ->sum('amount'),
            'total_used' => $customer->cashbackTransactions()
                ->where('type', 'debit')
                ->sum('amount'),
        ]);
    }

    /**
     * Calcular cashback que será ganho em um pedido
     */
    public function calculate(Request $request)
    {
        $request->validate([
            'total' => 'required|numeric|min:0',
        ]);

        $customer = $request->user();
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

        // Buscar percentual do tier
        $tierField = $customer->loyalty_tier . '_percentage';
        $percentage = $settings->$tierField ?? 0;

        // Calcular cashback
        $willEarn = ($orderTotal * $percentage) / 100;

        // Verificar se é aniversário do cliente
        $isB birthday = false;
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
        $transactions = $request->user()
            ->cashbackTransactions()
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
            'enabled' => true,
            'tiers' => [
                [
                    'name' => 'bronze',
                    'label' => 'Bronze',
                    'percentage' => $settings->bronze_percentage,
                    'min_orders' => $settings->bronze_min_orders,
                    'min_spent' => $settings->bronze_min_spent,
                ],
                [
                    'name' => 'silver',
                    'label' => 'Prata',
                    'percentage' => $settings->silver_percentage,
                    'min_orders' => $settings->silver_min_orders,
                    'min_spent' => $settings->silver_min_spent,
                ],
                [
                    'name' => 'gold',
                    'label' => 'Ouro',
                    'percentage' => $settings->gold_percentage,
                    'min_orders' => $settings->gold_min_orders,
                    'min_spent' => $settings->gold_min_spent,
                ],
                [
                    'name' => 'platinum',
                    'label' => 'Platina',
                    'percentage' => $settings->platinum_percentage,
                    'min_orders' => $settings->platinum_min_orders,
                    'min_spent' => $settings->platinum_min_spent,
                ],
            ],
            'birthday_bonus_enabled' => $settings->birthday_bonus_enabled,
            'birthday_multiplier' => $settings->birthday_multiplier,
            'referral_bonus_enabled' => $settings->referral_bonus_enabled,
            'referral_bonus_amount' => $settings->referral_bonus_amount,
        ]);
    }

    /**
     * Obter label do tier
     */
    private function getTierLabel(string $tier): string
    {
        return match ($tier) {
            'bronze' => 'Bronze',
            'silver' => 'Prata',
            'gold' => 'Ouro',
            'platinum' => 'Platina',
            default => $tier,
        };
    }

    /**
     * Obter próximo tier
     */
    private function getNextTier(string $currentTier): ?string
    {
        return match ($currentTier) {
            'bronze' => 'silver',
            'silver' => 'gold',
            'gold' => 'platinum',
            'platinum' => null,
            default => null,
        };
    }
}
