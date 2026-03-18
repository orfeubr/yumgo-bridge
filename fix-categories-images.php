<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use App\Models\Category;
use App\Models\Product;

echo "🔧 Corrigindo categorias e imagens...\n\n";

$tenants = Tenant::all();

foreach ($tenants as $tenant) {
    echo "📍 {$tenant->name}:\n";
    tenancy()->initialize($tenant);

    // 1. Reordenar categorias (Bebidas/Sobremesas por último)
    $categories = Category::all();
    $orderNum = 1;

    foreach ($categories as $cat) {
        $name = strtolower($cat->name);

        if (str_contains($name, 'bebida') || str_contains($name, 'sobremesa')) {
            $cat->order = 999; // Por último
        } else {
            $cat->order = $orderNum++;
        }
        $cat->save();
    }

    echo "  ✅ Categorias reordenadas\n";

    // 2. Corrigir URLs das imagens
    $products = Product::all();
    $updated = 0;

    foreach ($products as $product) {
        if (str_starts_with($product->image, '/storage/')) {
            $product->image = str_replace('/storage/', '/tenancy/assets/', $product->image);
            $product->save();
            $updated++;
        }
    }

    echo "  ✅ {$updated} URLs de imagens corrigidas\n";

    // Mostrar ordem final
    $cats = Category::orderBy('order')->get(['name', 'order']);
    echo "  📋 Ordem das categorias:\n";
    foreach ($cats as $c) {
        echo "     {$c->order}. {$c->name}\n";
    }
    echo "\n";

    tenancy()->end();
}

echo "🎉 Concluído!\n";
