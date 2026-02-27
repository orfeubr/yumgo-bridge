<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use App\Models\Product;

echo "🖼️  Corrigindo imagens da Marmitaria da Gi...\n\n";

$tenant = Tenant::find('marmitaria-gi');
tenancy()->initialize($tenant);

// Imagens de alta qualidade e CORRETAS
$images = [
    'Feijoada Completa' => 'https://images.unsplash.com/photo-1623428187969-5da2dcea5ebf?w=800&q=80&fit=crop', // Feijoada OK
    'Contra Filé Grelhado' => 'https://images.unsplash.com/photo-1600891964092-4316c288032e?w=800&q=80&fit=crop', // Carne grelhada
    'Frango à Parmegiana' => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=800&q=80&fit=crop', // Parmegiana
    'Isca de Frango Empanado' => 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?w=800&q=80&fit=crop', // Frango empanado
    'Linguiça Toscana' => 'https://images.unsplash.com/photo-1529503418506-356c131c5782?w=800&q=80&fit=crop', // Linguiça GRELHADA
];

foreach ($images as $name => $url) {
    $product = Product::where('name', $name)->first();
    if ($product) {
        $product->update(['image' => $url]);
        echo "✅ {$name}\n";
    }
}

tenancy()->end();

echo "\n🎨 Imagens corrigidas!\n";
