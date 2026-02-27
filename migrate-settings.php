<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "🔧 Criando tabela settings para todos os tenants...\n\n";

$tenants = Tenant::all();

foreach ($tenants as $tenant) {
    echo "📦 {$tenant->name} ({$tenant->id})... ";

    try {
        tenancy()->initialize($tenant);

        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();

                // Identidade Visual
                $table->string('logo')->nullable();
                $table->string('banner')->nullable();
                $table->string('primary_color')->default('#EA1D2C');
                $table->string('secondary_color')->default('#333333');
                $table->string('accent_color')->default('#FFA500');

                // Informações de Contato
                $table->string('phone')->nullable();
                $table->string('whatsapp')->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->string('instagram')->nullable();
                $table->string('facebook')->nullable();

                // Horários de Funcionamento (JSON)
                $table->json('business_hours')->nullable();
                $table->boolean('is_open_now')->default(true);
                $table->text('holiday_message')->nullable();

                // Configurações de Delivery
                $table->decimal('delivery_fee', 10, 2)->default(0);
                $table->decimal('minimum_order_value', 10, 2)->default(0);
                $table->integer('delivery_radius_km')->default(10);
                $table->integer('estimated_delivery_time')->default(45);
                $table->boolean('allow_pickup')->default(true);
                $table->boolean('allow_delivery')->default(true);

                // Métodos de Pagamento Aceitos
                $table->boolean('accept_pix')->default(true);
                $table->boolean('accept_credit_card')->default(true);
                $table->boolean('accept_debit_card')->default(true);
                $table->boolean('accept_cash')->default(true);
                $table->boolean('accept_voucher')->default(false);

                // Impressora Térmica
                $table->string('printer_type')->default('none');
                $table->string('printer_ip')->nullable();
                $table->integer('printer_port')->default(9100);
                $table->string('printer_model')->nullable();
                $table->integer('paper_width')->default(58);
                $table->boolean('auto_print_orders')->default(false);
                $table->integer('print_copies')->default(1);

                // Gestão de Pedidos
                $table->boolean('auto_accept_orders')->default(false);
                $table->integer('preparation_time')->default(30);
                $table->boolean('require_customer_phone')->default(true);
                $table->boolean('require_customer_cpf')->default(false);
                $table->text('order_instructions')->nullable();

                // Notificações
                $table->boolean('notify_email_new_order')->default(true);
                $table->boolean('notify_sms_new_order')->default(false);
                $table->boolean('notify_whatsapp_new_order')->default(false);
                $table->string('notification_email')->nullable();
                $table->string('notification_phone')->nullable();

                // Políticas
                $table->text('terms_of_service')->nullable();
                $table->text('privacy_policy')->nullable();
                $table->text('return_policy')->nullable();

                // SEO
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->text('meta_keywords')->nullable();

                // Recursos Ativados
                $table->boolean('enable_reviews')->default(true);
                $table->boolean('enable_loyalty_program')->default(true);
                $table->boolean('enable_coupons')->default(true);
                $table->boolean('enable_scheduled_orders')->default(false);

                $table->timestamps();
            });

            echo "✅ Criado\n";
        } else {
            echo "⏭️  Já existe\n";
        }

        tenancy()->end();
    } catch (\Exception $e) {
        echo "❌ Erro: {$e->getMessage()}\n";
        tenancy()->end();
    }
}

echo "\n✨ Concluído!\n";
