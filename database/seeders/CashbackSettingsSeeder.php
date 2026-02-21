<?php

namespace Database\Seeders;

use App\Models\CashbackSettings;
use Illuminate\Database\Seeder;

class CashbackSettingsSeeder extends Seeder
{
    public function run(): void
    {
        CashbackSettings::updateOrCreate(
            ['id' => 1],
            [
                // Tier Bronze (Iniciante)
                'bronze_percentage' => 2.00,
                'bronze_min_orders' => 0,
                'bronze_min_spent' => 0.00,
                
                // Tier Silver (Prata)
                'silver_percentage' => 3.50,
                'silver_min_orders' => 5,
                'silver_min_spent' => 200.00,
                
                // Tier Gold (Ouro)
                'gold_percentage' => 5.00,
                'gold_min_orders' => 15,
                'gold_min_spent' => 500.00,
                
                // Tier Platinum (Platina)
                'platinum_percentage' => 7.00,
                'platinum_min_orders' => 30,
                'platinum_min_spent' => 1000.00,
                
                // Bônus de aniversário
                'birthday_bonus_enabled' => true,
                'birthday_multiplier' => 2.00, // Dobra o cashback
                
                // Programa de indicação
                'referral_enabled' => true,
                'referral_bonus_referrer' => 10.00, // R$ 10 para quem indicou
                'referral_bonus_referred' => 5.00,  // R$ 5 para quem foi indicado
                
                // Configurações gerais
                'expiration_days' => 180, // 6 meses
                'min_order_value_to_earn' => 10.00,
                'min_cashback_to_use' => 5.00,
                
                'is_active' => true,
            ]
        );

        $this->command->info('✅ Configurações de cashback criadas!');
        $this->command->info('   🥉 Bronze: 2% (sem requisitos)');
        $this->command->info('   🥈 Prata: 3.5% (5 pedidos, R$ 200)');
        $this->command->info('   🥇 Ouro: 5% (15 pedidos, R$ 500)');
        $this->command->info('   💎 Platina: 7% (30 pedidos, R$ 1000)');
    }
}
