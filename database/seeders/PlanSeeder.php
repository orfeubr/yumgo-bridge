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
                'name' => 'Trial',
                'description' => 'Teste grátis por 15 dias - Conheça todas as funcionalidades',
                'price_monthly' => 0.00,
                'commission_percentage' => 3.00,
                'features' => [
                    '✅ Produtos ilimitados',
                    '✅ Até 100 pedidos/mês',
                    '✅ Cashback configurável',
                    '✅ Cardápio digital',
                    '✅ Painel de pedidos em tempo real',
                    '✅ 15 dias grátis',
                    '⚠️ Suporte por email (48h)',
                ],
                'max_products' => null, // Ilimitado
                'max_orders_per_month' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'Starter',
                'description' => 'Ideal para começar - Comissão 3% (10x menor que iFood!)',
                'price_monthly' => 79.00,
                'commission_percentage' => 3.00,
                'features' => [
                    '✅ Produtos ilimitados',
                    '✅ Até 500 pedidos/mês',
                    '✅ Cashback configurável',
                    '✅ Cardápio digital',
                    '✅ Painel de pedidos em tempo real',
                    '✅ App mobile para clientes',
                    '✅ Até 3 usuários',
                    '✅ Emissão de NFC-e',
                    '✅ Integração Pagar.me/Asaas',
                    '⚠️ Suporte por email (24h)',
                ],
                'max_products' => null, // Ilimitado
                'max_orders_per_month' => 500,
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'description' => 'Para crescer rápido - Comissão 2% + Relatórios avançados',
                'price_monthly' => 149.00,
                'commission_percentage' => 2.00,
                'features' => [
                    '✅ Produtos ilimitados',
                    '✅ Até 2.000 pedidos/mês',
                    '✅ Tudo do Starter +',
                    '📊 Relatórios avançados (vendas, produtos, clientes)',
                    '📊 Dashboard com gráficos em tempo real',
                    '📊 Exportação de dados (Excel, PDF)',
                    '👥 Até 10 usuários com permissões',
                    '🎯 Sistema de cupons avançado',
                    '🔔 Notificações push',
                    '💬 Suporte por chat (12h)',
                    '🎨 Personalização de tema/cores',
                ],
                'max_products' => null, // Ilimitado
                'max_orders_per_month' => 2000,
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'description' => 'Operações grandes - Comissão 1% + API + Gerente dedicado',
                'price_monthly' => 299.00,
                'commission_percentage' => 1.00,
                'features' => [
                    '✅ Produtos ilimitados',
                    '✅ Pedidos ilimitados',
                    '✅ Tudo do Pro +',
                    '🚀 API REST completa',
                    '🚀 Webhooks personalizados',
                    '🚀 Multi-loja (várias filiais)',
                    '🚀 Integração ERP/SAP',
                    '👥 Usuários ilimitados',
                    '👨‍💼 Gerente de conta dedicado',
                    '⚡ Suporte prioritário 24/7',
                    '🎨 White-label (marca própria)',
                    '📱 App mobile personalizado',
                    '🔒 SLA 99.9% uptime',
                ],
                'max_products' => null, // Ilimitado
                'max_orders_per_month' => null, // Ilimitado
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
