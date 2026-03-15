#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use App\Models\Order;
use Illuminate\Support\Facades\Broadcast;

// Inicializar tenant
$tenant = Tenant::find('marmitariadagi');
tenancy()->initialize($tenant);

// Buscar pedido
$order = Order::latest()->first();

if (!$order) {
    echo "❌ Nenhum pedido encontrado!\n";
    exit(1);
}

echo "📦 Pedido: #{$order->order_number}\n";
echo "💰 Total: R$ " . number_format($order->total, 2, ',', '.') . "\n";
echo "🚀 Disparando broadcast DIRETO (sem fila)...\n";
echo "\n";

// Preparar dados manualmente (igual ao evento)
$data = [
    'order_id' => $order->id,
    'order_number' => $order->order_number,
    'customer' => [
        'name' => $order->customer_name,
    ],
    'totals' => [
        'total' => (float) $order->total,
    ],
    'items' => [],
    'delivery' => [
        'method' => 'delivery',
    ],
];

$channel = "restaurant.marmitariadagi";
$event = ".order.created";  // COM PONTO (Laravel Echo espera assim)

echo "📡 Canal: $channel\n";
echo "🎧 Evento: $event\n";
echo "📦 Dados: " . json_encode($data) . "\n";
echo "\n";

try {
    // Usar Broadcast facade diretamente
    $broadcaster = Broadcast::connection('reverb');

    echo "✅ Broadcaster obtido\n";
    echo "Tipo: " . get_class($broadcaster) . "\n";
    echo "\n";

    // Enviar evento diretamente
    $broadcaster->broadcast([$channel], $event, $data);

    echo "✅ Evento enviado com sucesso!\n";

} catch (\Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
}
