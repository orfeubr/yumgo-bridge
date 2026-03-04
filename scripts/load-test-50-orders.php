#!/usr/bin/env php
<?php

/**
 * TESTE DE CARGA: 50 PEDIDOS SIMULTÂNEOS REAIS
 *
 * Simula 50 clientes diferentes fazendo pedidos ao mesmo tempo usando cURL assíncrono
 *
 * Uso: php scripts/load-test-50-orders.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Configuração
$baseUrl = 'https://marmitariadagi.yumgo.com.br';
$numOrders = 50;
$tenant = \App\Models\Tenant::where('slug', 'marmitariadagi')->first();

if (!$tenant) {
    die("❌ Tenant 'marmitaria-gi' não encontrado\n");
}

tenancy()->initialize($tenant);

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  🔥 TESTE DE CARGA: 50 PEDIDOS SIMULTÂNEOS                ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "🎯 Target: {$baseUrl}\n";
echo "👥 Clientes: {$numOrders}\n";
echo "🏢 Tenant: {$tenant->name}\n\n";

// Cria 50 clientes únicos
echo "📝 Criando {$numOrders} clientes únicos...\n";
$customers = [];

// Buscar produtos ativos
$products = \App\Models\Product::where('is_active', true)->get();

if ($products->count() === 0) {
    die("❌ Nenhum produto ativo disponível\n");
}

echo "📦 Produtos ativos: {$products->count()}\n\n";

$timestamp = time();
for ($i = 1; $i <= $numOrders; $i++) {
    $customer = \App\Models\Customer::create([
        'name' => "Load Test Cliente {$i}",
        'email' => "loadtest{$i}_{$timestamp}@test.com",
        'phone' => sprintf('119%08d', $i + $timestamp),
        'password' => bcrypt('password123'),
        'cashback_balance' => rand(0, 50),
        'loyalty_tier' => 'bronze',
    ]);

    $customers[] = $customer;

    if ($i % 10 === 0) {
        echo "   ✓ {$i}/{$numOrders} clientes criados\n";
    }
}

echo "✅ {$numOrders} clientes criados com sucesso\n\n";

// Preparar requisições cURL assíncronas
echo "🚀 Preparando {$numOrders} requisições simultâneas...\n\n";

$multiHandle = curl_multi_init();
$curlHandles = [];
$startTimes = [];
$results = [
    'success' => 0,
    'errors' => 0,
    'response_times' => [],
    'errors_details' => [],
];

foreach ($customers as $index => $customer) {
    // Dados do pedido
    $orderData = [
        'customer_id' => $customer->id,
        'items' => [
            [
                'product_id' => $products->random()->id,
                'quantity' => rand(1, 3),
                'unit_price' => 50.00,
                'subtotal' => 50.00 * rand(1, 3),
            ]
        ],
        'subtotal' => rand(50, 200),
        'delivery_fee' => 5.00,
        'payment_method' => 'pix',
        'delivery_address' => [
            'street' => 'Rua Teste Load',
            'number' => (string)rand(1, 999),
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zipcode' => '01000-000',
        ],
        'use_cashback' => false,
    ];

    // Configurar cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "{$baseUrl}/api/v1/orders",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($orderData),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
    ]);

    $curlHandles[$index] = $ch;
    $startTimes[$index] = microtime(true);
    curl_multi_add_handle($multiHandle, $ch);
}

echo "⏱️  Disparando {$numOrders} requisições SIMULTÂNEAS...\n\n";

$globalStartTime = microtime(true);

// Executar todas as requisições em paralelo
$running = null;
do {
    curl_multi_exec($multiHandle, $running);
    curl_multi_select($multiHandle);
} while ($running > 0);

$globalEndTime = microtime(true);
$totalTime = ($globalEndTime - $globalStartTime) * 1000; // ms

// Processar resultados
echo "📊 Processando resultados...\n\n";

foreach ($curlHandles as $index => $ch) {
    $endTime = microtime(true);
    $responseTime = ($endTime - $startTimes[$index]) * 1000; // ms
    $results['response_times'][] = $responseTime;

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $response = curl_multi_getcontent($ch);
    $error = curl_error($ch);

    if ($httpCode >= 200 && $httpCode < 300 && empty($error)) {
        $results['success']++;
        $responseData = json_decode($response, true);
        $orderId = $responseData['order']['id'] ?? 'N/A';

        echo sprintf(
            "✅ [%02d/%02d] HTTP %d | Pedido #%s | %.2fms\n",
            $index + 1,
            $numOrders,
            $httpCode,
            $orderId,
            $responseTime
        );
    } else {
        $results['errors']++;
        $results['errors_details'][] = [
            'index' => $index + 1,
            'http_code' => $httpCode,
            'error' => $error ?: substr($response, 0, 100),
        ];

        echo sprintf(
            "❌ [%02d/%02d] HTTP %d | ERRO: %s\n",
            $index + 1,
            $numOrders,
            $httpCode,
            $error ?: 'Response error'
        );
    }

    curl_multi_remove_handle($multiHandle, $ch);
    curl_close($ch);
}

curl_multi_close($multiHandle);

// Estatísticas
$avgResponseTime = array_sum($results['response_times']) / count($results['response_times']);
$minResponseTime = min($results['response_times']);
$maxResponseTime = max($results['response_times']);
sort($results['response_times']);
$p50 = $results['response_times'][floor(count($results['response_times']) * 0.50)];
$p95 = $results['response_times'][floor(count($results['response_times']) * 0.95)];
$p99 = $results['response_times'][floor(count($results['response_times']) * 0.99)];

$throughput = ($results['success'] / ($totalTime / 1000)); // pedidos/segundo
$successRate = ($results['success'] / $numOrders) * 100;

// Relatório Final
echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  📊 RELATÓRIO FINAL                                        ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "⏱️  TEMPO TOTAL: " . number_format($totalTime, 2) . "ms (" . number_format($totalTime / 1000, 2) . "s)\n";
echo "✅ SUCESSOS: {$results['success']}/{$numOrders} (" . number_format($successRate, 2) . "%)\n";
echo "❌ ERROS: {$results['errors']}/{$numOrders}\n";
echo "🚀 THROUGHPUT: " . number_format($throughput, 2) . " pedidos/segundo\n\n";

echo "📈 LATÊNCIA (Response Time):\n";
echo "   ├─ Média: " . number_format($avgResponseTime, 2) . "ms\n";
echo "   ├─ Mínimo: " . number_format($minResponseTime, 2) . "ms\n";
echo "   ├─ Máximo: " . number_format($maxResponseTime, 2) . "ms\n";
echo "   ├─ P50 (mediana): " . number_format($p50, 2) . "ms\n";
echo "   ├─ P95: " . number_format($p95, 2) . "ms\n";
echo "   └─ P99: " . number_format($p99, 2) . "ms\n\n";

// Diagnóstico
echo "🩺 DIAGNÓSTICO:\n\n";

if ($successRate >= 95) {
    echo "   ✅ Taxa de sucesso EXCELENTE (≥95%)\n";
} elseif ($successRate >= 90) {
    echo "   ⚠️  Taxa de sucesso BOA (≥90%) mas pode melhorar\n";
} else {
    echo "   ❌ Taxa de sucesso RUIM (<90%) - REQUER ATENÇÃO!\n";
}

if ($avgResponseTime < 500) {
    echo "   ✅ Latência média EXCELENTE (<500ms)\n";
} elseif ($avgResponseTime < 1000) {
    echo "   ⚠️  Latência média BOA (<1s) mas pode melhorar\n";
} else {
    echo "   ❌ Latência média RUIM (>1s) - OTIMIZAR!\n";
}

if ($p95 < 1000) {
    echo "   ✅ P95 EXCELENTE (<1s) - 95% dos pedidos são rápidos\n";
} elseif ($p95 < 2000) {
    echo "   ⚠️  P95 BOA (<2s) mas 5% dos pedidos estão lentos\n";
} else {
    echo "   ❌ P95 RUIM (>2s) - Muitos pedidos lentos!\n";
}

if ($throughput >= 10) {
    echo "   ✅ Throughput EXCELENTE (≥10 pedidos/s)\n";
} elseif ($throughput >= 5) {
    echo "   ⚠️  Throughput MODERADO (≥5 pedidos/s)\n";
} else {
    echo "   ❌ Throughput BAIXO (<5 pedidos/s) - Escalar!\n";
}

echo "\n";

if (!empty($results['errors_details'])) {
    echo "❌ DETALHES DOS ERROS:\n";
    foreach ($results['errors_details'] as $error) {
        echo sprintf(
            "   [%02d] HTTP %d: %s\n",
            $error['index'],
            $error['http_code'],
            $error['error']
        );
    }
    echo "\n";
}

// Recomendações
echo "💡 RECOMENDAÇÕES:\n\n";

if ($avgResponseTime > 500) {
    echo "   • Aumentar workers PHP-FPM (pm.max_children)\n";
    echo "   • Otimizar queries SQL (adicionar índices)\n";
    echo "   • Implementar cache de produtos/configurações\n";
}

if ($p95 > 1000) {
    echo "   • Analisar queries lentas (EXPLAIN)\n";
    echo "   • Usar eager loading (N+1 queries)\n";
    echo "   • Aumentar pool de conexões do banco\n";
}

if ($successRate < 95) {
    echo "   • Verificar logs de erro (storage/logs/laravel.log)\n";
    echo "   • Aumentar timeout do PHP-FPM\n";
    echo "   • Validar pool de conexões PostgreSQL\n";
}

if ($throughput < 10) {
    echo "   • Escalar horizontalmente (mais instâncias)\n";
    echo "   • Load balancer (Nginx upstream)\n";
    echo "   • Redis para cache de sessão\n";
}

echo "\n";

// Limpar dados de teste
echo "🧹 Limpando dados de teste...\n";
foreach ($customers as $customer) {
    $customer->orders()->delete();
    $customer->cashbackTransactions()->delete();
    $customer->delete();
}
echo "✅ Limpeza concluída\n\n";

echo "════════════════════════════════════════════════════════════\n";
echo "Teste finalizado em " . date('Y-m-d H:i:s') . "\n";
echo "════════════════════════════════════════════════════════════\n\n";
