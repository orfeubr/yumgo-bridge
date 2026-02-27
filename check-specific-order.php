<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use Stancl\Tenancy\Facades\Tenancy;

// Tenant: Marmitaria da Gi
$tenant = Tenant::where('id', '144c5973-f985-4309-8f9a-c404dd11feae')->first();

if ($tenant) {
    Tenancy::initialize($tenant);
    
    // Buscar pedido
    $order = DB::table('orders')->where('order_number', '20260226-CC51C8')->first();
    
    if ($order) {
        echo "=== ORDER #" . $order->id . " - " . $order->order_number . " ===\n\n";
        echo "📋 COLUNAS DA TABELA ORDERS:\n";
        echo "id: " . $order->id . "\n";
        echo "order_number: " . $order->order_number . "\n";
        echo "customer_id: " . $order->customer_id . "\n";
        echo "status: " . $order->status . "\n";
        echo "payment_status: " . $order->payment_status . "\n";
        echo "payment_method: " . $order->payment_method . "\n";
        echo "subtotal: R$ " . number_format($order->subtotal, 2, ',', '.') . "\n";
        echo "delivery_fee: R$ " . number_format($order->delivery_fee, 2, ',', '.') . "\n";
        echo "cashback_used: R$ " . number_format($order->cashback_used, 2, ',', '.') . "\n";
        echo "cashback_earned: R$ " . number_format($order->cashback_earned, 2, ',', '.') . "\n";
        echo "total: R$ " . number_format($order->total, 2, ',', '.') . "\n";
        echo "delivery_address: " . $order->delivery_address . "\n";
        echo "delivery_city: " . ($order->delivery_city ?? 'NULL') . "\n";
        echo "delivery_neighborhood: " . ($order->delivery_neighborhood ?? 'NULL') . "\n";
        echo "notes: " . ($order->notes ?? 'NULL') . "\n";
        echo "created_at: " . $order->created_at . "\n";
        echo "updated_at: " . $order->updated_at . "\n\n";
        
        $payment = DB::table('payments')->where('order_id', $order->id)->first();
        if ($payment) {
            echo "💳 COLUNAS DA TABELA PAYMENTS:\n";
            echo "id: " . $payment->id . "\n";
            echo "order_id: " . $payment->order_id . "\n";
            echo "gateway: " . $payment->gateway . "\n";
            echo "transaction_id: " . $payment->transaction_id . "\n";
            echo "status: " . $payment->status . "\n";
            echo "amount: R$ " . number_format($payment->amount, 2, ',', '.') . "\n";
            echo "pix_qr_code: " . (strlen($payment->pix_qr_code ?? '') > 50 ? substr($payment->pix_qr_code, 0, 50) . '...' : ($payment->pix_qr_code ?? 'NULL')) . "\n";
            echo "pix_qr_code_base64: " . (strlen($payment->pix_qr_code_base64 ?? '') > 50 ? 'SIM (truncado)' : 'NULL') . "\n";
            echo "paid_at: " . ($payment->paid_at ?? 'NULL') . "\n";
            echo "created_at: " . $payment->created_at . "\n";
            echo "updated_at: " . $payment->updated_at . "\n";
        } else {
            echo "❌ Nenhum payment encontrado para este pedido!\n";
        }
    } else {
        echo "❌ Order 20260226-CC51C8 não encontrado!\n";
    }
}
