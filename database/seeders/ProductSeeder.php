<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\ProductAddon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Pizzas
        $pizzaCategory = Category::where('slug', 'pizzas')->first();
        
        if ($pizzaCategory) {
            $pizzas = [
                [
                    'name' => 'Pizza Calabresa',
                    'description' => 'Calabresa, cebola, mussarela e orégano',
                    'price' => 45.00,
                    'pizza_config' => [
                        'allow_half_and_half' => true,
                        'border_options' => ['catupiry', 'cheddar', 'chocolate'],
                    ],
                ],
                [
                    'name' => 'Pizza Margherita',
                    'description' => 'Molho de tomate, mussarela, tomate e manjericão',
                    'price' => 42.00,
                    'pizza_config' => [
                        'allow_half_and_half' => true,
                        'border_options' => ['catupiry', 'cheddar', 'chocolate'],
                    ],
                ],
                [
                    'name' => 'Pizza Portuguesa',
                    'description' => 'Presunto, ovos, cebola, azeitona e ervilha',
                    'price' => 48.00,
                    'pizza_config' => [
                        'allow_half_and_half' => true,
                        'border_options' => ['catupiry', 'cheddar', 'chocolate'],
                    ],
                ],
                [
                    'name' => 'Pizza 4 Queijos',
                    'description' => 'Mussarela, provolone, gorgonzola e parmesão',
                    'price' => 52.00,
                    'pizza_config' => [
                        'allow_half_and_half' => true,
                        'border_options' => ['catupiry', 'cheddar', 'chocolate'],
                    ],
                ],
                [
                    'name' => 'Pizza Frango Catupiry',
                    'description' => 'Frango desfiado com catupiry',
                    'price' => 46.00,
                    'pizza_config' => [
                        'allow_half_and_half' => true,
                        'border_options' => ['catupiry', 'cheddar', 'chocolate'],
                    ],
                ],
            ];

            foreach ($pizzas as $pizzaData) {
                $pizza = Product::updateOrCreate(
                    [
                        'category_id' => $pizzaCategory->id,
                        'slug' => Str::slug($pizzaData['name']),
                    ],
                    [
                        'name' => $pizzaData['name'],
                        'description' => $pizzaData['description'],
                        'price' => $pizzaData['price'],
                        'pizza_config' => $pizzaData['pizza_config'],
                        'is_active' => true,
                        'is_featured' => true,
                        'preparation_time' => 30,
                    ]
                );

                // Adiciona variações de tamanho
                ProductVariation::updateOrCreate(
                    ['product_id' => $pizza->id, 'name' => 'Pequena'],
                    [
                        'price_modifier' => -10.00,
                        'modifier_type' => 'fixed',
                        'serves' => 1,
                        'is_active' => true,
                        'order' => 1,
                    ]
                );

                ProductVariation::updateOrCreate(
                    ['product_id' => $pizza->id, 'name' => 'Média'],
                    [
                        'price_modifier' => 0.00,
                        'modifier_type' => 'fixed',
                        'serves' => 2,
                        'is_active' => true,
                        'order' => 2,
                    ]
                );

                ProductVariation::updateOrCreate(
                    ['product_id' => $pizza->id, 'name' => 'Grande'],
                    [
                        'price_modifier' => 15.00,
                        'modifier_type' => 'fixed',
                        'serves' => 3,
                        'is_active' => true,
                        'order' => 3,
                    ]
                );

                ProductVariation::updateOrCreate(
                    ['product_id' => $pizza->id, 'name' => 'Família'],
                    [
                        'price_modifier' => 30.00,
                        'modifier_type' => 'fixed',
                        'serves' => 4,
                        'is_active' => true,
                        'order' => 4,
                    ]
                );

                // Adiciona adicionais
                ProductAddon::updateOrCreate(
                    ['product_id' => $pizza->id, 'name' => 'Borda de Catupiry'],
                    ['price' => 8.00, 'is_active' => true, 'order' => 1]
                );

                ProductAddon::updateOrCreate(
                    ['product_id' => $pizza->id, 'name' => 'Borda de Cheddar'],
                    ['price' => 8.00, 'is_active' => true, 'order' => 2]
                );

                ProductAddon::updateOrCreate(
                    ['product_id' => $pizza->id, 'name' => 'Bacon Extra'],
                    ['price' => 6.00, 'is_active' => true, 'order' => 3]
                );
            }
        }

        // Bebidas
        $bebidaCategory = Category::where('slug', 'bebidas')->first();
        
        if ($bebidaCategory) {
            $bebidas = [
                ['name' => 'Coca-Cola 2L', 'price' => 12.00],
                ['name' => 'Guaraná 2L', 'price' => 10.00],
                ['name' => 'Suco de Laranja 500ml', 'price' => 8.00],
                ['name' => 'Água Mineral 500ml', 'price' => 3.00],
                ['name' => 'Cerveja Heineken Long Neck', 'price' => 8.00],
            ];

            foreach ($bebidas as $bebida) {
                Product::updateOrCreate(
                    [
                        'category_id' => $bebidaCategory->id,
                        'slug' => Str::slug($bebida['name']),
                    ],
                    [
                        'name' => $bebida['name'],
                        'price' => $bebida['price'],
                        'is_active' => true,
                        'preparation_time' => 5,
                        'has_stock_control' => true,
                        'stock_quantity' => 50,
                        'min_stock_alert' => 10,
                    ]
                );
            }
        }

        // Sobremesas
        $sobremesaCategory = Category::where('slug', 'sobremesas')->first();
        
        if ($sobremesaCategory) {
            $sobremesas = [
                ['name' => 'Brownie com Sorvete', 'price' => 15.00],
                ['name' => 'Petit Gateau', 'price' => 18.00],
                ['name' => 'Pudim de Leite', 'price' => 12.00],
                ['name' => 'Sorvete (2 bolas)', 'price' => 10.00],
            ];

            foreach ($sobremesas as $sobremesa) {
                Product::updateOrCreate(
                    [
                        'category_id' => $sobremesaCategory->id,
                        'slug' => Str::slug($sobremesa['name']),
                    ],
                    [
                        'name' => $sobremesa['name'],
                        'price' => $sobremesa['price'],
                        'is_active' => true,
                        'preparation_time' => 10,
                    ]
                );
            }
        }

        $this->command->info('✅ Produtos criados!');
        $this->command->info('   🍕 5 Pizzas com variações e adicionais');
        $this->command->info('   🥤 5 Bebidas');
        $this->command->info('   🍰 4 Sobremesas');
    }
}
