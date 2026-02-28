<?php

/**
 * Script para configurar dados bancários de teste nos tenants existentes
 * Execute: php setup-bank-data-tenants.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;

echo "🏦 Configurando dados bancários de teste...\n\n";

$tenants = Tenant::all();

if ($tenants->isEmpty()) {
    echo "❌ Nenhum tenant encontrado!\n";
    exit(1);
}

foreach ($tenants as $tenant) {
    echo "🔄 Processando: {$tenant->name}\n";

    // Dados bancários de teste (válidos para Pagar.me sandbox)
    $tenant->update([
        'company_name' => $tenant->name . ' LTDA',
        'company_type' => 'company',
        'cpf_cnpj' => '11222333000181', // CNPJ de teste válido
        'mobile_phone' => '11999999999',
        'payment_gateway' => 'pagarme', // Ativar Pagar.me

        // Dados bancários
        'bank_code' => '341', // Itaú
        'bank_account_type' => 'checking',
        'bank_agency' => '0001',
        'bank_branch_digit' => '0',
        'bank_account' => rand(10000000, 99999999), // Conta aleatória
        'bank_account_digit' => rand(1, 9),
    ]);

    echo "  ✅ Dados bancários configurados!\n";
    echo "  📊 Banco: Itaú (341)\n";
    echo "  📊 Agência: 0001-0\n";
    echo "  📊 Conta: {$tenant->bank_account}-{$tenant->bank_account_digit}\n";
    echo "  📊 CNPJ: 11.222.333/0001-81\n";
    echo "\n";
}

echo "✅ Concluído! {$tenants->count()} tenant(s) configurado(s)\n";
echo "\nAgora execute: php artisan pagarme:create-recipients\n";
