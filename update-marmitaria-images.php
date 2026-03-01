<?php

/**
 * Script para atualizar imagens da Marmitaria da Gi
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use App\Models\Product;

echo "🖼️  Atualizando imagens da Marmitaria da Gi...\n\n";

$tenant = Tenant::find('marmitaria-gi');

if (!$tenant) {
    echo "❌ Tenant marmitaria-gi não encontrado!\n";
    exit(1);
}

tenancy()->initialize($tenant);

// Imagens de alta qualidade
$images = [
    'Feijoada Completa' => 'https://images.unsplash.com/photo-1623428187969-5da2dcea5ebf?w=800&q=80&fit=crop&auto=format',
    'Contra Filé Grelhado' => 'https://images.unsplash.com/photo-1558030006-450675393462?w=800&q=80&fit=crop&auto=format',
    'Frango à Parmegiana' => 'https://images.unsplash.com/photo-1632778149955-e80f8ceca2e7?w=800&q=80&fit=crop&auto=format',
    'Isca de Frango Empanado' => 'https://images.unsplash.com/photo-1562967914-608f82629710?w=800&q=80&fit=crop&auto=format',
    'Linguiça Toscana' => 'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?w=800&q=80&fit=crop&auto=format',
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
echo "📊 {$updated} marmitas atualizadas\n";
echo "\n";
echo "🌐 Acesse: https://marmitaria-gi.yumgo.com.br\n";
