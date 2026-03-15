#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenants = App\Models\Tenant::select('id', 'slug', 'name')->get();

echo "📋 Tenants disponíveis:\n\n";

foreach ($tenants as $tenant) {
    echo "  - ID: {$tenant->id}\n";
    echo "    Slug: {$tenant->slug}\n";
    echo "    Nome: {$tenant->name}\n";
    echo "\n";
}

echo "Total: {$tenants->count()} tenant(s)\n";
