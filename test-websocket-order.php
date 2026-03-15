#!/usr/bin/env php
<?php

/**
 * Teste de Envio de Pedido via WebSocket
 *
 * Dispara um evento NewOrderEvent simulado para testar se o bridge recebe
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Log;
use App\Events\NewOrderEvent;

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Pegar tenant da linha de comando
$tenantSlug = $argv[1] ?? 'marmitaria-gi';

// Inicializar tenancy
try {
    $tenant = App\Models\Tenant::where('slug', $tenantSlug)->firstOrFail();

    echo "🔵 Tenant encontrado: {$tenant->name} (ID: {$tenant->id})\n";

    // Inicializar schema do tenant
    tenancy()->initialize($tenant);

    echo "✅ Tenancy inicializado\n";
    echo "📡 Canal WebSocket: restaurant.{$tenant->id}\n";
    echo "\n";

    // Buscar último pedido do banco
    $order = App\Models\Order::with(['items.product', 'customer'])
        ->where('payment_status', 'paid')
        ->latest()
        ->first();

    if (!$order) {
        echo "⚠️  Nenhum pedido pago encontrado no banco.\n";
        echo "   Vou criar um pedido de teste...\n\n";

        // Buscar primeiro cliente e produto para criar pedido
        $customer = App\Models\Customer::first();
        $product = App\Models\Product::first();

        if (!$customer || !$product) {
            echo "❌ Impossível criar pedido: sem cliente ou produto no banco.\n";
            exit(1);
        }

        // Criar pedido de teste
        $order = App\Models\Order::create([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'customer_email' => $customer->email,
            'delivery_method' => 'delivery',
            'delivery_address' => 'Rua Teste WebSocket, 123',
            'delivery_neighborhood' => 'Centro',
            'delivery_fee' => 5.00,
            'subtotal' => 50.00,
            'discount' => 0.00,
            'total' => 55.00,
            'payment_method' => 'pix',
            'payment_status' => 'paid',
            'status' => 'pending',
            'notes' => 'Pedido de TESTE WebSocket',
        ]);

        // Criar item do pedido
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name . ' (TESTE)',
            'quantity' => 2,
            'price' => 25.00,
            'subtotal' => 50.00,
        ]);

        $order->load(['items.product', 'customer']);
        echo "✅ Pedido de teste criado (#$order->id)\n\n";
    }

    echo "📦 Usando pedido:\n";
    echo "   - ID: {$order->id}\n";
    echo "   - Cliente: {$order->customer_name}\n";
    echo "   - Total: R$ " . number_format($order->total, 2, ',', '.') . "\n";
    echo "   - Items: {$order->items->count()}\n";
    echo "   - Status pagamento: {$order->payment_status}\n";
    echo "\n";

    // Disparar evento
    echo "🚀 Disparando evento NewOrderEvent...\n";
    event(new NewOrderEvent($order));

    echo "✅ Evento disparado!\n";
    echo "\n";
    echo "🔔 Verifique se o bridge recebeu o pedido.\n";
    echo "   Se não recebeu, verifique:\n";
    echo "   1. Bridge está conectado?\n";
    echo "   2. Logs do bridge (electron-bridge/logs/main.log)\n";
    echo "   3. Logs do Reverb (storage/logs/laravel.log)\n";
    echo "\n";

    // Aguardar 2 segundos para garantia
    sleep(2);

    echo "✅ Teste concluído!\n";

} catch (\Exception $e) {
    echo "❌ ERRO: {$e->getMessage()}\n";
    echo "Stack: {$e->getTraceAsString()}\n";
    exit(1);
}
