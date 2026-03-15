#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Events\NewOrderEvent;

// Inicializar tenant
$tenant = Tenant::find('marmitariadagi');
tenancy()->initialize($tenant);

// Buscar cliente e produto
$customer = Customer::first();
$product = Product::first();

if (!$customer || !$product) {
    echo "❌ Faltam dados (customer ou product)\n";
    exit(1);
}

// Criar pedido real
$order = new Order();
$order->customer_id = $customer->id;
$order->customer_name = $customer->name;
$order->customer_phone = $customer->phone;
$order->customer_email = $customer->email;
$order->subtotal = 50.00;
$order->delivery_fee = 5.00;
$order->discount = 0;
$order->total = 55.00;
$order->status = 'pending';
$order->payment_status = 'paid';
$order->payment_method = 'pix';
$order->notes = 'Pedido de teste REAL via WebSocket';
$order->save();

// Criar item
$item = new OrderItem();
$item->order_id = $order->id;
$item->product_id = $product->id;
$item->product_name = $product->name;
$item->quantity = 2;
$item->price = 25.00;
$item->subtotal = 50.00;
$item->save();

echo "✅ Pedido criado: #{$order->order_number}\n";
echo "💰 Total: R$ " . number_format($order->total, 2, ',', '.') . "\n";
echo "🍕 Produto: {$product->name} (x2)\n";
echo "👤 Cliente: {$customer->name}\n";
echo "\n";
echo "🚀 Disparando evento...\n";

// Disparar evento
event(new NewOrderEvent($order));

echo "✅ Evento disparado!\n";
echo "📡 Canal: restaurant.marmitariadagi\n";
echo "🎧 Evento: .order.created\n";
