<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenant = \App\Models\Tenant::where('id', 'a48efe45-872d-403e-a522-2cf445b1229b')->first();

if (!$tenant) {
    echo "❌ Tenant não encontrado\n";
    exit(1);
}

echo "✅ Tenant: {$tenant->name}\n";

// Initialize tenancy BEFORE creating table
tenancy()->initialize($tenant);

echo "🔄 Criando tabela customers...\n";

try {
    // Create customers table in the correct schema
    DB::statement("
        CREATE TABLE IF NOT EXISTS customers (
            id BIGSERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            phone VARCHAR(255) UNIQUE NOT NULL,
            cpf VARCHAR(255) UNIQUE,
            birth_date DATE,
            password VARCHAR(255) NOT NULL,
            cashback_balance DECIMAL(10, 2) DEFAULT 0.00,
            loyalty_tier VARCHAR(255) DEFAULT 'bronze' CHECK (loyalty_tier IN ('bronze', 'silver', 'gold', 'platinum')),
            total_orders INTEGER DEFAULT 0,
            total_spent DECIMAL(10, 2) DEFAULT 0.00,
            address_street VARCHAR(255),
            address_number VARCHAR(255),
            address_complement VARCHAR(255),
            address_neighborhood VARCHAR(255),
            address_city VARCHAR(255),
            address_state VARCHAR(255),
            address_zipcode VARCHAR(255),
            is_active BOOLEAN DEFAULT true,
            email_verified_at TIMESTAMP,
            remember_token VARCHAR(100),
            created_at TIMESTAMP,
            updated_at TIMESTAMP,
            deleted_at TIMESTAMP
        )
    ");

    echo "✅ Customers table created\n";

    // Mark migration as run
    $exists = DB::table('migrations')->where('migration', '2026_02_21_003637_create_customers_table')->exists();
    if (!$exists) {
        DB::table('migrations')->insert([
            'migration' => '2026_02_21_003637_create_customers_table',
            'batch' => 1,
        ]);
        echo "✅ Migration marked as run\n";
    }

    // Now run index migration
    echo "🚀 Running index migration...\n";
    Artisan::call('migrate', [
        '--path' => 'database/migrations/tenant/2026_03_09_023323_add_performance_indexes_to_tenant_tables.php',
        '--force' => true,
    ]);

    echo Artisan::output();
    echo "✅ Done!\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

tenancy()->end();
