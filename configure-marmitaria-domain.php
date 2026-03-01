<?php

/**
 * Script para configurar o domínio da Marmitaria da Gi
 *
 * Uso: php configure-marmitaria-domain.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

echo "🔧 Configurando domínio para Marmitaria da Gi...\n\n";

// Verificar se o tenant existe
$tenant = Tenant::find('marmitaria-gi');

if (!$tenant) {
    echo "❌ Tenant 'marmitaria-gi' não encontrado!\n";
    echo "📝 Execute primeiro: php artisan db:seed --class=MarmitariaGiSeeder\n";
    exit(1);
}

echo "✅ Tenant encontrado: {$tenant->name}\n";

// Verificar se o domínio já existe
$domain = Domain::where('domain', 'marmitaria-gi.yumgo.com.br')->first();

if ($domain) {
    echo "⚠️  Domínio já existe!\n";
    echo "   - Domínio: {$domain->domain}\n";
    echo "   - Tenant: {$domain->tenant_id}\n";

    if ($domain->tenant_id !== 'marmitaria-gi') {
        echo "🔄 Atualizando tenant do domínio...\n";
        $domain->update(['tenant_id' => 'marmitaria-gi']);
        echo "✅ Domínio vinculado ao tenant correto!\n";
    }
} else {
    echo "➕ Criando domínio...\n";
    $domain = Domain::create([
        'domain' => 'marmitaria-gi.yumgo.com.br',
        'tenant_id' => 'marmitaria-gi',
    ]);
    echo "✅ Domínio criado com sucesso!\n";
}

echo "\n";
echo "🎉 CONFIGURAÇÃO CONCLUÍDA!\n\n";
echo "📱 Acesse: https://marmitaria-gi.yumgo.com.br\n";
echo "🍱 Você verá o catálogo de marmitas da Gi!\n\n";

// Mostrar todos os domínios do tenant
echo "📋 Domínios configurados para este tenant:\n";
foreach ($tenant->domains as $d) {
    echo "   - {$d->domain}\n";
}

echo "\n✨ Tudo pronto!\n";
