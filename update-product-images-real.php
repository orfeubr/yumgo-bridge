<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

echo "🖼️  Atualizando imagens com fotos reais de alta qualidade...\n\n";

// URLs de Unsplash com IDs específicos de fotos de comida brasileira/geral
// Unsplash permite uso gratuito com atribuição
$productImages = [
    // === BEBIDAS ===
    'Coca-Cola 2L' => 'https://images.unsplash.com/photo-1554866585-cd94860890b7?w=800&q=80', // Coca-Cola bottle
    'Coca-Cola Lata' => 'https://images.unsplash.com/photo-1629203851122-3726ecdf6. [...]',
    'Guaraná Antarctica 2L' => 'https://images.unsplash.com/photo-1581636625402-29b2a704ef13?w=800&q=80', // Soda bottle
    'Guaraná Antarctica Lata' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=800&q=80', // Can drink
    'Sprite Lata' => 'https://images.unsplash.com/photo-1625772452859-1c03d5bf1137?w=800&q=80', // Sprite can
    'Sprite 2L' => 'https://images.unsplash.com/photo-1581636625402-29b2a704ef13?w=800&q=80',
    'Cerveja Brahma Lata' => 'https://images.unsplash.com/photo-1608270586620-248524c67de9?w=800&q=80', // Beer can
    'Cerveja Brahma Long Neck' => 'https://images.unsplash.com/photo-1535958636474-b021ee887b13?w=800&q=80', // Beer bottle
    'Suco de Laranja' => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=800&q=80', // Orange juice
    'Caipirinha' => 'https://images.unsplash.com/photo-1514361892635-6b07e31e75f9?w=800&q=80', // Caipirinha cocktail

    // === CARNES E CHURRASCO ===
    'Picanha na Chapa' => 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=800&q=80', // Grilled steak
    'Picanha na Brasa' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&q=80', // BBQ steak
    'Contra-Filé Acebolado' => 'https://images.unsplash.com/photo-1600891964092-4316c288032e?w=800&q=80', // Steak with onions
    'Contra-Filé' => 'https://images.unsplash.com/photo-1603360946369-dc9bb6258143?w=800&q=80', // Beef steak
    'Costela Assada' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80', // Roasted ribs
    'Costela de Porco' => 'https://images.unsplash.com/photo-1529692236671-f1f6cf9683ba?w=800&q=80', // Pork ribs
    'Fraldinha' => 'https://images.unsplash.com/photo-1558030006-450675393462?w=800&q=80', // Beef cut
    'Cupim' => 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=800&q=80', // Brazilian beef
    'Linguiça Artesanal' => 'https://images.unsplash.com/photo-1612392062798-2ecb0d5d89cf?w=800&q=80', // Sausage
    'Coração de Frango' => 'https://images.unsplash.com/photo-1529692236671-f1f6cf9683ba?w=800&q=80', // Chicken hearts

    // === PRATOS PRINCIPAIS ===
    'Feijoada Completa' => 'https://images.unsplash.com/photo-1623428187969-5da2dcea5ebf?w=800&q=80', // Brazilian feijoada
    'Strogonoff de Carne' => 'https://images.unsplash.com/photo-1574484284002-952d92456975?w=800&q=80', // Beef strogonoff
    'Strogonoff de Frango' => 'https://images.unsplash.com/photo-1598103442097-8b74394b95c6?w=800&q=80', // Chicken strogonoff
    'Carne de Panela' => 'https://images.unsplash.com/photo-1529692236671-f1f6cf9683ba?w=800&q=80', // Pot roast
    'Frango a Parmegiana' => 'https://images.unsplash.com/photo-1632778149955-e80f8ceca2e8?w=800&q=80', // Chicken parmigiana
    'Bife a Parmegiana' => 'https://images.unsplash.com/photo-1595295333158-4742f28fbd85?w=800&q=80', // Beef parmigiana
    'Bife Acebolado' => 'https://images.unsplash.com/photo-1600891964092-4316c288032e?w=800&q=80', // Steak with onions
    'Filé de Frango Grelhado' => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=800&q=80', // Grilled chicken

    // === PETISCOS ===
    'Frango a Passarinho' => 'https://images.unsplash.com/photo-1562967914-608f82629710?w=800&q=80', // Fried chicken
    'Frango à Passarinho' => 'https://images.unsplash.com/photo-1562967914-608f82629710?w=800&q=80',
    'Calabresa Acebolada' => 'https://images.unsplash.com/photo-1612392062798-2ecb0d5d89cf?w=800&q=80', // Sausage with onions
    'Torresmo' => 'https://images.unsplash.com/photo-1529692236671-f1f6cf9683ba?w=800&q=80', // Pork crackling
    'Porção de Batata Frita' => 'https://images.unsplash.com/photo-1576107232684-1279f390859f?w=800&q=80', // French fries
    'Mandioca Frita' => 'https://images.unsplash.com/photo-1639024471283-03518883512d?w=800&q=80', // Fried cassava
    'Bolinho de Bacalhau' => 'https://images.unsplash.com/photo-1599487488170-d11ec9c172f0?w=800&q=80', // Cod fish croquette
    'Pastel Sortido' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=800&q=80', // Brazilian pastel
    'Iscas de Peixe' => 'https://images.unsplash.com/photo-1619881589928-0485dc3c7e3d?w=800&q=80', // Fish strips

    // === MARMITAS ===
    'Marmitex Tradicional' => 'https://images.unsplash.com/photo-1623428187969-5da2dcea5ebf?w=800&q=80', // Brazilian lunch box
    'Marmitex Executivo' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800&q=80', // Lunch box
    'Marmitex Completo' => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=800&q=80', // Complete meal
    'Marmitex Premium' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800&q=80',
    'Marmitex Churrasco' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&q=80', // BBQ lunch box

    // === ACOMPANHAMENTOS ===
    'Arroz Branco' => 'https://images.unsplash.com/photo-1516684732162-798a0062be99?w=800&q=80', // White rice
    'Farofa Especial' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=800&q=80', // Farofa
    'Vinagrete' => 'https://images.unsplash.com/photo-1546549032-9571cd6b27df?w=800&q=80', // Vinaigrette
];

function downloadImage($url, $filename) {
    try {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$imageData || strlen($imageData) < 1000) {
            return null;
        }

        // Salvar no storage
        $path = "products/{$filename}";
        Storage::disk('public')->put($path, $imageData);

        return $path;

    } catch (\Exception $e) {
        return null;
    }
}

$tenants = Tenant::all();
$totalUpdated = 0;
$totalFailed = 0;

foreach ($tenants as $tenant) {
    echo "📍 {$tenant->name}:\n";
    tenancy()->initialize($tenant);

    $products = Product::with('category')->get();
    $updated = 0;
    $failed = 0;

    foreach ($products as $product) {
        echo "  🍽️  {$product->name}... ";

        // Tentar encontrar URL específica
        $imageUrl = $productImages[$product->name] ?? null;

        if (!$imageUrl) {
            echo "⚠️  Sem imagem mapeada\n";
            $failed++;
            continue;
        }

        $newImage = downloadImage($imageUrl, "{$tenant->slug}_{$product->id}_hq.jpg");

        if ($newImage) {
            $product->image = $newImage;
            $product->save();
            echo "✅\n";
            $updated++;
        } else {
            echo "❌\n";
            $failed++;
        }

        // Delay para não sobrecarregar
        usleep(800000); // 0.8 segundo
    }

    echo "  📊 Resultado: {$updated} ✅ | {$failed} ❌\n\n";

    $totalUpdated += $updated;
    $totalFailed += $failed;

    tenancy()->end();
}

echo "\n🎉 Concluído!\n";
echo "✅ Total atualizado: {$totalUpdated}\n";
if ($totalFailed > 0) {
    echo "❌ Total falhou: {$totalFailed}\n";
}

