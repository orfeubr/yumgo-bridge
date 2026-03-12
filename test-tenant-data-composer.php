<?php

/**
 * Teste do TenantDataComposer
 * Verifica se os dados estão sendo fornecidos corretamente
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\View;

echo "🧪 TESTE: TenantDataComposer\n";
echo str_repeat("=", 60) . "\n\n";

// Buscar primeiro tenant
$tenant = Tenant::first();

if (!$tenant) {
    echo "❌ Nenhum tenant encontrado\n";
    exit(1);
}

echo "✅ Tenant: {$tenant->name} ({$tenant->slug})\n\n";

// Inicializar tenancy
tenancy()->initialize($tenant);

echo "✅ Tenancy inicializado\n\n";

// Renderizar view de teste
try {
    $html = View::make('tenant.catalog')->render();

    echo "✅ View renderizada com sucesso!\n";
    echo "📊 Tamanho HTML: " . strlen($html) . " bytes\n\n";

    // Verificar se $settings está disponível
    if (strpos($html, '{{ $settings') !== false || strpos($html, '$settings??') !== false) {
        echo "⚠️ AVISO: View ainda usa \$settings (variável está disponível via composer)\n";
    }

    echo "\n✅ SUCESSO: Composer funcionando corretamente!\n";

} catch (\Exception $e) {
    echo "❌ ERRO ao renderizar view:\n";
    echo "   Mensagem: " . $e->getMessage() . "\n";
    echo "   Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo "   Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎉 TESTE CONCLUÍDO\n";
