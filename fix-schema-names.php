<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== RENOMEANDO SCHEMAS PARA PADRÃO CORRETO ===\n\n";

// Schemas que precisam ser renomeados
$renames = [
    'tenant144c5973-f985-4309-8f9a-c404dd11feae' => 'tenant144c5973f9854309c404dd11feae',
    'tenantparker-pizzaria' => 'tenantparkerpizzaria',
];

foreach ($renames as $oldName => $newName) {
    echo "Renomeando: {$oldName} → {$newName}\n";
    
    try {
        // Verificar se o schema antigo existe
        $exists = DB::select("SELECT schema_name FROM information_schema.schemata WHERE schema_name = ?", [$oldName]);
        
        if (!$exists) {
            echo "  ⚠️ Schema {$oldName} não existe, pulando...\n\n";
            continue;
        }
        
        // Verificar se o novo já existe
        $newExists = DB::select("SELECT schema_name FROM information_schema.schemata WHERE schema_name = ?", [$newName]);
        
        if ($newExists) {
            echo "  ⚠️ Schema {$newName} já existe!\n";
            echo "  ❌ ABORTAR: Não podemos sobrescrever um schema existente.\n\n";
            continue;
        }
        
        // Renomear o schema
        DB::statement("ALTER SCHEMA \"{$oldName}\" RENAME TO \"{$newName}\"");
        
        echo "  ✅ Renomeado com sucesso!\n\n";
        
    } catch (\Exception $e) {
        echo "  ❌ Erro: " . $e->getMessage() . "\n\n";
    }
}

echo "=== SCHEMAS ATUAIS ===\n\n";
$schemas = DB::select("SELECT schema_name FROM information_schema.schemata WHERE schema_name LIKE 'tenant%' ORDER BY schema_name");

foreach ($schemas as $schema) {
    echo "📂 {$schema->schema_name}\n";
}

echo "\n✅ Concluído!\n";
