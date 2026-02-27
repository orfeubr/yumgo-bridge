<?php

// Simular webhook do Asaas para o pedido 20260226-CC51C8
$webhookData = [
    'event' => 'PAYMENT_CONFIRMED',
    'payment' => [
        'id' => 'pay_69sm489kqeqgyvqi',
        'externalReference' => '144c5973-f985-4309-8f9a-c404dd11feae:20260226-CC51C8',
        'value' => 45.00,
        'status' => 'CONFIRMED',
        'billingType' => 'PIX',
        'confirmedDate' => date('Y-m-d H:i:s'),
    ]
];

echo "=== SIMULANDO WEBHOOK ASAAS ===\n";
echo "Event: {$webhookData['event']}\n";
echo "Payment ID: {$webhookData['payment']['id']}\n";
echo "Order Number: 20260226-CC51C8\n\n";

$ch = curl_init('https://yumgo.com.br/api/webhooks/asaas');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "=== RESPOSTA DO WEBHOOK ===\n";
echo "HTTP Code: {$httpCode}\n";
echo "Response: {$response}\n\n";

if ($httpCode == 200) {
    echo "✅ Webhook processado!\n";
    sleep(1);
    
    // Verificar status atualizado
    require __DIR__.'/vendor/autoload.php';
    $app = require_once __DIR__.'/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    use App\Models\Tenant;
    use Stancl\Tenancy\Facades\Tenancy;
    
    $tenant = Tenant::where('id', '144c5973-f985-4309-8f9a-c404dd11feae')->first();
    if ($tenant) {
        Tenancy::initialize($tenant);
        $order = DB::table('orders')->where('order_number', '20260226-CC51C8')->first();
        
        echo "\n=== STATUS ATUALIZADO ===\n";
        echo "Status: {$order->status}\n";
        echo "Payment Status: {$order->payment_status}\n";
        echo "Updated At: {$order->updated_at}\n";
    }
} else {
    echo "❌ Erro ao processar webhook!\n";
}
