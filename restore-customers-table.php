<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "🔧 Recriando tabela customers nos schemas tenant...\n\n";

$tenants = Tenant::all();

foreach ($tenants as $tenant) {
    echo "📍 Tenant: {$tenant->name} ({$tenant->slug})\n";

    try {
        tenancy()->initialize($tenant);

        // Verificar se a tabela já existe
        if (Schema::hasTable('customers')) {
            echo "   ✅ Tabela customers já existe - pulando\n\n";
            tenancy()->end();
            continue;
        }

        echo "   🔨 Criando tabela customers...\n";

        // Recriar a tabela customers (mesma estrutura da migration original)
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable(); // Nullable para permitir login só com WhatsApp
            $table->string('phone')->unique();
            $table->string('cpf')->unique()->nullable();
            $table->date('birth_date')->nullable();
            $table->string('password');

            // Auth Social (Google, WhatsApp)
            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable();
            $table->string('avatar')->nullable();

            // Verificação de código
            $table->string('verification_code', 6)->nullable();
            $table->timestamp('verification_code_expires_at')->nullable();

            // Cashback & Loyalty (ISOLADO POR TENANT!)
            $table->decimal('cashback_balance', 10, 2)->default(0.00);
            $table->enum('loyalty_tier', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze');
            $table->integer('total_orders')->default(0);
            $table->decimal('total_spent', 10, 2)->default(0.00);

            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('email');
            $table->index('phone');
            $table->index('cpf');
        });

        echo "   ✅ Tabela customers criada com sucesso!\n";

        // Verificar quantas orders existem sem customers
        $ordersCount = \DB::table('orders')->count();
        echo "   📊 {$ordersCount} pedidos existentes (customer_id será mantido)\n\n";

        tenancy()->end();

    } catch (\Exception $e) {
        echo "   ❌ ERRO: " . $e->getMessage() . "\n\n";
        tenancy()->end();
    }
}

echo "🎉 Concluído!\n\n";

echo "📋 Resumo:\n";
foreach (Tenant::all() as $tenant) {
    tenancy()->initialize($tenant);
    $hasCustomers = Schema::hasTable('customers');
    $status = $hasCustomers ? '✅' : '❌';
    echo "{$status} {$tenant->name}: " . ($hasCustomers ? 'OK' : 'FALTANDO') . "\n";
    tenancy()->end();
}
