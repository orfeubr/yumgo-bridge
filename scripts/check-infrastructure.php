#!/usr/bin/env php
<?php

/**
 * DIAGNÓSTICO DE INFRAESTRUTURA
 *
 * Verifica se o sistema está preparado para lidar com alta carga
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  🩺 DIAGNÓSTICO DE INFRAESTRUTURA                          ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$issues = [];
$warnings = [];
$recommendations = [];

// 1. PHP-FPM Configuration
echo "1️⃣  PHP-FPM Workers\n";
echo str_repeat("-", 60) . "\n";

$phpFpmConfig = shell_exec('grep -E "pm\.|pm " /etc/php/8.3/fpm/pool.d/www.conf 2>/dev/null || echo "Config not found"');

if (strpos($phpFpmConfig, 'Config not found') === false) {
    echo $phpFpmConfig . "\n";

    // Parse config
    preg_match('/pm\.max_children = (\d+)/', $phpFpmConfig, $maxChildren);
    preg_match('/pm\.start_servers = (\d+)/', $phpFpmConfig, $startServers);
    preg_match('/pm\.min_spare_servers = (\d+)/', $phpFpmConfig, $minSpare);
    preg_match('/pm\.max_spare_servers = (\d+)/', $phpFpmConfig, $maxSpare);

    $maxChildren = (int)($maxChildren[1] ?? 0);
    $startServers = (int)($startServers[1] ?? 0);

    if ($maxChildren < 50) {
        $issues[] = "❌ pm.max_children = {$maxChildren} (INSUFICIENTE para 50 pedidos simultâneos)";
        $recommendations[] = "Aumentar pm.max_children para pelo menos 50-100";
    } else {
        echo "✅ pm.max_children = {$maxChildren} (SUFICIENTE)\n";
    }

    if ($startServers < 20) {
        $warnings[] = "⚠️  pm.start_servers = {$startServers} (baixo)";
        $recommendations[] = "Aumentar pm.start_servers para 20-30";
    } else {
        echo "✅ pm.start_servers = {$startServers} (BOM)\n";
    }
} else {
    $issues[] = "❌ Não foi possível ler configuração PHP-FPM";
}

echo "\n";

// 2. PostgreSQL Connections
echo "2️⃣  PostgreSQL Pool de Conexões\n";
echo str_repeat("-", 60) . "\n";

try {
    $maxConnections = DB::selectOne("SHOW max_connections")->max_connections;
    $currentConnections = DB::selectOne("SELECT count(*) as count FROM pg_stat_activity")->count;

    echo "Max connections: {$maxConnections}\n";
    echo "Current connections: {$currentConnections}\n";
    echo "Available: " . ($maxConnections - $currentConnections) . "\n";

    if ($maxConnections < 100) {
        $warnings[] = "⚠️  max_connections = {$maxConnections} (pode ser insuficiente sob carga)";
        $recommendations[] = "Aumentar max_connections do PostgreSQL para 200+";
    } else {
        echo "✅ Max connections suficiente\n";
    }

    $usagePercent = ($currentConnections / $maxConnections) * 100;
    if ($usagePercent > 80) {
        $issues[] = "❌ Uso de conexões em {$usagePercent}% (ALTO!)";
    } elseif ($usagePercent > 60) {
        $warnings[] = "⚠️  Uso de conexões em {$usagePercent}% (moderado)";
    } else {
        echo "✅ Uso de conexões: {$usagePercent}% (BOM)\n";
    }
} catch (\Exception $e) {
    $issues[] = "❌ Erro ao verificar PostgreSQL: {$e->getMessage()}";
}

echo "\n";

// 3. Redis
echo "3️⃣  Redis\n";
echo str_repeat("-", 60) . "\n";

try {
    $redis = \Illuminate\Support\Facades\Redis::connection();
    $info = $redis->info();

    $maxMemory = $info['maxmemory'] ?? 0;
    $usedMemory = $info['used_memory'] ?? 0;
    $connectedClients = $info['connected_clients'] ?? 0;

    if ($maxMemory > 0) {
        $maxMemoryMb = round($maxMemory / 1024 / 1024, 2);
        $usedMemoryMb = round($usedMemory / 1024 / 1024, 2);
        $usagePercent = ($usedMemory / $maxMemory) * 100;

        echo "Max memory: {$maxMemoryMb}MB\n";
        echo "Used memory: {$usedMemoryMb}MB ({$usagePercent}%)\n";
        echo "Connected clients: {$connectedClients}\n";

        if ($maxMemoryMb < 512) {
            $warnings[] = "⚠️  Redis max_memory = {$maxMemoryMb}MB (baixo)";
            $recommendations[] = "Aumentar Redis maxmemory para 1GB+";
        } else {
            echo "✅ Redis memory configurado adequadamente\n";
        }
    } else {
        echo "⚠️  Redis sem limite de memória (maxmemory not set)\n";
        echo "Clients conectados: {$connectedClients}\n";
    }

    echo "✅ Redis acessível\n";
} catch (\Exception $e) {
    $issues[] = "❌ Erro ao conectar Redis: {$e->getMessage()}";
}

echo "\n";

// 4. Supervisor (Filas)
echo "4️⃣  Supervisor Queue Workers\n";
echo str_repeat("-", 60) . "\n";

$supervisorStatus = shell_exec('sudo supervisorctl status 2>/dev/null || echo "Supervisor not running"');

if (strpos($supervisorStatus, 'Supervisor not running') === false) {
    echo $supervisorStatus . "\n";

    // Conta workers
    $nfceWorkers = preg_match_all('/laravel-queue-nfce.*RUNNING/', $supervisorStatus);
    $defaultWorkers = preg_match_all('/laravel-queue-default.*RUNNING/', $supervisorStatus);

    echo "NFC-e workers: {$nfceWorkers}\n";
    echo "Default workers: {$defaultWorkers}\n";

    if ($nfceWorkers < 2) {
        $warnings[] = "⚠️  Apenas {$nfceWorkers} worker(s) NFC-e (recomendado: 2+)";
    } else {
        echo "✅ Workers NFC-e adequados\n";
    }

    if ($defaultWorkers < 4) {
        $warnings[] = "⚠️  Apenas {$defaultWorkers} worker(s) padrão (recomendado: 4+)";
    } else {
        echo "✅ Workers padrão adequados\n";
    }
} else {
    $issues[] = "❌ Supervisor não está rodando";
    $recommendations[] = "Iniciar Supervisor: sudo supervisorctl start all";
}

echo "\n";

// 5. Nginx
echo "5️⃣  Nginx Configuration\n";
echo str_repeat("-", 60) . "\n";

$nginxConfig = shell_exec('grep -E "worker_connections|worker_processes" /etc/nginx/nginx.conf 2>/dev/null || echo "Config not found"');

if (strpos($nginxConfig, 'Config not found') === false) {
    echo $nginxConfig . "\n";

    preg_match('/worker_processes\s+(\d+|auto)/', $nginxConfig, $workerProcesses);
    preg_match('/worker_connections\s+(\d+)/', $nginxConfig, $workerConnections);

    $processes = $workerProcesses[1] ?? 'unknown';
    $connections = (int)($workerConnections[1] ?? 0);

    if ($connections < 1024) {
        $warnings[] = "⚠️  worker_connections = {$connections} (baixo)";
        $recommendations[] = "Aumentar worker_connections para 2048+";
    } else {
        echo "✅ worker_connections = {$connections} (BOM)\n";
    }

    if ($processes === 'auto') {
        echo "✅ worker_processes = auto (IDEAL)\n";
    } elseif ((int)$processes < 2) {
        $warnings[] = "⚠️  worker_processes = {$processes} (baixo)";
    }
} else {
    $warnings[] = "⚠️  Não foi possível ler configuração Nginx";
}

echo "\n";

// 6. Sistema Operacional
echo "6️⃣  Sistema Operacional\n";
echo str_repeat("-", 60) . "\n";

$cpuCount = shell_exec('nproc');
$memTotal = shell_exec("free -m | grep Mem | awk '{print $2}'");
$memAvailable = shell_exec("free -m | grep Mem | awk '{print $7}'");
$loadAverage = sys_getloadavg();

echo "CPU cores: " . trim($cpuCount) . "\n";
echo "Memória total: " . trim($memTotal) . "MB\n";
echo "Memória disponível: " . trim($memAvailable) . "MB\n";
echo "Load average: " . implode(', ', $loadAverage) . "\n";

$cpuCount = (int)trim($cpuCount);
$memAvailable = (int)trim($memAvailable);

if ($cpuCount < 2) {
    $warnings[] = "⚠️  Apenas {$cpuCount} CPU core(s) - upgrade recomendado";
}

if ($memAvailable < 1024) {
    $warnings[] = "⚠️  Memória disponível baixa: {$memAvailable}MB";
    $recommendations[] = "Liberar memória ou aumentar RAM";
}

echo "\n";

// 7. Laravel Configuration
echo "7️⃣  Laravel Configuration\n";
echo str_repeat("-", 60) . "\n";

$queueConnection = config('queue.default');
$cacheDriver = config('cache.default');
$sessionDriver = config('session.driver');

echo "Queue driver: {$queueConnection}\n";
echo "Cache driver: {$cacheDriver}\n";
echo "Session driver: {$sessionDriver}\n";

if ($queueConnection !== 'redis') {
    $issues[] = "❌ Queue não está usando Redis (driver: {$queueConnection})";
    $recommendations[] = "Configurar QUEUE_CONNECTION=redis no .env";
} else {
    echo "✅ Queue usando Redis (ÓTIMO)\n";
}

if ($cacheDriver !== 'redis') {
    $warnings[] = "⚠️  Cache não está usando Redis (driver: {$cacheDriver})";
    $recommendations[] = "Configurar CACHE_DRIVER=redis no .env";
} else {
    echo "✅ Cache usando Redis (ÓTIMO)\n";
}

if ($sessionDriver !== 'redis') {
    $warnings[] = "⚠️  Session não está usando Redis (driver: {$sessionDriver})";
    $recommendations[] = "Configurar SESSION_DRIVER=redis no .env";
} else {
    echo "✅ Session usando Redis (ÓTIMO)\n";
}

echo "\n";

// 8. Resumo Final
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  📋 RESUMO DO DIAGNÓSTICO                                  ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

if (empty($issues) && empty($warnings)) {
    echo "🎉 SISTEMA PREPARADO PARA 50 PEDIDOS SIMULTÂNEOS!\n\n";
    echo "✅ Todos os componentes estão adequadamente configurados\n";
    echo "✅ Infraestrutura pronta para alta carga\n";
} else {
    if (!empty($issues)) {
        echo "❌ PROBLEMAS CRÍTICOS ENCONTRADOS:\n";
        foreach ($issues as $issue) {
            echo "   {$issue}\n";
        }
        echo "\n";
    }

    if (!empty($warnings)) {
        echo "⚠️  AVISOS (não bloqueiam, mas podem afetar performance):\n";
        foreach ($warnings as $warning) {
            echo "   {$warning}\n";
        }
        echo "\n";
    }

    if (!empty($recommendations)) {
        echo "💡 RECOMENDAÇÕES:\n";
        foreach ($recommendations as $recommendation) {
            echo "   • {$recommendation}\n";
        }
        echo "\n";
    }
}

// Score final
$totalChecks = 20;
$issuesCount = count($issues);
$warningsCount = count($warnings);
$score = max(0, $totalChecks - ($issuesCount * 2) - $warningsCount);
$scorePercent = ($score / $totalChecks) * 100;

echo "🏆 SCORE DE PREPARAÇÃO: {$scorePercent}%\n";

if ($scorePercent >= 90) {
    echo "   Status: 🟢 EXCELENTE - Sistema pronto para produção\n";
} elseif ($scorePercent >= 70) {
    echo "   Status: 🟡 BOM - Algumas otimizações recomendadas\n";
} elseif ($scorePercent >= 50) {
    echo "   Status: 🟠 MODERADO - Melhorias necessárias\n";
} else {
    echo "   Status: 🔴 CRÍTICO - Requer atenção imediata\n";
}

echo "\n════════════════════════════════════════════════════════════\n\n";
