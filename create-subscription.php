#!/usr/bin/env php
<?php

/**
 * Script Helper: Criar Assinatura para Tenant
 *
 * Uso:
 *   php create-subscription.php TENANT_SLUG [PLAN_ID]
 *
 * Exemplos:
 *   php create-subscription.php marmitariadagi
 *   php create-subscription.php parker-pizzaria 2
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Argumentos
$tenantSlug = $argv[1] ?? null;
$planId = $argv[2] ?? null;

if (!$tenantSlug) {
    echo "❌ Erro: Informe o SLUG do tenant\n";
    echo "Uso: php create-subscription.php TENANT_SLUG [PLAN_ID]\n";
    exit(1);
}

// Buscar tenant
$tenant = \App\Models\Tenant::find($tenantSlug);

if (!$tenant) {
    echo "❌ Tenant '$tenantSlug' não encontrado!\n";
    echo "\n📋 Tenants disponíveis:\n";
    $tenants = \App\Models\Tenant::all();
    foreach ($tenants as $t) {
        echo "  - {$t->id} ({$t->name})\n";
    }
    exit(1);
}

// Buscar plano
if ($planId) {
    $plan = \App\Models\Plan::find($planId);
} else {
    // Pegar primeiro plano (geralmente Trial)
    $plan = \App\Models\Plan::first();
}

if (!$plan) {
    echo "❌ Plano não encontrado!\n";
    echo "\n📋 Planos disponíveis:\n";
    $plans = \App\Models\Plan::all();
    foreach ($plans as $p) {
        echo "  - ID {$p->id}: {$p->name} (R$ " . number_format($p->price_monthly, 2, ',', '.') . "/mês)\n";
    }
    exit(1);
}

// Verificar se já existe assinatura ativa
$existingActive = \App\Models\Subscription::where('tenant_id', $tenant->id)
    ->whereIn('status', ['active', 'trialing'])
    ->first();

if ($existingActive) {
    echo "⚠️  Já existe uma assinatura ativa para este tenant!\n";
    echo "Status: {$existingActive->status}\n";
    echo "Plano: {$existingActive->plan->name}\n";
    echo "Iniciou em: {$existingActive->starts_at}\n";
    echo "\nDeseja criar mesmo assim? (s/N): ";
    $confirm = trim(fgets(STDIN));
    if (strtolower($confirm) !== 's') {
        echo "❌ Cancelado.\n";
        exit(0);
    }
}

// Criar assinatura
try {
    $subscription = \App\Models\Subscription::create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'amount' => $plan->price_monthly,
        'starts_at' => now(),
        'ends_at' => null,
        'trial_ends_at' => $plan->trial_period_days > 0 ? now()->addDays($plan->trial_period_days) : null,
        'next_billing_date' => now()->addMonth(),
    ]);

    echo "✅ Assinatura criada com sucesso!\n\n";
    echo "📊 Detalhes:\n";
    echo "  ID: {$subscription->id}\n";
    echo "  Tenant: {$tenant->name} ({$tenant->id})\n";
    echo "  Plano: {$plan->name}\n";
    echo "  Valor: R$ " . number_format($subscription->amount, 2, ',', '.') . "/mês\n";
    echo "  Status: {$subscription->status}\n";
    echo "  Início: {$subscription->starts_at}\n";

    if ($subscription->trial_ends_at) {
        echo "  Trial até: {$subscription->trial_ends_at}\n";
    }

    echo "\n🎯 Acesse o painel:\n";
    echo "  https://{$tenant->id}.yumgo.com.br/painel\n";

} catch (\Exception $e) {
    echo "❌ Erro ao criar assinatura:\n";
    echo $e->getMessage() . "\n";
    exit(1);
}
