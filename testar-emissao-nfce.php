<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n🧪 === TESTE DE EMISSÃO DE NFC-e ===\n\n";

// 1. Encontrar tenant
$tenant = \App\Models\Tenant::where('slug', 'marmitariadagi')->first();

if (!$tenant) {
    echo "❌ Tenant não encontrado\n";
    exit(1);
}

echo "1️⃣ Inicializando tenancy: {$tenant->name}\n";
tenancy()->initialize($tenant);

// 2. Buscar um customer
$customer = \App\Models\Customer::first();

if (!$customer) {
    echo "❌ Nenhum customer encontrado. Criando um de teste...\n";
    $customer = \App\Models\Customer::create([
        'name' => 'Cliente Teste NFC-e',
        'email' => 'teste-nfce@example.com',
        'phone' => '31999999999',
        'password' => bcrypt('teste123'),
    ]);
}

echo "   Customer: {$customer->name} (ID: {$customer->id})\n\n";

// 3. Buscar produtos
$products = \App\Models\Product::whereNotNull('ncm')
    ->whereNotNull('cfop')
    ->limit(3)
    ->get();

if ($products->count() === 0) {
    echo "❌ Nenhum produto com NCM/CFOP encontrado\n";
    echo "   Execute: php classificar-produtos.php\n";
    exit(1);
}

echo "2️⃣ Criando pedido de teste...\n";

// 4. Criar pedido
$orderData = [
    'customer_id' => $customer->id,
    'order_number' => 'NFC-' . now()->format('YmdHis'),
    'status' => 'pending',
    'payment_status' => 'pending',
    'payment_method' => 'pix',
    'delivery_method' => 'delivery',
    'subtotal' => 0,
    'delivery_fee' => 5.00,
    'total' => 0,
    'delivery_address' => 'Rua Teste, 123',
    'delivery_neighborhood' => 'Centro',
    'delivery_city' => 'Belo Horizonte',
    'delivery_state' => 'MG',
    'delivery_zipcode' => '30000-000',
];

$order = \App\Models\Order::create($orderData);

echo "   Pedido criado: #{$order->order_number}\n";
echo "   Status: {$order->payment_status}\n\n";

// 5. Adicionar items ao pedido
echo "3️⃣ Adicionando items ao pedido...\n";
$subtotal = 0;

foreach ($products as $product) {
    $quantity = rand(1, 2);
    $price = $product->price;
    $total = $price * $quantity;

    \App\Models\OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'quantity' => $quantity,
        'unit_price' => $price,
        'subtotal' => $total,
        'notes' => null,
    ]);

    $subtotal += $total;

    echo sprintf(
        "   ✅ %dx %s (R$ %.2f cada) = R$ %.2f\n",
        $quantity,
        $product->name,
        $price,
        $total
    );
}

// 6. Atualizar totais
$total = $subtotal + $orderData['delivery_fee'];
$order->update([
    'subtotal' => $subtotal,
    'total' => $total,
]);

echo sprintf("\n   Subtotal: R$ %.2f\n", $subtotal);
echo sprintf("   Taxa entrega: R$ %.2f\n", $orderData['delivery_fee']);
echo sprintf("   TOTAL: R$ %.2f\n\n", $total);

echo "4️⃣ Aguardando 2 segundos antes de marcar como pago...\n";
sleep(2);

// 7. Simular pagamento (dispara emissão de NFC-e)
echo "\n🔥 SIMULANDO PAGAMENTO (dispara Observer)...\n\n";

$order->update([
    'payment_status' => 'paid',
    'status' => 'confirmed',
]);

echo "✅ Pagamento confirmado!\n";
echo "   payment_status: 'paid'\n";
echo "   OrderFiscalObserver deve ter detectado a mudança...\n\n";

echo "5️⃣ Aguardando 15 segundos para processamento...\n";
echo "   (Job tem delay de 5s + processamento)\n\n";

for ($i = 15; $i > 0; $i--) {
    echo "   ⏳ {$i} segundos...\n";
    sleep(1);
}

echo "\n6️⃣ Verificando se NFC-e foi emitida...\n\n";

$order->refresh();

$fiscalNote = \App\Models\FiscalNote::where('order_id', $order->id)->first();

if ($fiscalNote) {
    echo "🎉 NFC-e EMITIDA COM SUCESSO!\n\n";
    echo str_repeat('━', 70) . "\n";
    echo "  Chave de Acesso: {$fiscalNote->nfce_key}\n";
    echo "  Série: {$fiscalNote->serie}\n";
    echo "  Número: {$fiscalNote->numero}\n";
    echo "  Status: {$fiscalNote->status}\n";
    echo "  Emitida em: " . $fiscalNote->emission_date->format('d/m/Y H:i:s') . "\n";

    if ($fiscalNote->xml_content) {
        echo "  XML: ✅ Armazenado\n";
    }

    if ($fiscalNote->xml_rejected_reason) {
        echo "\n  ⚠️  Motivo de Rejeição:\n";
        echo "  {$fiscalNote->xml_rejected_reason}\n";
    }

    echo str_repeat('━', 70) . "\n";
} else {
    echo "⚠️  NFC-e ainda não foi emitida.\n\n";
    echo "Possíveis motivos:\n";
    echo "  1. Job ainda está processando (aguarde mais)\n";
    echo "  2. Job falhou (veja logs)\n";
    echo "  3. Workers não estão rodando\n\n";
    echo "Como verificar:\n";
    echo "  - Logs: tail -f storage/logs/laravel.log | grep 'NFC-e'\n";
    echo "  - Jobs falhados: php artisan queue:failed\n";
    echo "  - Workers: sudo supervisorctl status\n\n";
}

echo "\n📝 ID do Pedido: {$order->id}\n";
echo "   Para acompanhar: /painel/orders/{$order->id}\n\n";
