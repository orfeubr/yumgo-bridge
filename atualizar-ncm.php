<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenant = \App\Models\Tenant::where('slug', 'marmitariadagi')->first();
tenancy()->initialize($tenant);

echo "🔧 Atualizando produtos com NCM...\n\n";

$products = \App\Models\Product::all();
$updated = 0;

foreach ($products as $product) {
    $name = mb_strtolower($product->name);

    // Determinar NCM/CEST
    if (str_contains($name, 'água') || str_contains($name, 'agua')) {
        $ncm = '22021000';
        $cest = '0300100';
        $tipo = 'Águas';
    } elseif (str_contains($name, 'coca') || str_contains($name, 'guaraná') ||
              str_contains($name, 'guarana') || str_contains($name, 'suco')) {
        $ncm = '22029900';
        $cest = '0300700';
        $tipo = 'Bebidas';
    } else {
        $ncm = '19059090';
        $cest = null;
        $tipo = 'Alimentos';
    }

    $product->ncm = $ncm;
    $product->cfop = '5405';
    $product->cest = $cest;
    $product->save();

    echo sprintf("✅ %-35s → %s (NCM: %s)\n", substr($product->name, 0, 33), $tipo, $ncm);
    $updated++;
}

echo "\n📊 Total de produtos atualizados: $updated\n";

// Verificar
$withNCM = \App\Models\Product::whereNotNull('ncm')->whereNotNull('cfop')->count();
echo "📋 Produtos com NCM/CFOP: $withNCM\n\n";
