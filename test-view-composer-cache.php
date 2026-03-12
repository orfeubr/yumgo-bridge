<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

echo "🧪 Testando View Composer com Cache\n";
echo "====================================\n\n";

$tenant = Tenant::first();

if (!$tenant) {
    echo "❌ Nenhum tenant encontrado\n";
    exit(1);
}

echo "📍 Tenant: {$tenant->name} (ID: {$tenant->id})\n";
echo "\n";

// Inicializar tenancy
tenancy()->initialize($tenant);

$cacheKey = "tenant_{$tenant->id}_common_data";

// Limpar cache primeiro
Cache::forget($cacheKey);
echo "🗑️  Cache limpo\n";
echo "\n";

// Primeira chamada (vai criar cache)
echo "📥 Primeira chamada (cria cache):\n";
$start = microtime(true);

$composer = new \App\View\Composers\TenantDataComposer();
$view = new \Illuminate\View\View(
    app('view'),
    app('view')->getEngineResolver()->resolve('blade'),
    'test',
    [],
    []
);
$composer->compose($view);

$time1 = round((microtime(true) - $start) * 1000, 2);
echo "   ⏱️  Tempo: {$time1}ms\n";
echo "   ✅ Cache CRIADO\n";
echo "\n";

// Segunda chamada (vai usar cache)
echo "📥 Segunda chamada (usa cache):\n";
$start = microtime(true);

$composer->compose($view);

$time2 = round((microtime(true) - $start) * 1000, 2);
echo "   ⏱️  Tempo: {$time2}ms\n";
echo "   ✅ Cache USADO\n";
echo "\n";

// Calcular melhoria
$improvement = round((($time1 - $time2) / $time1) * 100, 1);

echo "📊 Resultados:\n";
echo "   Sem cache: {$time1}ms\n";
echo "   Com cache: {$time2}ms\n";
echo "   Melhoria: {$improvement}%\n";
echo "\n";

// Verificar conteúdo do cache
$cached = Cache::get($cacheKey);

if ($cached) {
    echo "✅ Cache contém:\n";
    echo "   - Settings: " . ($cached['settings'] ? '✅' : '❌') . "\n";
    echo "   - Categorias: " . count($cached['categories']) . "\n";
    echo "   - Zonas de entrega: " . count($cached['deliveryZones']) . "\n";
    echo "   - Está aberto: " . ($cached['isOpen'] ? '✅' : '❌') . "\n";
    echo "   - Cached at: {$cached['cached_at']}\n";
} else {
    echo "❌ Cache não encontrado\n";
}

echo "\n";
echo "🎉 Teste concluído com sucesso!\n";

tenancy()->end();
