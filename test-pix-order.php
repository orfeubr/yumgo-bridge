<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenant = App\Models\Tenant::where('id', '144c5973-f985-4309-8f9a-c404dd11feae')->first();
tenancy()->initialize($tenant);

$customer = App\Models\Customer::first();
$product = App\Models\Product::first();

if (!$customer || !$product) {
    echo "❌ Faltam customer ou product\n";
    exit(1);
}

$orderService = app(App\Services\OrderService::class);

try {
    $order = $orderService->createOrder($customer, [
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
                'price' => $product->price,
                'subtotal' => $product->price * 2,
            ]
        ],
        'delivery_address' => 'Rua Teste, 123',
        'delivery_city' => 'São Paulo',
        'delivery_neighborhood' => 'Centro',
        'delivery_fee' => 5.00,
        'payment_method' => 'pix',
        'notes' => 'Pedido teste com PIX via Asaas',
    ]);

    echo "✅ Pedido criado com PIX!\n";
    echo "Order ID: {$order->order_number}\n";
    echo "Total: R$ " . number_format($order->total, 2, ',', '.') . "\n";
    echo "Payment Method: {$order->payment_method}\n";

    if ($order->payment) {
        echo "Payment ID: {$order->payment->id}\n";
        echo "Payment Status: {$order->payment->status}\n";
        echo "Gateway: {$order->payment->gateway}\n";

        if ($order->payment->pix_qr_code) {
            echo "✅ QR Code PIX gerado!\n";
            echo "QR Code (primeiros 100 chars): " . substr($order->payment->pix_qr_code, 0, 100) . "...\n";
        } else {
            echo "⚠️ QR Code não foi gerado\n";
        }

        if ($order->payment->transaction_id) {
            echo "Transaction ID Asaas: {$order->payment->transaction_id}\n";
        }
    } else {
        echo "⚠️ Payment record não foi criado\n";
    }

} catch (\Exception $e) {
    echo "❌ Erro: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}:{$e->getLine()}\n";
}
