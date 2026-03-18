<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

echo "🔧 Corrigindo 4 imagens que falharam...\n\n";

// URLs alternativas para os produtos que falharam
$productImages = [
    // URLs alternativas com fallbacks
    'Linguiça Artesanal' => [
        'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?w=800&q=80', // Grilled sausages
        'https://images.unsplash.com/photo-1590004987778-bece5c9adab6?w=800&q=80', // Sausages platter
        'https://images.unsplash.com/photo-1599487488170-d11ec9c172f0?w=800&q=80', // Grilled meat
    ],
    'Coca-Cola Lata' => [
        'https://images.unsplash.com/photo-1629203851122-3726ecdf6c8e?w=800&q=80', // Cold can drink
        'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=800&q=80', // Soda can
        'https://images.unsplash.com/photo-1581636625402-29b2a704ef13?w=800&q=80', // Beverage can
    ],
    'Calabresa Acebolada' => [
        'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?w=800&q=80', // Sausage with onions
        'https://images.unsplash.com/photo-1590004987778-bece5c9adab6?w=800&q=80', // Grilled sausage
        'https://images.unsplash.com/photo-1529692236671-f1f6cf9683ba?w=800&q=80', // Grilled meat
    ],
    'Iscas de Peixe' => [
        'https://images.unsplash.com/photo-1580959375944-c4a568a5d241?w=800&q=80', // Fish and chips
        'https://images.unsplash.com/photo-1599487488170-d11ec9c172f0?w=800&q=80', // Fried fish
        'https://images.unsplash.com/photo-1534604973900-c43ab4c2e0ab?w=800&q=80', // Seafood
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
                // Salvar no storage
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

        // Delay entre tentativas
        if ($index < count($urls) - 1) {
            usleep(500000); // 0.5 segundo
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
        // Só processar os produtos que falharam
        if (!isset($productImages[$product->name])) {
            continue;
        }

        echo "📍 {$tenant->name} - 🍽️ {$product->name}:\n";

        $urls = $productImages[$product->name];
        $newImage = downloadImageWithFallback($urls, "{$tenant->slug}_{$product->id}_fixed.jpg");

        if ($newImage) {
            $product->image = $newImage;
            $product->save();
            echo "   ✅ Imagem atualizada!\n\n";
            $updated++;
        } else {
            echo "   ❌ Todas as tentativas falharam\n\n";
            $failed++;
        }

        usleep(800000); // 0.8 segundo entre produtos
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
