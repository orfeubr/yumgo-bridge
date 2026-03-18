<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

echo "🔧 Corrigindo Sprite Lata e Coca-Cola 2L...\n\n";

// URLs corretas
$productImages = [
    'Sprite Lata' => [
        'https://images.unsplash.com/photo-1625772452859-1c03d5bf1137?w=800&q=80', // Lemon-lime soda can
        'https://images.unsplash.com/photo-1624517452488-04869289c4ca?w=800&q=80', // Sprite can alternative
        'https://images.unsplash.com/photo-1581006852262-e4307cf6283a?w=800&q=80', // Green soda can
    ],
    'Coca-Cola 2L' => [
        'https://images.unsplash.com/photo-1629203849140-8c07f8e62c91?w=800&q=80', // Coca-Cola bottle 2L
        'https://images.unsplash.com/photo-1554866585-cd94860890b7?w=800&q=80', // Soda bottle large
        'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=800&q=80', // Cola bottle
    ],
];

function downloadImageWithFallback($urls, $filename) {
    foreach ($urls as $index => $url) {
        try {
            echo "   Tentando URL " . ($index + 1) . "... ";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $imageData && strlen($imageData) >= 1000) {
                $path = "products/{$filename}";
                Storage::disk('public')->put($path, $imageData);
                echo "✅\n";
                return $path;
            } else {
                echo "❌ (HTTP {$httpCode})\n";
            }

        } catch (\Exception $e) {
            echo "❌ (Erro: {$e->getMessage()})\n";
        }

        if ($index < count($urls) - 1) {
            usleep(500000);
        }
    }

    return null;
}

$tenants = Tenant::all();
$totalUpdated = 0;
$totalFailed = 0;

foreach ($tenants as $tenant) {
    tenancy()->initialize($tenant);

    $products = Product::all();
    $updated = 0;
    $failed = 0;

    foreach ($products as $product) {
        if (!isset($productImages[$product->name])) {
            continue;
        }

        echo "📍 {$tenant->name} - 🍽️ {$product->name}:\n";

        $urls = $productImages[$product->name];
        $newImage = downloadImageWithFallback($urls, "{$tenant->slug}_{$product->id}_correct.jpg");

        if ($newImage) {
            $product->image = $newImage;
            $product->save();
            echo "   ✅ Imagem corrigida!\n\n";
            $updated++;
        } else {
            echo "   ❌ Todas as tentativas falharam\n\n";
            $failed++;
        }

        usleep(800000);
    }

    if ($updated > 0 || $failed > 0) {
        echo "  📊 {$tenant->name}: {$updated} ✅ | {$failed} ❌\n\n";
    }

    $totalUpdated += $updated;
    $totalFailed += $failed;

    tenancy()->end();
}

echo "\n🎉 Concluído!\n";
echo "✅ Total corrigido: {$totalUpdated}\n";
if ($totalFailed > 0) {
    echo "❌ Total ainda com falha: {$totalFailed}\n";
}
