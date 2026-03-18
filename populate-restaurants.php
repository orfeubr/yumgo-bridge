<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

echo "🚀 Iniciando população de restaurantes...\n\n";

// Função para baixar imagem da internet
function downloadImage($url, $filename) {
    try {
        // Usar Lorem Picsum para imagens aleatórias de comida
        // Categoria food: https://picsum.photos/800/600
        $picsum_url = "https://picsum.photos/800/600?random=" . rand(1, 10000);

        $ch = curl_init($picsum_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $imageData && strlen($imageData) > 1000) {
            $path = "products/{$filename}";
            $result = Storage::disk('public')->put($path, $imageData);

            // Verificar se arquivo foi criado
            $fullPath = storage_path("app/public/{$path}");
            if (!file_exists($fullPath)) {
                echo " [DEBUG: Arquivo NÃO criado em {$fullPath}]";
                return null;
            }

            return "/storage/{$path}";
        }
    } catch (\Exception $e) {
        echo "  ⚠️ Erro ao baixar imagem: {$e->getMessage()}\n";
    }
    return null;
}

// Dados para cada restaurante - PRATOS TRADICIONAIS BRASILEIROS
$restaurantsData = [
    'marmitariadagi' => [
        'name' => 'Marmitaria da Gi',
        'categories' => [
            'Marmitas Executivas' => [
                ['name' => 'Feijoada Completa', 'price' => 25.90, 'description' => 'Feijoada com arroz, couve, farofa e laranja', 'image' => 'https://images.unsplash.com/photo-1628418384612-71f08cd4447e?w=800'],
                ['name' => 'Strogonoff de Carne', 'price' => 26.90, 'description' => 'Strogonoff de carne com arroz e batata palha', 'image' => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=800'],
                ['name' => 'Strogonoff de Frango', 'price' => 24.90, 'description' => 'Strogonoff de frango com arroz e batata palha', 'image' => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=800'],
                ['name' => 'Carne de Panela', 'price' => 23.90, 'description' => 'Carne de panela com arroz, feijão e batata', 'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=800'],
                ['name' => 'Frango a Parmegiana', 'price' => 25.90, 'description' => 'Filé de frango empanado com molho e queijo', 'image' => 'https://images.unsplash.com/photo-1632778149955-e80f8ceca2e8?w=800'],
                ['name' => 'Bife a Parmegiana', 'price' => 28.90, 'description' => 'Bife bovino empanado com molho e queijo', 'image' => 'https://images.unsplash.com/photo-1632778149955-e80f8ceca2e8?w=800'],
            ],
            'Carnes' => [
                ['name' => 'Contra-Filé Acebolado', 'price' => 27.90, 'description' => 'Contra-filé grelhado com cebola', 'image' => 'https://images.unsplash.com/photo-1558030006-450675393462?w=800'],
                ['name' => 'Bife Acebolado', 'price' => 24.90, 'description' => 'Bife bovino com cebola caramelizada', 'image' => 'https://images.unsplash.com/photo-1588168333986-5078d3ae3976?w=800'],
                ['name' => 'Filé de Frango Grelhado', 'price' => 22.90, 'description' => 'Filé de frango grelhado temperado', 'image' => 'https://images.unsplash.com/photo-1598103442097-8b74394b95c6?w=800'],
                ['name' => 'Frango à Passarinho', 'price' => 23.90, 'description' => 'Frango frito em pedaços com alho', 'image' => 'https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?w=800'],
                ['name' => 'Costela de Porco', 'price' => 26.90, 'description' => 'Costela de porco assada', 'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=800'],
            ],
            'Bebidas' => [
                ['name' => 'Coca-Cola Lata', 'price' => 5.00, 'description' => 'Coca-Cola 350ml gelada', 'image' => 'https://images.unsplash.com/photo-1554866585-cd94860890b7?w=800'],
                ['name' => 'Sprite Lata', 'price' => 5.00, 'description' => 'Sprite 350ml gelada', 'image' => 'https://images.unsplash.com/photo-1625772452859-1c03d5bf1137?w=800'],
                ['name' => 'Guaraná Antarctica Lata', 'price' => 5.00, 'description' => 'Guaraná Antarctica 350ml gelada', 'image' => 'https://images.unsplash.com/photo-1581636625402-29b2a704ef13?w=800'],
                ['name' => 'Suco de Laranja', 'price' => 7.00, 'description' => 'Suco de laranja natural 500ml', 'image' => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=800'],
            ],
        ],
    ],
    'botecodomeurei' => [
        'name' => 'Boteco do Meu Rei',
        'categories' => [
            'Petiscos' => [
                ['name' => 'Porção de Batata Frita', 'price' => 18.00, 'description' => 'Batata frita crocante 500g', 'image' => 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=800'],
                ['name' => 'Calabresa Acebolada', 'price' => 25.00, 'description' => 'Linguiça calabresa com cebola 400g', 'image' => 'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?w=800'],
                ['name' => 'Torresmo', 'price' => 22.00, 'description' => 'Torresmo crocante 300g', 'image' => 'https://images.unsplash.com/photo-1619366533111-2e09e8e19b89?w=800'],
                ['name' => 'Mandioca Frita', 'price' => 16.00, 'description' => 'Mandioca frita sequinha 500g', 'image' => 'https://images.unsplash.com/photo-1639744091413-62da60e97d8b?w=800'],
                ['name' => 'Bolinho de Bacalhau', 'price' => 28.00, 'description' => 'Bolinhos de bacalhau 12 unidades', 'image' => 'https://images.unsplash.com/photo-1626200419199-391ae4be7a41?w=800'],
                ['name' => 'Pastel Sortido', 'price' => 20.00, 'description' => 'Pastel de carne, queijo ou frango 6 unidades', 'image' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=800'],
                ['name' => 'Iscas de Peixe', 'price' => 32.00, 'description' => 'Iscas de tilápia empanada 400g', 'image' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=800'],
                ['name' => 'Frango a Passarinho', 'price' => 26.00, 'description' => 'Frango frito com alho 500g', 'image' => 'https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?w=800'],
            ],
            'Bebidas' => [
                ['name' => 'Cerveja Brahma Lata', 'price' => 6.00, 'description' => 'Cerveja Brahma 350ml gelada', 'image' => 'https://images.unsplash.com/photo-1608270586620-248524c67de9?w=800'],
                ['name' => 'Caipirinha', 'price' => 12.00, 'description' => 'Cachaça, limão, açúcar e gelo', 'image' => 'https://images.unsplash.com/photo-1551538827-9c037cb4f32a?w=800'],
                ['name' => 'Coca-Cola 2L', 'price' => 10.00, 'description' => 'Coca-Cola 2 litros', 'image' => 'https://images.unsplash.com/photo-1554866585-cd94860890b7?w=800'],
                ['name' => 'Guaraná Antarctica 2L', 'price' => 10.00, 'description' => 'Guaraná Antarctica 2 litros', 'image' => 'https://images.unsplash.com/photo-1581636625402-29b2a704ef13?w=800'],
                ['name' => 'Sprite Lata', 'price' => 5.00, 'description' => 'Sprite 350ml gelada', 'image' => 'https://images.unsplash.com/photo-1625772452859-1c03d5bf1137?w=800'],
                ['name' => 'Suco de Laranja', 'price' => 7.00, 'description' => 'Suco de laranja natural 500ml', 'image' => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=800'],
            ],
            'Pratos Principais' => [
                ['name' => 'Feijoada Completa', 'price' => 38.00, 'description' => 'Feijoada tradicional para 1 pessoa', 'image' => 'https://images.unsplash.com/photo-1628418384612-71f08cd4447e?w=800'],
                ['name' => 'Picanha na Chapa', 'price' => 45.00, 'description' => 'Picanha grelhada 400g com arroz e farofa', 'image' => 'https://images.unsplash.com/photo-1546964124-0cce460f38ef?w=800'],
                ['name' => 'Contra-Filé Acebolado', 'price' => 42.00, 'description' => 'Contra-filé 350g com arroz e feijão', 'image' => 'https://images.unsplash.com/photo-1558030006-450675393462?w=800'],
            ],
        ],
    ],
    'lospampas' => [
        'name' => 'Los Pampas',
        'categories' => [
            'Carnes Nobres' => [
                ['name' => 'Picanha na Brasa', 'price' => 89.90, 'description' => 'Picanha argentina 500g', 'image' => 'https://images.unsplash.com/photo-1546964124-0cce460f38ef?w=800'],
                ['name' => 'Contra-Filé', 'price' => 79.90, 'description' => 'Contra-filé grelhado 450g', 'image' => 'https://images.unsplash.com/photo-1558030006-450675393462?w=800'],
                ['name' => 'Costela Assada', 'price' => 75.00, 'description' => 'Costela bovina assada 600g', 'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=800'],
                ['name' => 'Fraldinha', 'price' => 69.90, 'description' => 'Fraldinha na brasa 400g', 'image' => 'https://images.unsplash.com/photo-1603360946369-dc9bb6258143?w=800'],
                ['name' => 'Cupim', 'price' => 85.00, 'description' => 'Cupim assado 500g', 'image' => 'https://images.unsplash.com/photo-1588168333986-5078d3ae3976?w=800'],
                ['name' => 'Linguiça Artesanal', 'price' => 35.00, 'description' => 'Linguiça artesanal 300g', 'image' => 'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?w=800'],
                ['name' => 'Coração de Frango', 'price' => 32.00, 'description' => 'Coração na brasa 300g', 'image' => 'https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?w=800'],
            ],
            'Acompanhamentos' => [
                ['name' => 'Farofa Especial', 'price' => 15.00, 'description' => 'Farofa com bacon e ovos', 'image' => 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=800'],
                ['name' => 'Vinagrete', 'price' => 8.00, 'description' => 'Vinagrete tradicional', 'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=800'],
                ['name' => 'Arroz Branco', 'price' => 10.00, 'description' => 'Arroz branco soltinho', 'image' => 'https://images.unsplash.com/photo-1516684732162-798a0062be99?w=800'],
            ],
            'Bebidas' => [
                ['name' => 'Cerveja Brahma Long Neck', 'price' => 10.00, 'description' => 'Cerveja Brahma 330ml gelada', 'image' => 'https://images.unsplash.com/photo-1608270586620-248524c67de9?w=800'],
                ['name' => 'Coca-Cola 2L', 'price' => 12.00, 'description' => 'Coca-Cola 2 litros', 'image' => 'https://images.unsplash.com/photo-1554866585-cd94860890b7?w=800'],
                ['name' => 'Guaraná Antarctica 2L', 'price' => 12.00, 'description' => 'Guaraná Antarctica 2 litros', 'image' => 'https://images.unsplash.com/photo-1581636625402-29b2a704ef13?w=800'],
                ['name' => 'Sprite Lata', 'price' => 8.00, 'description' => 'Sprite 350ml gelada', 'image' => 'https://images.unsplash.com/photo-1625772452859-1c03d5bf1137?w=800'],
                ['name' => 'Suco de Laranja', 'price' => 10.00, 'description' => 'Suco de laranja natural 500ml', 'image' => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=800'],
            ],
        ],
    ],
];

// Processar cada restaurante
foreach ($restaurantsData as $slug => $data) {
    echo "📍 Processando: {$data['name']} ({$slug})\n";

    $tenant = Tenant::find($slug);
    if (!$tenant) {
        echo "  ⚠️ Tenant não encontrado: {$slug}\n\n";
        continue;
    }

    // Inicializar tenancy
    tenancy()->initialize($tenant);

    echo "  🏪 Tenant inicializado: " . tenant('name') . "\n";

    // Limpar dados antigos (opcional)
    echo "  🗑️ Limpando produtos antigos...\n";
    Product::truncate();
    echo "  🗑️ Limpando categorias antigas...\n";
    Category::truncate();

    // Criar categorias e produtos
    foreach ($data['categories'] as $categoryName => $products) {
        echo "  📁 Criando categoria: {$categoryName}\n";

        $category = Category::create([
            'name' => $categoryName,
            'slug' => \Illuminate\Support\Str::slug($categoryName),
            'description' => "Categoria {$categoryName}",
            'is_active' => true,
            'sort_order' => 0,
        ]);

        foreach ($products as $index => $productData) {
            echo "    📦 Criando produto: {$productData['name']}";

            // Baixar imagem
            $imageUrl = $productData['image'];
            $filename = $slug . '_' . $category->id . '_' . $index . '.jpg';
            echo " (baixando imagem...)";

            // DEBUG: Verificar contexto de tenancy
            $currentTenant = tenant('id');
            echo " [tenant:{$currentTenant}]";

            $imagePath = downloadImage($imageUrl, $filename);

            if (!$imagePath) {
                echo " ⚠️ Usando placeholder\n";
                $imagePath = '/images/placeholder.jpg';
            } else {
                echo " ✅ ({$imagePath})\n";
            }

            Product::create([
                'category_id' => $category->id,
                'name' => $productData['name'],
                'slug' => \Illuminate\Support\Str::slug($productData['name']),
                'description' => $productData['description'],
                'price' => $productData['price'],
                'image' => $imagePath,
                'is_active' => true,
                'is_featured' => $index === 0, // Primeiro produto é destaque
                'has_stock_control' => false,
            ]);
        }
    }

    echo "  ✅ {$data['name']} populado com sucesso!\n\n";

    // Finalizar tenancy
    tenancy()->end();
}

echo "🎉 População completa! Todos os restaurantes foram populados.\n";
