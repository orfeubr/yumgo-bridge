<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'description' => 'Plano ideal para restaurantes iniciantes',
                'price_monthly' => 79.00,
                'commission_percentage' => 3.00,
                'features' => [
                    'Até 50 produtos',
                    'Até 500 pedidos/mês',
                    'Cashback configurável',
                    'App mobile',
                    'Suporte por email',
                    '1 usuário admin',
                ],
                'max_products' => 50,
                'max_orders_per_month' => 500,
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'description' => 'Plano profissional para restaurantes em crescimento',
                'price_monthly' => 149.00,
                'commission_percentage' => 2.00,
                'features' => [
                    'Até 200 produtos',
                    'Até 2000 pedidos/mês',
                    'Cashback configurável',
                    'App mobile',
                    'Suporte prioritário',
                    '3 usuários admin',
                    'Relatórios avançados',
                    'Integração com delivery próprio',
                ],
                'max_products' => 200,
                'max_orders_per_month' => 2000,
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'description' => 'Plano para grandes operações',
                'price_monthly' => 299.00,
                'commission_percentage' => 1.00,
                'features' => [
                    'Produtos ilimitados',
                    'Pedidos ilimitados',
                    'Cashback configurável',
                    'App mobile personalizado',
                    'Suporte 24/7',
                    'Usuários ilimitados',
                    'Relatórios avançados',
                    'Integração com delivery próprio',
                    'API dedicada',
                    'Multi-loja',
                ],
                'max_products' => null,
                'max_orders_per_month' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Trial',
                'description' => 'Período de teste gratuito',
                'price_monthly' => 0.00,
                'commission_percentage' => 3.00,
                'features' => [
                    'Até 20 produtos',
                    'Até 100 pedidos/mês',
                    'Todos os recursos básicos',
                    '15 dias grátis',
                ],
                'max_products' => 20,
                'max_orders_per_month' => 100,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }

        $this->command->info('✅ Planos criados com sucesso!');
    }
}
