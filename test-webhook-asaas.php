#!/usr/bin/env php
<?php

/**
 * Script para testar webhook do Asaas manualmente
 *
 * Uso:
 * php test-webhook-asaas.php <payment_id> <order_number>
 *
 * Exemplo:
 * php test-webhook-asaas.php pay_123456 20260226-3CF56E
 */

$paymentId = $argv[1] ?? null;
$orderNumber = $argv[2] ?? null;

if (!$paymentId || !$orderNumber) {
    echo "❌ Uso: php test-webhook-asaas.php <payment_id> <order_number>\n";
    echo "Exemplo: php test-webhook-asaas.php pay_123456 20260226-3CF56E\n";
    exit(1);
}

echo "🧪 Testando webhook Asaas\n";
echo "Payment ID: $paymentId\n";
echo "Order Number: $orderNumber\n";
echo str_repeat("-", 50) . "\n\n";

// Buscar tenant_id pelo order_number
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Buscar em qual tenant está esse pedido
echo "🔍 Buscando tenant do pedido $orderNumber...\n";

$tenants = App\Models\Tenant::where('status', 'active')->get();
$foundTenant = null;
$foundOrder = null;

foreach ($tenants as $tenant) {
    tenancy()->initialize($tenant);

    $order = App\Models\Order::where('order_number', $orderNumber)->first();

    if ($order) {
        $foundTenant = $tenant;
        $foundOrder = $order;
        echo "✅ Tenant encontrado: {$tenant->id} ({$tenant->name})\n";
        echo "✅ Order encontrado: #{$order->id} - Status: {$order->status} - Payment Status: {$order->payment_status}\n";
        break;
    }

    tenancy()->end();
}

if (!$foundTenant || !$foundOrder) {
    echo "❌ Pedido não encontrado em nenhum tenant!\n";
    exit(1);
}

echo "\n📦 Simulando payload do webhook Asaas...\n";

$webhookPayload = [
    'event' => 'PAYMENT_CONFIRMED',
    'payment' => [
        'id' => $paymentId,
        'customer' => 'cus_000005163076',
        'billingType' => 'PIX',
        'value' => (float) $foundOrder->total,
        'netValue' => (float) $foundOrder->total - 0.99,
        'status' => 'CONFIRMED',
        'dueDate' => date('Y-m-d'),
        'paymentDate' => date('Y-m-d'),
        'clientPaymentDate' => date('Y-m-d H:i:s'),
        'externalReference' => "{$foundTenant->id}:{$foundOrder->id}",
        'originalValue' => null,
        'interestValue' => null,
        'description' => "Pedido #{$foundOrder->order_number}",
        'invoiceUrl' => 'https://www.asaas.com/i/fake',
        'invoiceNumber' => '12345',
        'transactionReceiptUrl' => 'https://www.asaas.com/comprovantes/fake',
        'nossoNumero' => '000000000000000000',
        'confirmedDate' => date('Y-m-d'),
    ],
];

echo json_encode($webhookPayload, JSON_PRETTY_PRINT) . "\n\n";

echo "🌐 Enviando POST para https://yumgo.com.br/api/webhooks/asaas...\n";

$ch = curl_init('https://yumgo.com.br/api/webhooks/asaas');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookPayload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Para teste local
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Erro cURL: $error\n";
    exit(1);
}

echo "📥 Resposta HTTP: $httpCode\n";
echo "📄 Body: $response\n\n";

// Verificar se order foi atualizado
echo "🔍 Verificando se order foi atualizado...\n";

tenancy()->initialize($foundTenant);
$updatedOrder = App\Models\Order::find($foundOrder->id);

echo "Status antes: {$foundOrder->status} | Payment Status antes: {$foundOrder->payment_status}\n";
echo "Status depois: {$updatedOrder->status} | Payment Status depois: {$updatedOrder->payment_status}\n";

if ($updatedOrder->payment_status === 'paid' && $updatedOrder->status === 'confirmed') {
    echo "✅ SUCESSO! Order atualizado corretamente!\n";
} else {
    echo "❌ FALHA! Order NÃO foi atualizado.\n";
}

tenancy()->end();
