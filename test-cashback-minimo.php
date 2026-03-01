<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use App\Models\Customer;
use App\Models\CashbackSettings;
use App\Services\CashbackService;

// Inicializar tenant
$tenant = Tenant::where('slug', 'marmitaria-gi')
    ->orWhere('slug', 'marmitariadagi')
    ->first();

if (!$tenant) {
    die("❌ Tenant não encontrado!\n");
}

tenancy()->initialize($tenant);

echo "🏪 Tenant: {$tenant->name}\n";
echo str_repeat("=", 60) . "\n\n";

// Buscar/criar configuração de cashback
$settings = CashbackSettings::first();
if (!$settings) {
    $settings = CashbackSettings::create([
        'bronze_percentage' => 5,
        'min_order_value_to_earn' => 20,
        'min_cashback_to_use' => 5.00, // ⭐ Mínimo R$ 5 para usar
        'is_active' => true,
    ]);
}

echo "⚙️ Configuração Cashback:\n";
echo "   - Mínimo para usar: R$ " . number_format($settings->min_cashback_to_use, 2, ',', '.') . "\n\n";

// Buscar customer
$customer = Customer::first();
if (!$customer) {
    die("❌ Customer não encontrado!\n");
}

$cashbackService = new CashbackService();

echo "👤 Cliente: {$customer->name}\n";
echo "💰 Saldo atual: R$ " . number_format($customer->cashback_balance, 2, ',', '.') . "\n\n";

echo str_repeat("=", 60) . "\n";
echo "TESTES\n";
echo str_repeat("=", 60) . "\n\n";

// TESTE 1: Cliente tem R$ 2,40 (MENOR que mínimo R$ 5)
$customer->cashback_balance = 2.40;
$customer->save();

echo "📍 TESTE 1: Saldo R$ 2,40 (menor que mínimo R$ 5,00)\n";
echo "   Tentando usar: R$ 2,40 (todo saldo)\n";
$result = $cashbackService->useCashback($customer, 2.40);
echo "   Resultado: " . ($result ? "✅ PERMITIU" : "❌ BLOQUEOU") . "\n";
if ($result) {
    echo "   Novo saldo: R$ " . number_format($customer->cashback_balance, 2, ',', '.') . "\n";
}
echo "\n";

// Restaurar saldo
$customer->cashback_balance = 2.40;
$customer->save();

// TESTE 2: Cliente tem R$ 10 (MAIOR que mínimo)
$customer->cashback_balance = 10.00;
$customer->save();

echo "📍 TESTE 2: Saldo R$ 10,00 (maior que mínimo R$ 5,00)\n";
echo "   Tentando usar: R$ 2,00 (abaixo do mínimo)\n";
$result = $cashbackService->useCashback($customer, 2.00);
echo "   Resultado: " . ($result ? "✅ PERMITIU" : "❌ BLOQUEOU") . " (esperado: BLOQUEAR)\n\n";

// TESTE 3: Cliente tem R$ 10 e usa R$ 5
$customer->cashback_balance = 10.00;
$customer->save();

echo "📍 TESTE 3: Saldo R$ 10,00 (maior que mínimo R$ 5,00)\n";
echo "   Tentando usar: R$ 5,00 (igual ao mínimo)\n";
$result = $cashbackService->useCashback($customer, 5.00);
echo "   Resultado: " . ($result ? "✅ PERMITIU" : "❌ BLOQUEOU") . "\n";
if ($result) {
    echo "   Novo saldo: R$ " . number_format($customer->cashback_balance, 2, ',', '.') . "\n";
}
echo "\n";

// TESTE 4: Cliente tem R$ 0,50 (muito abaixo do mínimo)
$customer->cashback_balance = 0.50;
$customer->save();

echo "📍 TESTE 4: Saldo R$ 0,50 (muito abaixo do mínimo R$ 5,00)\n";
echo "   Tentando usar: R$ 0,50 (todo saldo)\n";
$result = $cashbackService->useCashback($customer, 0.50);
echo "   Resultado: " . ($result ? "✅ PERMITIU" : "❌ BLOQUEOU") . "\n";
if ($result) {
    echo "   Novo saldo: R$ " . number_format($customer->cashback_balance, 2, ',', '.') . "\n";
}
echo "\n";

echo str_repeat("=", 60) . "\n";
echo "✅ TESTES CONCLUÍDOS\n";
echo str_repeat("=", 60) . "\n";
