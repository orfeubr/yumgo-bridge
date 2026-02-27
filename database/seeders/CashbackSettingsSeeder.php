<?php

namespace Database\Seeders;

use App\Models\CashbackSettings;
use Illuminate\Database\Seeder;

class CashbackSettingsSeeder extends Seeder
{
    public function run(): void
    {
        CashbackSettings::firstOrCreate([], [
            // Bronze
            'bronze_percentage' => 2.00,
            'bronze_min_orders' => 0,
            'bronze_min_spent' => 0.00,

            // Prata
            'silver_percentage' => 3.50,
            'silver_min_orders' => 5,
            'silver_min_spent' => 200.00,

            // Ouro
            'gold_percentage' => 5.00,
            'gold_min_orders' => 15,
            'gold_min_spent' => 500.00,

            // Platina
            'platinum_percentage' => 7.00,
            'platinum_min_orders' => 30,
            'platinum_min_spent' => 1000.00,

            // Bônus
            'birthday_bonus_enabled' => true,
            'birthday_multiplier' => 2.00,
            'referral_enabled' => true,
            'referral_bonus_referrer' => 10.00,
            'referral_bonus_referred' => 5.00,

            // Regras
            'expiration_days' => 180,
            'min_order_value_to_earn' => 10.00,
            'min_cashback_to_use' => 5.00,

            // Status
            'is_active' => true,
        ]);

        $this->command->info('✅ Configurações de cashback inicializadas!');
    }
}
