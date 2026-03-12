<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Stancl\Tenancy\Database\Models\Tenant;
use Illuminate\Support\Facades\DB;

echo "🔧 Marcando migration problemática como executada em todos os tenants...\n\n";

$migrationName = '2026_02_24_001104_update_tenant_tables_for_central_customers';
$tenants = Tenant::all();
$count = 0;

foreach ($tenants as $tenant) {
    try {
        tenancy()->initialize($tenant);

        // Verificar se já existe
        $exists = DB::table('migrations')
            ->where('migration', $migrationName)
            ->exists();

        if (!$exists) {
            DB::table('migrations')->insert([
                'migration' => $migrationName,
                'batch' => 1,
            ]);
            echo "✅ Tenant {$tenant->id}: Migration marcada como executada\n";
            $count++;
        } else {
            echo "⏭️  Tenant {$tenant->id}: Já estava marcada\n";
        }

        try {
            tenancy()->end();
        } catch (\Exception $e2) {
            // Ignorar erro ao finalizar tenancy
        }
    } catch (\Exception $e) {
        echo "❌ Tenant {$tenant->id}: Erro - " . $e->getMessage() . "\n";
        try {
            tenancy()->end();
        } catch (\Exception $e2) {
            // Ignorar erro ao finalizar tenancy
        }
    }
}

echo "\n✅ Concluído! $count tenants atualizados.\n";
