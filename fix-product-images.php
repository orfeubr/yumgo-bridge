<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

echo "🖼️  Atualizando imagens de produtos com fotos reais de comida...\n\n";

// Mapeamento de termos de busca por tipo de produto
$foodSearchTerms = [
    // Petiscos
    'Frango a Passarinho' => 'fried-chicken-pieces',
    'Calabresa Acebolada' => 'grilled-sausage-onions',
    'Torresmo' => 'pork-crackling',
    'Porção de Batata Frita' => 'french-fries',
    'Mandioca Frita' => 'fried-cassava-yuca',
    'Bolinho de Bacalhau' => 'codfish-croquette',
    'Pastel Sortido' => 'brazilian-pastel',
    'Iscas de Peixe' => 'fried-fish-strips',

    // Bebidas
    'Suco de Laranja' => 'orange-juice-glass',
    'Cerveja Brahma Lata' => 'beer-can',
    'Caipirinha' => 'caipirinha-cocktail',
    'Coca-Cola 2L' => 'coca-cola-bottle',
    'Guaraná Antarctica 2L' => 'guarana-bottle',
    'Sprite Lata' => 'sprite-can',

    // Pratos Principais
    'Feijoada Completa' => 'brazilian-feijoada',
    'Picanha na Chapa' => 'grilled-picanha-steak',
    'Contra-Filé Acebolado' => 'beef-steak-onions',

    // Marmitas
    'Marmitex Tradicional' => 'lunch-box-rice-beans-meat',
    'Marmitex Executivo' => 'brazilian-lunch-box',
    'Marmitex Completo' => 'complete-meal-box',
];

function downloadFoodImage($searchTerm, $filename) {
    try {
        // Usar Lorem Picsum com seed para imagens consistentes
        // Mas vamos usar uma API que tem categorias de comida
        // Foodish API - Fotos aleatórias de comida
        $categories = [
            'burger', 'pizza', 'pasta', 'dessert', 'salad',
            'sandwich', 'soup', 'chicken', 'steak', 'seafood',
            'fries', 'rice', 'noodles', 'drink', 'juice'
        ];

        // Tentar determinar categoria pela busca
        $category = 'burger'; // padrão
        $lower = strtolower($searchTerm);

        if (str_contains($lower, 'chicken') || str_contains($lower, 'frango')) $category = 'chicken';
        elseif (str_contains($lower, 'steak') || str_contains($lower, 'beef') || str_contains($lower, 'picanha')) $category = 'steak';
        elseif (str_contains($lower, 'fries') || str_contains($lower, 'batata')) $category = 'fries';
        elseif (str_contains($lower, 'rice') || str_contains($lower, 'lunch-box')) $category = 'rice';
        elseif (str_contains($lower, 'fish') || str_contains($lower, 'peixe')) $category = 'seafood';
        elseif (str_contains($lower, 'juice') || str_contains($lower, 'suco')) $category = 'juice';
        elseif (str_contains($lower, 'beer') || str_contains($lower, 'drink') || str_contains($lower, 'coca')) $category = 'drink';
        elseif (str_contains($lower, 'feijoada')) $category = 'soup';
        elseif (str_contains($lower, 'pastel')) $category = 'sandwich';

        // Usar Foodish API
        $apiUrl = "https://foodish-api.com/api/images/{$category}";

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            echo "   ⚠️  API retornou código {$httpCode}, usando imagem padrão\n";
            return null;
        }

        $data = json_decode($response, true);
        if (!isset($data['image'])) {
            echo "   ⚠️  Resposta da API inválida\n";
            return null;
        }

        $imageUrl = $data['image'];

        // Baixar a imagem
        $ch = curl_init($imageUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $imageData = curl_exec($ch);
        curl_close($ch);

        if (!$imageData) {
            echo "   ❌ Falha ao baixar imagem\n";
            return null;
        }

        // Salvar no storage
        $path = "products/{$filename}";
        Storage::disk('public')->put($path, $imageData);

        echo "   ✅ Imagem baixada: {$category}\n";
        return $path;

    } catch (\Exception $e) {
        echo "   ❌ Erro: {$e->getMessage()}\n";
        return null;
    }
}

$tenants = Tenant::all();

foreach ($tenants as $tenant) {
    echo "📍 {$tenant->name}:\n";
    tenancy()->initialize($tenant);

    $products = Product::all();
    $updated = 0;

    foreach ($products as $product) {
        $searchTerm = $foodSearchTerms[$product->name] ?? strtolower($product->name);

        echo "  🍽️  {$product->name}... ";

        $newImage = downloadFoodImage($searchTerm, "{$tenant->slug}_{$product->id}_food.jpg");

        if ($newImage) {
            $product->image = $newImage;
            $product->save();
            $updated++;
        }

        // Delay para não sobrecarregar a API
        sleep(2);
    }

    echo "\n  ✅ {$updated} imagens atualizadas\n\n";

    tenancy()->end();
}

echo "🎉 Concluído!\n";
