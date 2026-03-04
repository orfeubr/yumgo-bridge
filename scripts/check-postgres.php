#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "📊 PostgreSQL Status:\n";
echo str_repeat("-", 60) . "\n";

try {
    $maxConn = DB::selectOne("SHOW max_connections")->max_connections;
    $currentConn = DB::selectOne("SELECT count(*) as count FROM pg_stat_activity")->count;

    echo "Max connections: {$maxConn}\n";
    echo "Current connections: {$currentConn}\n";
    echo "Available: " . ($maxConn - $currentConn) . "\n";

    // Verificar se é RDS
    $version = DB::selectOne("SELECT version()")->version;
    if (strpos($version, 'rds') !== false || strpos(config('database.connections.pgsql.host'), 'rds.amazonaws.com') !== false) {
        echo "\n⚠️  PostgreSQL rodando na AWS RDS\n";
        echo "   Para alterar max_connections:\n";
        echo "   1. AWS Console → RDS → Parameter Groups\n";
        echo "   2. Editar parameter group do banco\n";
        echo "   3. Alterar max_connections para 200\n";
        echo "   4. Aplicar mudanças (pode requerer reinício)\n";
    }

} catch (\Exception $e) {
    echo "❌ Erro: {$e->getMessage()}\n";
}

echo "\n";
