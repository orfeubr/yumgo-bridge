<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔧 FIXING ALL TENANTS WITH MISSING CUSTOMERS TABLE OR SCHEMA\n";
echo "================================================================\n\n";

$tenants = \App\Models\Tenant::all();

$fixed = 0;
$alreadyOk = 0;
$errors = 0;

foreach ($tenants as $tenant) {
    echo "🏪 Processing: {$tenant->name} ({$tenant->id})\n";

    $schemaName = 'tenant' . $tenant->id;

    // Check if schema exists
    $schemaExists = DB::select("SELECT schema_name FROM information_schema.schemata WHERE schema_name = ?", [$schemaName]);

    if (empty($schemaExists)) {
        echo "  📦 Creating schema...\n";
        try {
            DB::statement("CREATE SCHEMA IF NOT EXISTS \"{$schemaName}\"");
            echo "  ✅ Schema created\n";
        } catch (\Exception $e) {
            echo "  ❌ Error creating schema: " . $e->getMessage() . "\n";
            $errors++;
            continue;
        }
    }

    // Initialize tenancy
    tenancy()->initialize($tenant);

    // Check if customers table exists
    $customersTable = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = ? AND tablename = 'customers'", [$schemaName]);

    if (empty($customersTable)) {
        echo "  🔨 Creating customers table...\n";

        try {
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

            echo "  ✅ Customers table created\n";

            // Run all migrations
            echo "  🚀 Running all tenant migrations...\n";
            Artisan::call('migrate', [
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);

            echo "  ✅ Migrations completed\n";
            $fixed++;

        } catch (\Exception $e) {
            echo "  ❌ Error: " . $e->getMessage() . "\n";
            $errors++;
        }
    } else {
        echo "  ✅ Already OK\n";
        $alreadyOk++;
    }

    tenancy()->end();
    echo "\n";
}

echo "================================================================\n";
echo "📊 SUMMARY\n";
echo "  ✅ Already OK: {$alreadyOk}\n";
echo "  🔧 Fixed: {$fixed}\n";
echo "  ❌ Errors: {$errors}\n";
echo "\n✅ Done!\n";
