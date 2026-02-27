<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "🔧 Atualizando tabela settings com novos campos...\n\n";

$tenants = Tenant::all();

foreach ($tenants as $tenant) {
    echo "📦 {$tenant->name} ({$tenant->id})... ";

    try {
        tenancy()->initialize($tenant);

        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                // Verificar e adicionar campos do estabelecimento
                if (!Schema::hasColumn('settings', 'business_name')) {
                    $table->string('business_name')->nullable()->after('accent_color');
                    $table->string('trade_name')->nullable()->after('business_name');
                    $table->string('cnpj')->nullable()->after('trade_name');
                    $table->string('state_registration')->nullable()->after('cnpj');
                    $table->string('municipal_registration')->nullable()->after('state_registration');
                    $table->string('segment')->nullable()->after('municipal_registration');
                }

                // Verificar e adicionar campos de endereço expandidos
                if (!Schema::hasColumn('settings', 'address_number')) {
                    $table->string('address_number')->nullable()->after('address');
                    $table->string('address_complement')->nullable()->after('address_number');
                    $table->string('neighborhood')->nullable()->after('address_complement');
                    $table->string('city')->nullable()->after('neighborhood');
                    $table->string('state')->nullable()->after('city');
                    $table->string('zipcode')->nullable()->after('state');
                    $table->string('website')->nullable()->after('facebook');
                }

                // Verificar e adicionar campos de delivery expandidos
                if (!Schema::hasColumn('settings', 'free_delivery_above')) {
                    $table->decimal('free_delivery_above', 10, 2)->default(0)->after('delivery_fee');
                    $table->integer('min_delivery_time')->default(30)->after('delivery_radius_km');
                    $table->integer('max_delivery_time')->default(60)->after('min_delivery_time');
                    $table->json('delivery_zones')->nullable()->after('allow_delivery');
                    $table->json('neighborhoods')->nullable()->after('delivery_zones');
                    $table->boolean('delivery_by_restaurant')->default(true)->after('neighborhoods');
                    $table->boolean('delivery_by_customer')->default(false)->after('delivery_by_restaurant');
                    $table->boolean('delivery_by_motoboy')->default(false)->after('delivery_by_customer');
                }

                // Verificar e adicionar campos de pagamento na entrega
                if (!Schema::hasColumn('settings', 'accept_payment_on_delivery')) {
                    $table->boolean('accept_payment_on_delivery')->default(true)->after('accept_voucher');
                    $table->boolean('cash_on_delivery')->default(true)->after('accept_payment_on_delivery');
                    $table->boolean('card_on_delivery')->default(true)->after('cash_on_delivery');
                    $table->decimal('cash_change_for', 10, 2)->nullable()->after('card_on_delivery');
                }

                // Verificar e adicionar campos de numeração de pedidos
                if (!Schema::hasColumn('settings', 'order_number_prefix')) {
                    $table->string('order_number_prefix')->default('PED')->after('order_instructions');
                    $table->integer('order_number_start')->default(1)->after('order_number_prefix');
                    $table->integer('order_number_current')->default(1)->after('order_number_start');
                    $table->integer('order_number_padding')->default(6)->after('order_number_current');
                    $table->boolean('reset_order_number_daily')->default(false)->after('order_number_padding');
                }

                // Verificar e adicionar campos de NFCe
                if (!Schema::hasColumn('settings', 'nfce_enabled')) {
                    $table->boolean('nfce_enabled')->default(false)->after('reset_order_number_daily');
                    $table->string('nfce_environment')->default('homologacao')->after('nfce_enabled');
                    $table->string('nfce_certificate_path')->nullable()->after('nfce_environment');
                    $table->string('nfce_certificate_password')->nullable()->after('nfce_certificate_path');
                    $table->integer('nfce_series')->default(1)->after('nfce_certificate_password');
                    $table->integer('nfce_last_number')->default(0)->after('nfce_series');
                    $table->string('nfce_csc')->nullable()->after('nfce_last_number');
                    $table->string('nfce_csc_id')->nullable()->after('nfce_csc');
                    $table->decimal('nfce_tax_regime', 2, 0)->default(1)->after('nfce_csc_id');
                    $table->boolean('nfce_auto_emit')->default(false)->after('nfce_tax_regime');
                    $table->text('nfce_additional_info')->nullable()->after('nfce_auto_emit');
                }

                // Remover estimated_delivery_time antigo se existir
                if (Schema::hasColumn('settings', 'estimated_delivery_time')) {
                    $table->dropColumn('estimated_delivery_time');
                }
            });

            echo "✅ Atualizado\n";
        } else {
            echo "⚠️  Tabela não existe\n";
        }

        tenancy()->end();
    } catch (\Exception $e) {
        echo "❌ Erro: {$e->getMessage()}\n";
        tenancy()->end();
    }
}

echo "\n✨ Concluído!\n";
