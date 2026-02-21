<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'BEMVINDO10',
                'description' => 'Desconto de 10% para novos clientes',
                'type' => 'percentage',
                'value' => 10.00,
                'min_order_value' => 30.00,
                'usage_limit' => 100,
                'usage_per_customer' => 1,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(3),
                'is_active' => true,
            ],
            [
                'code' => 'PIZZA20',
                'description' => 'R$ 20 OFF em pedidos acima de R$ 80',
                'type' => 'fixed',
                'value' => 20.00,
                'min_order_value' => 80.00,
                'usage_limit' => 50,
                'usage_per_customer' => 3,
                'starts_at' => now(),
                'expires_at' => now()->addMonth(),
                'is_active' => true,
            ],
            [
                'code' => 'FRETEGRATIS',
                'description' => 'Frete grátis em pedidos acima de R$ 50',
                'type' => 'fixed',
                'value' => 8.00, // Valor médio do frete
                'min_order_value' => 50.00,
                'usage_limit' => null, // Ilimitado
                'usage_per_customer' => 10,
                'starts_at' => now(),
                'expires_at' => null, // Sem expiração
                'is_active' => true,
            ],
            [
                'code' => 'PRIMEIRACOMPRA',
                'description' => '15% OFF na primeira compra',
                'type' => 'percentage',
                'value' => 15.00,
                'min_order_value' => 40.00,
                'usage_limit' => null,
                'usage_per_customer' => 1,
                'starts_at' => now(),
                'expires_at' => null,
                'is_active' => true,
            ],
            [
                'code' => 'FIMDESEMANA',
                'description' => '10% OFF aos finais de semana',
                'type' => 'percentage',
                'value' => 10.00,
                'min_order_value' => 35.00,
                'usage_limit' => null,
                'usage_per_customer' => null, // Ilimitado por cliente
                'starts_at' => now(),
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::updateOrCreate(
                ['code' => $coupon['code']],
                $coupon
            );
        }

        $this->command->info('✅ Cupons criados!');
        $this->command->info('   🎟️  BEMVINDO10 - 10% OFF');
        $this->command->info('   🎟️  PIZZA20 - R$ 20 OFF');
        $this->command->info('   🎟️  FRETEGRATIS - Frete grátis');
        $this->command->info('   🎟️  PRIMEIRACOMPRA - 15% OFF');
        $this->command->info('   🎟️  FIMDESEMANA - 10% OFF');
    }
}
