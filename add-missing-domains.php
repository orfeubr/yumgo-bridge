<?php

/**
 * Script para adicionar domínios para todos os tenants que não têm
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

echo "🔧 Adicionando domínios para tenants sem domínio...\n\n";

$tenants = Tenant::all();
$added = 0;
$skipped = 0;

foreach ($tenants as $tenant) {
    // Verificar se já tem domínio
    if ($tenant->domains()->count() > 0) {
        echo "⏭️  {$tenant->name}: já tem domínio (" . $tenant->domains->first()->domain . ")\n";
        $skipped++;
        continue;
    }

    // Criar domínio usando o slug
    $domain = $tenant->slug . '.yumgo.com.br';

    try {
        Domain::create([
            'domain' => $domain,
            'tenant_id' => $tenant->id,
        ]);
        echo "✅ {$tenant->name}: {$domain}\n";
        $added++;
    } catch (\Exception $e) {
        echo "❌ {$tenant->name}: Erro - {$e->getMessage()}\n";
    }
}

echo "\n";
echo "📊 Resumo:\n";
echo "   - Domínios adicionados: {$added}\n";
echo "   - Já existiam: {$skipped}\n";
echo "\n";

echo "🌐 Domínios configurados:\n";
foreach (Tenant::with('domains')->get() as $tenant) {
    foreach ($tenant->domains as $domain) {
        echo "   - https://{$domain->domain} → {$tenant->name}\n";
    }
}

echo "\n✨ Concluído!\n";
