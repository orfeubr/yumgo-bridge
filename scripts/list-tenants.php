#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenants = \App\Models\Tenant::all(['id', 'slug', 'name']);

echo "\nTenants disponíveis:\n";
echo str_repeat("-", 60) . "\n";

foreach ($tenants as $tenant) {
    echo "ID: {$tenant->id} | Slug: {$tenant->slug} | Nome: {$tenant->name}\n";
}

echo "\nTotal: {$tenants->count()} tenant(s)\n\n";
