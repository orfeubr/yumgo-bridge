<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use Stancl\Tenancy\Facades\Tenancy;

// Tenant correto: Marmitaria da Gi
$tenant = Tenant::where('id', '144c5973-f985-4309-8f9a-c404dd11feae')->first();

if ($tenant) {
    echo "Usando tenant: {$tenant->id} ({$tenant->name})\n\n";
    Tenancy::initialize($tenant);
    
    $order = DB::table('orders')->where('order_number', '20260226-7AF0CC')->first();
    
    if ($order) {
        echo "=== ORDER STATUS ===\n";
        echo "Order Number: {$order->order_number}\n";
        echo "Status: {$order->status}\n";
        echo "Payment Status: {$order->payment_status}\n";
        echo "Created: {$order->created_at}\n";
        echo "Updated: {$order->updated_at}\n\n";
        
        $payment = DB::table('payments')->where('order_id', $order->id)->first();
        if ($payment) {
            echo "=== PAYMENT ===\n";
            echo "Status: {$payment->status}\n";
            echo "Transaction ID: {$payment->transaction_id}\n";
            echo "Paid At: {$payment->paid_at}\n";
        }
    } else {
        echo "Order não encontrado!\n";
    }
} else {
    echo "Tenant não encontrado!\n";
}
