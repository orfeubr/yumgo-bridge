<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Pizzas',
                'slug' => 'pizzas',
                'description' => 'Pizzas tradicionais e especiais',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Bebidas',
                'slug' => 'bebidas',
                'description' => 'Refrigerantes, sucos e águas',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Sobremesas',
                'slug' => 'sobremesas',
                'description' => 'Doces e sobremesas deliciosas',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Porções',
                'slug' => 'porcoes',
                'description' => 'Petiscos e porções',
                'order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Lanches',
                'slug' => 'lanches',
                'description' => 'Hambúrgueres e sanduíches',
                'order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('✅ Categorias criadas!');
    }
}
