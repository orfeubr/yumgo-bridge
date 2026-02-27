<?php

// Simular webhook do Asaas para o pedido 20260226-7AF0CC
$webhookData = [
    'event' => 'PAYMENT_CONFIRMED',
    'payment' => [
        'id' => 'pay_1s1wo1vufp7esvea',
        'externalReference' => '144c5973-f985-4309-8f9a-c404dd11feae:20260226-7AF0CC',
        'value' => 36.00,
        'status' => 'CONFIRMED',
        'billingType' => 'PIX',
        'confirmedDate' => date('Y-m-d H:i:s'),
    ]
];

echo "=== SIMULANDO WEBHOOK ASAAS ===\n";
echo "Event: {$webhookData['event']}\n";
echo "Payment ID: {$webhookData['payment']['id']}\n";
echo "External Reference: {$webhookData['payment']['externalReference']}\n\n";

// Fazer requisição para o webhook
$ch = curl_init('https://yumgo.com.br/api/webhooks/asaas');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Para desenvolvimento

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "=== RESPOSTA DO WEBHOOK ===\n";
echo "HTTP Code: {$httpCode}\n";
echo "Response: {$response}\n\n";

if ($httpCode == 200) {
    echo "✅ Webhook processado com sucesso!\n";
    echo "🔄 Verifique se o status do pedido foi atualizado.\n";
} else {
    echo "❌ Erro ao processar webhook!\n";
    echo "📋 Verifique os logs: tail -f storage/logs/laravel.log\n";
}
