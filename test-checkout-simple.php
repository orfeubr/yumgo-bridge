<?php
// Teste direto da API sem passar pelo Nginx

$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/api/v1/orders';
$_SERVER['HTTP_HOST'] = 'marmitaria-gi.yumgo.com.br';
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer 2|YVdmgXKiHNF2LVqvMRhzO8B9PlJc3eWoDaQs6tZy1f9b4081';
$_SERVER['CONTENT_TYPE'] = 'application/json';

$_POST = [];
$GLOBALS['HTTP_RAW_POST_DATA'] = json_encode([
    'items' => [[
        'product_id' => 1,
        'quantity' => 1,
        'price' => 16.90,
        'subtotal' => 16.90
    ]],
    'delivery_address' => 'Rua Teste, 123',
    'delivery_city' => 'São Paulo',
    'delivery_neighborhood' => 'Centro',
    'delivery_fee' => 5.00,
    'payment_method' => 'cash',
    'notes' => 'Teste'
]);

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create(
    '/api/v1/orders',
    'POST',
    [],
    [],
    [],
    $_SERVER,
    json_encode([
        'items' => [[
            'product_id' => 1,
            'quantity' => 1,
            'price' => 16.90,
            'subtotal' => 16.90
        ]],
        'delivery_address' => 'Rua Teste, 123',
        'delivery_city' => 'São Paulo',
        'delivery_neighborhood' => 'Centro',
        'delivery_fee' => 5.00,
        'payment_method' => 'cash',
    ])
);

try {
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Response: " . $response->getContent() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
