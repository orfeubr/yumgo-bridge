<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

echo "🔄 Sincronizando customers: CENTRAL → TENANT\n\n";

// Buscar customer central
$centralCustomer = DB::table('customers')->first();

if (!$centralCustomer) {
    echo "❌ Nenhum customer central encontrado\n";
    exit;
}

echo "👤 Customer central encontrado:\n";
echo "   ID: {$centralCustomer->id}\n";
echo "   Nome: {$centralCustomer->name}\n";
echo "   Email: {$centralCustomer->email}\n";
echo "   Provider: {$centralCustomer->provider}\n\n";

// Para cada tenant
$tenants = Tenant::all();

foreach ($tenants as $tenant) {
    echo "📍 Tenant: {$tenant->name}\n";

    try {
        tenancy()->initialize($tenant);

        // Verificar se já existe customer com esse email no tenant
        $tenantCustomer = DB::table('customers')
            ->where('email', $centralCustomer->email)
            ->first();

        if ($tenantCustomer) {
            echo "   ✅ Customer já existe (ID: {$tenantCustomer->id})\n";
            echo "      Cashback: R$ {$tenantCustomer->cashback_balance}\n";
            echo "      Total pedidos: {$tenantCustomer->total_orders}\n";
        } else {
            // Criar customer no tenant
            echo "   🔨 Criando customer no tenant...\n";

            $tenantCustomerId = DB::table('customers')->insertGetId([
                'name' => $centralCustomer->name,
                'email' => $centralCustomer->email,
                'phone' => $centralCustomer->phone ?? '',
                'password' => $centralCustomer->password,
                'provider' => $centralCustomer->provider,
                'provider_id' => $centralCustomer->provider_id,
                'avatar' => $centralCustomer->avatar,
                'email_verified_at' => $centralCustomer->email_verified_at,
                'cpf' => $centralCustomer->cpf,
                'birth_date' => $centralCustomer->birth_date,
                'cashback_balance' => 0.00,
                'loyalty_tier' => 'bronze',
                'total_orders' => 0,
                'total_spent' => 0.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            echo "   ✅ Customer criado (ID: {$tenantCustomerId})\n";

            // Atualizar pedidos sem customer_id
            $ordersUpdated = DB::table('orders')
                ->whereNull('customer_id')
                ->update(['customer_id' => $tenantCustomerId]);

            if ($ordersUpdated > 0) {
                echo "   📦 {$ordersUpdated} pedidos vinculados ao customer\n";
            }
        }

        echo "\n";
        tenancy()->end();

    } catch (\Exception $e) {
        echo "   ❌ ERRO: " . $e->getMessage() . "\n\n";
        tenancy()->end();
    }
}

echo "🎉 Sincronização concluída!\n";
