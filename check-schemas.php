<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;

echo "=== TENANTS NO BANCO ===\n\n";

$tenants = Tenant::all();

foreach ($tenants as $tenant) {
    echo "ID: {$tenant->id}\n";
    echo "Name: {$tenant->name}\n";
    echo "Slug: " . ($tenant->slug ?? 'NULL') . "\n";
    echo "Status: {$tenant->status}\n";
    
    // Verificar schema no banco
    $schemaName = "tenant" . str_replace('-', '', $tenant->id);
    echo "Schema esperado: {$schemaName}\n";
    
    // Verificar se existe
    $exists = DB::select("SELECT schema_name FROM information_schema.schemata WHERE schema_name = ?", [$schemaName]);
    echo "Existe no banco: " . ($exists ? "✅ SIM" : "❌ NÃO") . "\n";
    
    echo "---\n\n";
}

// Listar TODOS os schemas tenant no banco
echo "=== SCHEMAS NO BANCO ===\n\n";
$schemas = DB::select("SELECT schema_name FROM information_schema.schemata WHERE schema_name LIKE 'tenant%' ORDER BY schema_name");

foreach ($schemas as $schema) {
    echo "📂 {$schema->schema_name}\n";
}
