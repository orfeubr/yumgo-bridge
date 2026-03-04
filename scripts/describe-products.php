#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenant = \App\Models\Tenant::where('slug', 'marmitariadagi')->first();
tenancy()->initialize($tenant);

$columns = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'products'");

echo "\nColunas da tabela 'products':\n";
echo str_repeat("-", 60) . "\n";

foreach ($columns as $column) {
    echo "{$column->column_name} ({$column->data_type})\n";
}

echo "\nTotal produtos: " . \App\Models\Product::count() . "\n\n";
