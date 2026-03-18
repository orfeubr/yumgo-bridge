<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

echo "🔧 Corrigindo Sprite Lata com nova URL...\n\n";

// URLs alternativas de latas de Sprite/refrigerante limão
$spriteUrls = [
    'https://images.unsplash.com/photo-1624517452488-04869289c4ca?w=800&q=80', // Lemon soda
    'https://images.unsplash.com/photo-1581006852262-e4307cf6283a?w=800&q=80', // Green soda can
    'https://images.unsplash.com/photo-1610889556528-9a770e32642f?w=800&q=80', // Citrus drink
    'https://images.unsplash.com/photo-1625772452859-1c03d5bf1137?w=800&q=80', // Sprite can
];

function downloadImage($urls) {
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
                echo "✅ ({$httpCode}, " . number_format(strlen($imageData)) . " bytes)\n";
                return $imageData;
            } else {
                echo "❌ (HTTP {$httpCode})\n";
            }

        } catch (\Exception $e) {
            echo "❌ (Erro: {$e->getMessage()})\n";
        }

        usleep(500000);
    }

    return null;
}

$tenants = Tenant::all();
$updated = 0;

foreach ($tenants as $tenant) {
    tenancy()->initialize($tenant);

    $sprite = Product::where('name', 'Sprite Lata')->first();

    if ($sprite) {
        echo "📍 {$tenant->name} - 🍋 Sprite Lata:\n";

        $imageData = downloadImage($spriteUrls);

        if ($imageData) {
            $path = "products/{$tenant->slug}_sprite_final.jpg";
            Storage::disk('public')->put($path, $imageData);

            $sprite->image = $path;
            $sprite->save();

            echo "   ✅ Imagem atualizada: {$path}\n\n";
            $updated++;
        } else {
            echo "   ❌ Todas as tentativas falharam\n\n";
        }

        usleep(800000);
    }

    tenancy()->end();
}

echo "\n🎉 Concluído! {$updated} imagens atualizadas.\n";
