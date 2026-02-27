<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "🏷️  Adicionando recursos aos produtos...\n\n";

$tenants = Tenant::all();

foreach ($tenants as $tenant) {
    echo "📦 {$tenant->name}... ";

    try {
        tenancy()->initialize($tenant);

        Schema::table('products', function (Blueprint $table) {
            // Tags (Vegano, Sem Glúten, Apimentado, etc.)
            if (!Schema::hasColumn('products', 'tags')) {
                $table->json('tags')->nullable()->after('is_featured');
            }

            // Gestão de Estoque
            if (!Schema::hasColumn('products', 'stock_enabled')) {
                $table->boolean('stock_enabled')->default(false)->after('tags');
                $table->integer('stock_quantity')->default(0)->after('stock_enabled');
                $table->integer('stock_min_alert')->default(5)->after('stock_quantity');
                $table->boolean('stock_alert_sent')->default(false)->after('stock_min_alert');
            }

            // QR Code
            if (!Schema::hasColumn('products', 'qr_code')) {
                $table->string('qr_code')->nullable()->after('stock_alert_sent');
            }
        });

        echo "✅\n";
        tenancy()->end();
    } catch (\Exception $e) {
        echo "❌ {$e->getMessage()}\n";
        tenancy()->end();
    }
}

echo "\n✨ Recursos adicionados a todos os produtos!\n";
echo "\n📋 Novos campos:\n";
echo "  - tags (JSON) - Ex: ['Vegano', 'Sem Glúten']\n";
echo "  - stock_enabled (boolean)\n";
echo "  - stock_quantity (int)\n";
echo "  - stock_min_alert (int)\n";
echo "  - stock_alert_sent (boolean)\n";
echo "  - qr_code (string)\n";
