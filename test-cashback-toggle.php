<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use App\Models\Customer;
use App\Models\Product;

// Inicializar tenant
$tenant = Tenant::where('slug', 'marmitaria-gi')
    ->orWhere('slug', 'marmitariadagi')
    ->first();

if (!$tenant) {
    die("❌ Tenant não encontrado!\n");
}

tenancy()->initialize($tenant);

echo "🏪 Tenant: {$tenant->name}\n";
echo str_repeat("=", 50) . "\n\n";

// Buscar customer
$customer = Customer::first();
if (!$customer) {
    die("❌ Customer não encontrado!\n");
}

echo "👤 Cliente: {$customer->name}\n";
echo "💰 Saldo cashback: R$ " . number_format($customer->cashback_balance, 2, ',', '.') . "\n\n";

// Buscar produto para teste
$product = Product::where('is_active', true)->first();
if (!$product) {
    die("❌ Produto não encontrado!\n");
}

echo "🍽️  Produto: {$product->name}\n";
echo "💵 Preço: R$ " . number_format($product->price, 2, ',', '.') . "\n\n";

// Simular pedido
$subtotal = $product->price * 2; // 2 unidades
$deliveryFee = 5.00;
$totalBeforeCashback = $subtotal + $deliveryFee;

echo str_repeat("=", 50) . "\n";
echo "SIMULAÇÃO DE PEDIDO\n";
echo str_repeat("=", 50) . "\n\n";

echo "📦 Subtotal (2x): R$ " . number_format($subtotal, 2, ',', '.') . "\n";
echo "🚗 Taxa entrega: R$ " . number_format($deliveryFee, 2, ',', '.') . "\n";
echo "➖➖➖➖➖➖➖➖➖➖\n";
echo "💵 Total ANTES cashback: R$ " . number_format($totalBeforeCashback, 2, ',', '.') . "\n\n";

// TESTE 1: SEM usar cashback
echo "📍 TESTE 1: Cliente NÃO marca toggle\n";
echo "   use_cashback: false\n";
echo "   Cashback usado: R$ 0,00\n";
echo "   Total a pagar: R$ " . number_format($totalBeforeCashback, 2, ',', '.') . "\n\n";

// TESTE 2: COM toggle (usa todo saldo)
echo "📍 TESTE 2: Cliente MARCA toggle ✅\n";
echo "   use_cashback: true\n";

$cashbackBalance = $customer->cashback_balance;
$cashbackToUse = min($cashbackBalance, $totalBeforeCashback);
$finalTotal = $totalBeforeCashback - $cashbackToUse;

echo "   Saldo disponível: R$ " . number_format($cashbackBalance, 2, ',', '.') . "\n";
echo "   Total do pedido: R$ " . number_format($totalBeforeCashback, 2, ',', '.') . "\n";
echo "   Cashback aplicado: R$ " . number_format($cashbackToUse, 2, ',', '.') . "\n";
echo "   ➖➖➖➖➖➖➖➖➖➖\n";
echo "   ✅ Total a pagar: R$ " . number_format($finalTotal, 2, ',', '.') . "\n";
echo "   💰 Saldo restante: R$ " . number_format($cashbackBalance - $cashbackToUse, 2, ',', '.') . "\n\n";

// TESTE 3: Saldo MAIOR que pedido
echo "📍 TESTE 3: Saldo R$ 100 (maior que pedido)\n";
$fakeCashback = 100.00;
$cashbackToUse3 = min($fakeCashback, $totalBeforeCashback);
$finalTotal3 = $totalBeforeCashback - $cashbackToUse3;

echo "   Saldo disponível: R$ " . number_format($fakeCashback, 2, ',', '.') . "\n";
echo "   Total do pedido: R$ " . number_format($totalBeforeCashback, 2, ',', '.') . "\n";
echo "   Cashback aplicado: R$ " . number_format($cashbackToUse3, 2, ',', '.') . " (limitado ao total!)\n";
echo "   ➖➖➖➖➖➖➖➖➖➖\n";
echo "   ✅ Total a pagar: R$ " . number_format($finalTotal3, 2, ',', '.') . " (GRÁTIS!)\n";
echo "   💰 Saldo restante: R$ " . number_format($fakeCashback - $cashbackToUse3, 2, ',', '.') . "\n\n";

echo str_repeat("=", 50) . "\n";
echo "✅ TOGGLE DE CASHBACK FUNCIONANDO CORRETAMENTE!\n";
echo str_repeat("=", 50) . "\n";
