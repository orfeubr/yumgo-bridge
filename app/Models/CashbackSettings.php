<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashbackSettings extends Model
{
    protected $fillable = [
        'bronze_percentage',
        'bronze_min_orders',
        'bronze_min_spent',
        'silver_percentage',
        'silver_min_orders',
        'silver_min_spent',
        'gold_percentage',
        'gold_min_orders',
        'gold_min_spent',
        'platinum_percentage',
        'platinum_min_orders',
        'platinum_min_spent',
        'birthday_bonus_enabled',
        'birthday_multiplier',
        'referral_enabled',
        'referral_bonus_referrer',
        'referral_bonus_referred',
        'expiration_days',
        'min_order_value_to_earn',
        'min_cashback_to_use',
        'is_active',
    ];

    protected $casts = [
        'bronze_percentage' => 'decimal:2',
        'bronze_min_spent' => 'decimal:2',
        'silver_percentage' => 'decimal:2',
        'silver_min_spent' => 'decimal:2',
        'gold_percentage' => 'decimal:2',
        'gold_min_spent' => 'decimal:2',
        'platinum_percentage' => 'decimal:2',
        'platinum_min_spent' => 'decimal:2',
        'birthday_multiplier' => 'decimal:2',
        'referral_bonus_referrer' => 'decimal:2',
        'referral_bonus_referred' => 'decimal:2',
        'min_order_value_to_earn' => 'decimal:2',
        'min_cashback_to_use' => 'decimal:2',
        'birthday_bonus_enabled' => 'boolean',
        'referral_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Pega percentual para um tier
     */
    public function getPercentageForTier(string $tier): float
    {
        return match($tier) {
            'bronze' => $this->bronze_percentage,
            'silver' => $this->silver_percentage,
            'gold' => $this->gold_percentage,
            'platinum' => $this->platinum_percentage,
            default => $this->bronze_percentage,
        };
    }

    /**
     * Verifica requisitos para um tier
     */
    public function meetsRequirements(string $tier, int $orders, float $spent): bool
    {
        return match($tier) {
            'bronze' => true,
            'silver' => $orders >= $this->silver_min_orders && $spent >= $this->silver_min_spent,
            'gold' => $orders >= $this->gold_min_orders && $spent >= $this->gold_min_spent,
            'platinum' => $orders >= $this->platinum_min_orders && $spent >= $this->platinum_min_spent,
            default => false,
        };
    }
}
