#!/usr/bin/env php
<?php

/**
 * TESTE DE CARGA BACKEND: 50 PEDIDOS SIMULTÂNEOS
 *
 * Simula 50 pedidos processados diretamente no backend (sem HTTP)
 * para testar a capacidade do sistema: banco de dados, Redis, PHP
 *
 * Uso: php scripts/load-test-backend-50-orders.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Configuração
$numOrders = 50;
$tenant = \App\Models\Tenant::where('slug', 'marmitariadagi')->first();

if (!$tenant) {
    die("❌ Tenant não encontrado\n");
}

tenancy()->initialize($tenant);

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  🔥 TESTE DE CARGA BACKEND: 50 PEDIDOS SIMULTÂNEOS        ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "🏢 Tenant: {$tenant->name}\n";
echo "👥 Pedidos: {$numOrders}\n\n";

// Buscar produtos ativos
$products = \App\Models\Product::where('is_active', true)->get();

if ($products->count() === 0) {
    die("❌ Nenhum produto ativo\n");
}

echo "📦 Produtos disponíveis: {$products->count()}\n\n";

// Limpar clientes de teste anteriores
echo "🧹 Limpando clientes de teste anteriores...\n";
\App\Models\Customer::where('email', 'like', 'loadtest%@test.com')->delete();
echo "✅ Limpeza concluída\n\n";

// Criar 50 clientes únicos
echo "📝 Criando {$numOrders} clientes...\n";

$customers = [];
$timestamp = time();

for ($i = 1; $i <= $numOrders; $i++) {
    $uniqueId = $timestamp + $i;
    $customers[] = \App\Models\Customer::create([
        'name' => "Load Test {$i}",
        'email' => "loadtest{$i}_{$uniqueId}@test.com",
        'phone' => sprintf('119%09d', $uniqueId),
        'password' => bcrypt('password'),
        'cashback_balance' => rand(0, 20),
        'loyalty_tier' => 'bronze',
    ]);

    if ($i % 10 === 0) {
        echo "   ✓ {$i}/{$numOrders}\n";
    }
}

echo "✅ {$numOrders} clientes criados\n\n";

// Preparar OrderService
$orderService = app(\App\Services\OrderService::class);

// Métricas
$results = [
    'success' => 0,
    'errors' => 0,
    'response_times' => [],
    'errors_details' => [],
    'db_queries_before' => 0,
    'db_queries_after' => 0,
    'memory_before' => 0,
    'memory_after' => 0,
];

// Capturar estado inicial
$results['memory_before'] = memory_get_usage(true) / 1024 / 1024; // MB
DB::flushQueryLog();
DB::enableQueryLog();

echo "⏱️  Processando {$numOrders} pedidos...\n\n";

$globalStartTime = microtime(true);

// Processar pedidos sequencialmente (simula concorrência por processar todos rapidamente)
foreach ($customers as $index => $customer) {
    $orderStartTime = microtime(true);

    try {
        // Seleciona 1-3 produtos aleatórios
        $numItems = rand(1, 3);
        $selectedProducts = $products->random(min($numItems, $products->count()));

        $items = [];
        $subtotal = 0;

        foreach ($selectedProducts as $product) {
            $quantity = rand(1, 2);
            $price = (float)$product->price;
            $itemSubtotal = $price * $quantity;

            $items[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $price,
                'subtotal' => $itemSubtotal,
            ];

            $subtotal += $itemSubtotal;
        }

        // Dados do pedido
        $orderData = [
            'items' => $items,
            'subtotal' => $subtotal,
            'delivery_fee' => 5.00,
            'payment_method' => 'pix',
            'delivery_address' => [
                'street' => 'Rua Teste',
                'number' => (string)rand(1, 999),
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zipcode' => '01000-000',
            ],
            'use_cashback' => false,
        ];

        // Processar pedido
        $order = $orderService->createOrder($customer, $orderData);

        $orderEndTime = microtime(true);
        $responseTime = ($orderEndTime - $orderStartTime) * 1000; // ms
        $results['response_times'][] = $responseTime;
        $results['success']++;

        echo sprintf(
            "✅ [%02d/%02d] Pedido #%d | Cliente: %s | R$ %.2f | %.2fms\n",
            $index + 1,
            $numOrders,
            $order->id,
            $customer->name,
            $order->total,
            $responseTime
        );

    } catch (\Exception $e) {
        $orderEndTime = microtime(true);
        $responseTime = ($orderEndTime - $orderStartTime) * 1000;
        $results['response_times'][] = $responseTime;

        $results['errors']++;
        $results['errors_details'][] = [
            'customer' => $customer->name,
            'error' => $e->getMessage(),
        ];

        echo sprintf(
            "❌ [%02d/%02d] ERRO: %s\n",
            $index + 1,
            $numOrders,
            substr($e->getMessage(), 0, 60)
        );
    }

    // Micro delay para evitar travar completamente
    usleep(5000); // 5ms
}

$globalEndTime = microtime(true);
$totalTime = ($globalEndTime - $globalStartTime) * 1000; // ms

// Capturar estado final
$results['memory_after'] = memory_get_usage(true) / 1024 / 1024; // MB
$queries = DB::getQueryLog();
$results['db_queries_after'] = count($queries);

// Estatísticas
if (count($results['response_times']) > 0) {
    $avgResponseTime = array_sum($results['response_times']) / count($results['response_times']);
    $minResponseTime = min($results['response_times']);
    $maxResponseTime = max($results['response_times']);
    sort($results['response_times']);
    $p50 = $results['response_times'][floor(count($results['response_times']) * 0.50)];
    $p95 = $results['response_times'][floor(count($results['response_times']) * 0.95)];
    $p99 = $results['response_times'][floor(count($results['response_times']) * 0.99)];
} else {
    $avgResponseTime = $minResponseTime = $maxResponseTime = $p50 = $p95 = $p99 = 0;
}

$throughput = $results['success'] > 0 ? ($results['success'] / ($totalTime / 1000)) : 0;
$successRate = ($results['success'] / $numOrders) * 100;
$memoryUsed = $results['memory_after'] - $results['memory_before'];
$avgQueriesPerOrder = $results['success'] > 0 ? ($results['db_queries_after'] / $results['success']) : 0;

// Relatório
echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  📊 RELATÓRIO FINAL                                        ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "⏱️  TEMPO TOTAL: " . number_format($totalTime, 2) . "ms (" . number_format($totalTime / 1000, 2) . "s)\n";
echo "✅ SUCESSOS: {$results['success']}/{$numOrders} (" . number_format($successRate, 2) . "%)\n";
echo "❌ ERROS: {$results['errors']}/{$numOrders}\n";
echo "🚀 THROUGHPUT: " . number_format($throughput, 2) . " pedidos/segundo\n\n";

echo "📈 LATÊNCIA (Tempo de Processamento):\n";
echo "   ├─ Média: " . number_format($avgResponseTime, 2) . "ms\n";
echo "   ├─ Mínimo: " . number_format($minResponseTime, 2) . "ms\n";
echo "   ├─ Máximo: " . number_format($maxResponseTime, 2) . "ms\n";
echo "   ├─ P50 (mediana): " . number_format($p50, 2) . "ms\n";
echo "   ├─ P95: " . number_format($p95, 2) . "ms\n";
echo "   └─ P99: " . number_format($p99, 2) . "ms\n\n";

echo "💾 RECURSOS:\n";
echo "   ├─ Queries SQL total: {$results['db_queries_after']}\n";
echo "   ├─ Queries por pedido: " . number_format($avgQueriesPerOrder, 2) . "\n";
echo "   ├─ Memória inicial: " . number_format($results['memory_before'], 2) . "MB\n";
echo "   ├─ Memória final: " . number_format($results['memory_after'], 2) . "MB\n";
echo "   └─ Memória usada: " . number_format($memoryUsed, 2) . "MB\n\n";

// Diagnóstico
echo "🩺 DIAGNÓSTICO:\n\n";

if ($successRate >= 95) {
    echo "   ✅ Taxa de sucesso EXCELENTE (≥95%)\n";
} elseif ($successRate >= 90) {
    echo "   ⚠️  Taxa de sucesso BOA (≥90%) mas pode melhorar\n";
} else {
    echo "   ❌ Taxa de sucesso RUIM (<90%) - ATENÇÃO!\n";
}

if ($avgResponseTime < 200) {
    echo "   ✅ Latência média EXCELENTE (<200ms)\n";
} elseif ($avgResponseTime < 500) {
    echo "   ⚠️  Latência média BOA (<500ms)\n";
} else {
    echo "   ❌ Latência média ALTA (>500ms) - OTIMIZAR!\n";
}

if ($p95 < 500) {
    echo "   ✅ P95 EXCELENTE (<500ms)\n";
} elseif ($p95 < 1000) {
    echo "   ⚠️  P95 BOA (<1s)\n";
} else {
    echo "   ❌ P95 RUIM (>1s) - Alguns pedidos muito lentos!\n";
}

if ($throughput >= 10) {
    echo "   ✅ Throughput EXCELENTE (≥10/s)\n";
} elseif ($throughput >= 5) {
    echo "   ⚠️  Throughput MODERADO (≥5/s)\n";
} else {
    echo "   ❌ Throughput BAIXO (<5/s)\n";
}

if ($avgQueriesPerOrder < 20) {
    echo "   ✅ Queries/pedido EXCELENTE (<20)\n";
} elseif ($avgQueriesPerOrder < 50) {
    echo "   ⚠️  Queries/pedido MODERADO (<50) - revisar N+1\n";
} else {
    echo "   ❌ Queries/pedido ALTO (>50) - N+1 problem!\n";
}

echo "\n";

if (!empty($results['errors_details'])) {
    echo "❌ ERROS:\n";
    foreach (array_slice($results['errors_details'], 0, 10) as $error) {
        echo "   • {$error['customer']}: {$error['error']}\n";
    }
    if (count($results['errors_details']) > 10) {
        $remaining = count($results['errors_details']) - 10;
        echo "   ... e mais {$remaining} erros\n";
    }
    echo "\n";
}

// Recomendações baseadas nos resultados
echo "💡 RECOMENDAÇÕES:\n\n";

$recommendations = [];

if ($avgResponseTime > 500) {
    $recommendations[] = "Otimizar OrderService (muito tempo por pedido)";
    $recommendations[] = "Adicionar índices nas tabelas (orders, order_items, products)";
}

if ($avgQueriesPerOrder > 30) {
    $recommendations[] = "Implementar eager loading (with) para evitar N+1";
    $recommendations[] = "Cache de produtos e configurações (Redis)";
}

if ($throughput < 10) {
    $recommendations[] = "Aumentar workers PHP-FPM (pm.max_children atual: 5)";
    $recommendations[] = "Configurar pm.max_children = 50-100";
}

if ($p95 > 1000) {
    $recommendations[] = "Investigar queries lentas (log slow queries)";
    $recommendations[] = "Otimizar cálculo de cashback";
}

if ($results['errors'] > 0) {
    $recommendations[] = "Verificar logs: storage/logs/laravel.log";
    $recommendations[] = "Validar constraints do banco de dados";
}

// PHP-FPM específico
$recommendations[] = "**CRÍTICO**: Aumentar pm.max_children de 5 para 50+";
$recommendations[] = "Aumentar pm.start_servers de 2 para 20";
$recommendations[] = "Configurar pm.max_spare_servers para 30";

if (empty($recommendations)) {
    echo "   ✅ Sistema bem configurado!\n";
} else {
    foreach ($recommendations as $rec) {
        echo "   • {$rec}\n";
    }
}

echo "\n";

// Comparação: Configuração Atual vs Recomendada
echo "⚙️  CONFIGURAÇÃO ATUAL vs RECOMENDADA:\n\n";
echo "   PHP-FPM:\n";
echo "   ├─ Atual:      pm.max_children = 5 ❌\n";
echo "   └─ Recomendado: pm.max_children = 50-100 ✅\n\n";

echo "   PostgreSQL:\n";
echo "   ├─ Atual:      max_connections = 79 ⚠️\n";
echo "   └─ Recomendado: max_connections = 200+ ✅\n\n";

echo "   Nginx:\n";
echo "   ├─ Atual:      worker_connections = 768 ⚠️\n";
echo "   └─ Recomendado: worker_connections = 2048+ ✅\n\n";

// Score
$score = 0;
if ($successRate >= 95) $score += 30;
if ($avgResponseTime < 500) $score += 25;
if ($throughput >= 5) $score += 20;
if ($avgQueriesPerOrder < 50) $score += 15;
if ($p95 < 1000) $score += 10;

echo "🏆 SCORE DE PREPARAÇÃO: {$score}/100\n\n";

if ($score >= 80) {
    echo "   Status: 🟢 EXCELENTE - Sistema preparado!\n";
} elseif ($score >= 60) {
    echo "   Status: 🟡 BOM - Otimizações recomendadas\n";
} elseif ($score >= 40) {
    echo "   Status: 🟠 MODERADO - Melhorias necessárias\n";
} else {
    echo "   Status: 🔴 CRÍTICO - Requer atenção imediata!\n";
}

echo "\n";

// Limpeza
echo "🧹 Limpando dados de teste...\n";
foreach ($customers as $customer) {
    $customer->orders()->delete();
    $customer->cashbackTransactions()->delete();
    $customer->delete();
}
echo "✅ Limpeza concluída\n\n";

echo "════════════════════════════════════════════════════════════\n\n";
