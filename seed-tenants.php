<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;

foreach (Tenant::all() as $tenant) {
    echo "🌱 Seeding {$tenant->name}...\n";
    
    $tenant->run(function () {
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\TenantSeeder', '--force' => true]);
        echo Artisan::output();
    });
    
    echo "✅ {$tenant->name} seeded!\n\n";
}

echo "🎉 All tenants seeded successfully!\n";
