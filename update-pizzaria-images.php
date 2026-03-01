<?php

/**
 * Script para adicionar imagens aos produtos da Pizzaria Bella
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use App\Models\Product;

echo "🖼️  Adicionando imagens aos produtos da Pizzaria Bella...\n\n";

// Inicializar tenant pizzaria-bella
$tenant = Tenant::find('pizzaria-bella');

if (!$tenant) {
    echo "❌ Tenant pizzaria-bella não encontrado!\n";
    exit(1);
}

tenancy()->initialize($tenant);

// Mapa de imagens de qualidade (Unsplash)
$images = [
    // Pizzas
    'Pizza de Mussarela' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=800&q=80&fit=crop&auto=format',
    'Pizza Portuguesa' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=800&q=80&fit=crop&auto=format',
    'Pizza Quatro Queijos' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800&q=80&fit=crop&auto=format',
    'Pizza de Calabresa' => 'https://images.unsplash.com/photo-1628840042765-356cda07504e?w=800&q=80&fit=crop&auto=format',

    // Bebidas
    'Coca-Cola 2L' => 'https://images.unsplash.com/photo-1554866585-cd94860890b7?w=800&q=80&fit=crop&auto=format',
    'Guaraná Antarctica 2L' => 'https://images.unsplash.com/photo-1624517452488-04869289c4ca?w=800&q=80&fit=crop&auto=format',
    'Suco Natural 500ml' => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=800&q=80&fit=crop&auto=format',

    // Sobremesas
    'Pudim de Leite' => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=800&q=80&fit=crop&auto=format',
    'Brownie com Sorvete' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=800&q=80&fit=crop&auto=format',
];

$updated = 0;

foreach ($images as $productName => $imageUrl) {
    $product = Product::where('name', $productName)->first();

    if ($product) {
        $product->update(['image' => $imageUrl]);
        echo "✅ {$productName}\n";
        $updated++;
    } else {
        echo "⚠️  {$productName} - não encontrado\n";
    }
}

tenancy()->end();

echo "\n";
echo "🎉 Concluído!\n";
echo "📊 {$updated} produtos atualizados com imagens\n";
echo "\n";
echo "🌐 Acesse: https://pizzaria-bella.yumgo.com.br\n";
