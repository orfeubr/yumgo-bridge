<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\CashbackSettings;
use Illuminate\Database\Seeder;

class MarmitariaGiSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar ou criar tenant
        $tenant = Tenant::where('id', 'marmitaria-gi')->first();

        if (!$tenant) {
            $tenant = Tenant::create([
                'id' => 'marmitaria-gi',
                'name' => 'Marmitaria da Gi',
                'slug' => 'marmitaria-gi',
                'email' => 'contato@marmitariadagi.com.br',
                'status' => 'active',
            ]);
        }

        tenancy()->initialize($tenant);

        // Criar configurações de cashback
        CashbackSettings::firstOrCreate([], [
            'is_active' => true,
            'bronze_percentage' => 3.0,
            'silver_percentage' => 5.0,
            'gold_percentage' => 8.0,
            'platinum_percentage' => 10.0,
            'min_order_value_to_earn' => 15.00,
            'min_cashback_to_use' => 5.00,
            'expiration_days' => 90,
            'birthday_bonus_enabled' => true,
            'birthday_multiplier' => 2.0,
            'bronze_min_orders' => 0,
            'bronze_min_spent' => 0,
            'silver_min_orders' => 5,
            'silver_min_spent' => 150.00,
            'gold_min_orders' => 15,
            'gold_min_spent' => 500.00,
            'platinum_min_orders' => 30,
            'platinum_min_spent' => 1000.00,
        ]);

        // Criar categoria
        $categoria = Category::firstOrCreate(
            ['name' => 'Marmitas do Dia'],
            [
                'slug' => 'marmitas-do-dia',
                'description' => 'Marmitas fresquinhas preparadas todos os dias',
                'is_active' => true,
            ]
        );

        // Cardápio com preços corretos e imagens de qualidade
        $pratos = [
            [
                'name' => 'Feijoada Completa',
                'description' => 'Feijoada tradicional completa. Acompanha: arroz, couve, farofa, vinagrete e torresmo.',
                'price_p' => 31.00,
                'price_m' => 34.00,
                'image' => 'https://images.unsplash.com/photo-1623428187969-5da2dcea5ebf?w=800&q=80&fit=crop&auto=format',
            ],
            [
                'name' => 'Contra Filé Grelhado',
                'description' => 'Contra filé macio grelhado. Acompanha: arroz, feijão, farofa, maionese de batata com ovos e batata frita.',
                'price_p' => 37.00,
                'price_m' => 40.00,
                'image' => 'https://images.unsplash.com/photo-1558030006-450675393462?w=800&q=80&fit=crop&auto=format',
            ],
            [
                'name' => 'Frango à Parmegiana',
                'description' => 'Filé de frango empanado com molho e queijo. Acompanha: arroz, feijão, farofa, maionese de batata com ovos e batata frita.',
                'price_p' => 25.00,
                'price_m' => 28.00,
                'image' => 'https://images.unsplash.com/photo-1632778149955-e80f8ceca2e7?w=800&q=80&fit=crop&auto=format',
            ],
            [
                'name' => 'Isca de Frango Empanado',
                'description' => 'Iscas de frango empanadas e douradas. Acompanha: arroz, feijão, farofa, maionese de batata com ovos e batata frita.',
                'price_p' => 25.00,
                'price_m' => 28.00,
                'image' => 'https://images.unsplash.com/photo-1562967914-608f82629710?w=800&q=80&fit=crop&auto=format',
            ],
            [
                'name' => 'Linguiça Toscana',
                'description' => 'Linguiça toscana artesanal grelhada. Acompanha: arroz, feijão, farofa, maionese de batata com ovos e batata frita.',
                'price_p' => 23.00,
                'price_m' => 26.00,
                'image' => 'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?w=800&q=80&fit=crop&auto=format',
            ],
        ];

        foreach ($pratos as $pratoData) {
            $slug = \Illuminate\Support\Str::slug($pratoData['name']);
            $produto = Product::firstOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $categoria->id,
                    'name' => $pratoData['name'],
                    'description' => $pratoData['description'],
                    'price' => $pratoData['price_p'], // Preço base = P
                    'image' => $pratoData['image'],
                    'is_active' => true,
                    'is_featured' => true,
                ]
            );

            // Criar variações de tamanho
            ProductVariation::firstOrCreate(
                ['product_id' => $produto->id, 'name' => 'P'],
                ['price_modifier' => 0.00]
            );

            ProductVariation::firstOrCreate(
                ['product_id' => $produto->id, 'name' => 'M'],
                ['price_modifier' => ($pratoData['price_m'] - $pratoData['price_p'])]
            );
        }

        tenancy()->end();

        $this->command->info("✅ Marmitaria da Gi configurada!");
        $this->command->info("📦 5 marmitas cadastradas");
        $this->command->info("💰 Preços corretos aplicados");
    }
}
