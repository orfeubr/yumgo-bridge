<?php

namespace Database\Seeders;

use App\Models\RestaurantType;
use Illuminate\Database\Seeder;

class RestaurantTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Pizzaria', 'slug' => 'pizzaria', 'icon' => '🍕', 'description' => 'Restaurantes especializados em pizzas', 'sort_order' => 1],
            ['name' => 'Hamburgueria', 'slug' => 'hamburgueria', 'icon' => '🍔', 'description' => 'Hamburguerias artesanais e fast food', 'sort_order' => 2],
            ['name' => 'Marmitaria', 'slug' => 'marmitaria', 'icon' => '🍱', 'description' => 'Marmitas e quentinhas', 'sort_order' => 3],
            ['name' => 'Japonês', 'slug' => 'japones', 'icon' => '🍣', 'description' => 'Sushis, sashimis e comida japonesa', 'sort_order' => 4],
            ['name' => 'Açaí', 'slug' => 'acai', 'icon' => '🍨', 'description' => 'Açaí, sorvetes e sobremesas geladas', 'sort_order' => 5],
            ['name' => 'Brasileira', 'slug' => 'brasileira', 'icon' => '🍛', 'description' => 'Comida brasileira caseira', 'sort_order' => 6],
            ['name' => 'Lanches', 'slug' => 'lanches', 'icon' => '🌭', 'description' => 'Hot dogs, salgados e lanches rápidos', 'sort_order' => 7],
            ['name' => 'Sobremesas', 'slug' => 'sobremesas', 'icon' => '🍰', 'description' => 'Docerias, confeitarias e sobremesas', 'sort_order' => 8],
            ['name' => 'Saudável', 'slug' => 'saudavel', 'icon' => '🥗', 'description' => 'Opções fit, veganas e saudáveis', 'sort_order' => 9],
            ['name' => 'Bebidas', 'slug' => 'bebidas', 'icon' => '🥤', 'description' => 'Sucos, refrigerantes e bebidas', 'sort_order' => 10],
            ['name' => 'Padaria', 'slug' => 'padaria', 'icon' => '🥖', 'description' => 'Pães, bolos e produtos de padaria', 'sort_order' => 11],
            ['name' => 'Carnes', 'slug' => 'carnes', 'icon' => '🥩', 'description' => 'Churrascarias e carnes nobres', 'sort_order' => 12],
            ['name' => 'Mexicana', 'slug' => 'mexicana', 'icon' => '🌮', 'description' => 'Tacos, burritos e comida mexicana', 'sort_order' => 13],
            ['name' => 'Italiana', 'slug' => 'italiana', 'icon' => '🍝', 'description' => 'Massas, risotos e comida italiana', 'sort_order' => 14],
            ['name' => 'Árabe', 'slug' => 'arabe', 'icon' => '🥙', 'description' => 'Esfihas, kebabs e comida árabe', 'sort_order' => 15],
        ];

        foreach ($types as $type) {
            RestaurantType::updateOrCreate(['slug' => $type['slug']], $type);
        }

        $this->command->info('✅ ' . count($types) . ' tipos de restaurantes criados!');
    }
}
