<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;

echo "=== PADRONIZANDO TENANTS PARA USAR SLUGS ===\n\n";

// Tenant com UUID - vamos mudar para usar o slug
$marmitaria = Tenant::find('144c5973-f985-4309-8f9a-c404dd11feae');

if ($marmitaria) {
    echo "Tenant encontrado:\n";
    echo "ID atual: {$marmitaria->id}\n";
    echo "Name: {$marmitaria->name}\n";
    echo "Slug: {$marmitaria->slug}\n\n";
    
    // Criar novo tenant com slug
    $newSlug = 'marmitaria-gi';
    
    echo "Criando novo tenant com slug: {$newSlug}\n";
    
    try {
        DB::beginTransaction();
        
        // 1. Criar novo registro de tenant
        DB::table('tenants')->insert([
            'id' => $newSlug,
            'name' => $marmitaria->name,
            'slug' => $newSlug,
            'email' => $marmitaria->email,
            'phone' => $marmitaria->phone,
            'address' => $marmitaria->address,
            'city' => $marmitaria->city,
            'state' => $marmitaria->state,
            'zipcode' => $marmitaria->zipcode,
            'logo' => $marmitaria->logo,
            'description' => $marmitaria->description,
            'status' => $marmitaria->status,
            'plan_id' => $marmitaria->plan_id,
            'asaas_account_id' => $marmitaria->asaas_account_id,
            'asaas_status' => $marmitaria->asaas_status,
            'created_at' => $marmitaria->created_at,
            'updated_at' => now(),
        ]);
        
        echo "✅ Novo tenant criado\n";
        
        // 2. Atualizar domains
        DB::table('domains')
            ->where('tenant_id', $marmitaria->id)
            ->update(['tenant_id' => $newSlug]);
        
        echo "✅ Domains atualizados\n";
        
        // 3. Renomear schema
        $oldSchema = 'tenant144c5973f9854309c404dd11feae';
        $newSchema = 'tenantmarmitariagi';
        
        DB::statement("ALTER SCHEMA \"{$oldSchema}\" RENAME TO \"{$newSchema}\"");
        echo "✅ Schema renomeado: {$oldSchema} → {$newSchema}\n";
        
        // 4. Deletar tenant antigo
        DB::table('tenants')->where('id', $marmitaria->id)->delete();
        echo "✅ Tenant antigo removido\n";
        
        DB::commit();
        
        echo "\n🎉 Padronização concluída!\n\n";
        
    } catch (\Exception $e) {
        DB::rollBack();
        echo "\n❌ Erro: " . $e->getMessage() . "\n";
    }
}

echo "=== TENANTS ATUAIS ===\n\n";
$tenants = Tenant::all();
foreach ($tenants as $t) {
    echo "ID: {$t->id}\n";
    echo "Name: {$t->name}\n";
    echo "Schema: tenant" . str_replace('-', '', $t->id) . "\n\n";
}

echo "=== SCHEMAS NO BANCO ===\n\n";
$schemas = DB::select("SELECT schema_name FROM information_schema.schemata WHERE schema_name LIKE 'tenant%' ORDER BY schema_name");
foreach ($schemas as $schema) {
    echo "📂 {$schema->schema_name}\n";
}
