#!/usr/bin/env php
<?php

/**
 * Gerar Token de Teste para Bridge
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenantSlug = $argv[1] ?? 'marmitariadagi';

try {
    $tenant = App\Models\Tenant::find($tenantSlug);

    if (!$tenant) {
        echo "❌ Tenant não encontrado: {$tenantSlug}\n";
        exit(1);
    }

    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "🔑 CREDENCIAIS PARA O BRIDGE\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "\n";
    echo "🏪 Restaurante: {$tenant->name}\n";
    echo "🆔 Restaurant ID: {$tenant->id}\n";
    echo "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "🔐 TOKEN DE TESTE (Use este no bridge):\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "\n";

    // Gerar token simples de teste
    $token = base64_encode("bridge-{$tenant->id}-" . time());

    echo "{$token}\n";
    echo "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "\n";
    echo "📋 INSTRUÇÕES:\n";
    echo "\n";
    echo "1. Abra o YumGo Bridge\n";
    echo "2. Cole o Restaurant ID: {$tenant->id}\n";
    echo "3. Cole o Token acima\n";
    echo "4. Clique em 'Conectar'\n";
    echo "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "\n";
    echo "🧪 TESTAR CONEXÃO:\n";
    echo "\n";
    echo "Após conectar, rode em outro terminal:\n";
    echo "php artisan test:print 10\n";
    echo "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

} catch (Exception $e) {
    echo "❌ ERRO: {$e->getMessage()}\n";
    exit(1);
}
