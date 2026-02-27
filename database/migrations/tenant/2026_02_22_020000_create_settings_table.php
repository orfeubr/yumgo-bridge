<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            // Identidade Visual
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->string('primary_color')->default('#EA1D2C');
            $table->string('secondary_color')->default('#333333');
            $table->string('accent_color')->default('#FFA500');

            // Informações do Estabelecimento
            $table->string('business_name')->nullable(); // Razão Social
            $table->string('trade_name')->nullable(); // Nome Fantasia
            $table->string('cnpj')->nullable(); // CNPJ ou CPF
            $table->string('state_registration')->nullable(); // Inscrição Estadual
            $table->string('municipal_registration')->nullable(); // Inscrição Municipal
            $table->string('segment')->nullable(); // Pizzaria, Marmitex, etc.

            // Informações de Contato
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('address_number')->nullable();
            $table->string('address_complement')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zipcode')->nullable();
            $table->string('instagram')->nullable();
            $table->string('facebook')->nullable();
            $table->string('website')->nullable();

            // Horários de Funcionamento (JSON)
            $table->json('business_hours')->nullable();
            $table->boolean('is_open_now')->default(true);
            $table->text('holiday_message')->nullable();

            // Configurações de Delivery
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('free_delivery_above', 10, 2)->default(0); // Entrega grátis acima de X
            $table->decimal('minimum_order_value', 10, 2)->default(0);
            $table->integer('delivery_radius_km')->default(10);
            $table->integer('min_delivery_time')->default(30); // minutos mínimo
            $table->integer('max_delivery_time')->default(60); // minutos máximo
            $table->boolean('allow_pickup')->default(true);
            $table->boolean('allow_delivery')->default(true);

            // Zonas/Regiões de Atendimento (JSON)
            $table->json('delivery_zones')->nullable(); // [{name: 'Centro', fee: 5, time: 30}, ...]
            $table->json('neighborhoods')->nullable(); // ['Centro', 'Jardins', 'Vila Nova', ...]

            // Tipos de Entrega
            $table->boolean('delivery_by_restaurant')->default(true); // Entregador próprio
            $table->boolean('delivery_by_customer')->default(false); // Cliente busca
            $table->boolean('delivery_by_motoboy')->default(false); // Motoboy parceiro

            // Métodos de Pagamento Aceitos
            $table->boolean('accept_pix')->default(true);
            $table->boolean('accept_credit_card')->default(true);
            $table->boolean('accept_debit_card')->default(true);
            $table->boolean('accept_cash')->default(true);
            $table->boolean('accept_voucher')->default(false);

            // Pagamento na Entrega
            $table->boolean('accept_payment_on_delivery')->default(true);
            $table->boolean('cash_on_delivery')->default(true); // Dinheiro na entrega
            $table->boolean('card_on_delivery')->default(true); // Maquininha na entrega
            $table->decimal('cash_change_for', 10, 2)->nullable(); // Troco para quanto?

            // Impressora Térmica
            $table->string('printer_type')->default('none'); // none, network, usb, bluetooth
            $table->string('printer_ip')->nullable();
            $table->integer('printer_port')->default(9100);
            $table->string('printer_model')->nullable();
            $table->integer('paper_width')->default(58); // mm
            $table->boolean('auto_print_orders')->default(false);
            $table->integer('print_copies')->default(1);

            // Gestão de Pedidos
            $table->boolean('auto_accept_orders')->default(false);
            $table->integer('preparation_time')->default(30); // minutos
            $table->boolean('require_customer_phone')->default(true);
            $table->boolean('require_customer_cpf')->default(false);
            $table->text('order_instructions')->nullable();

            // Numeração de Pedidos
            $table->string('order_number_prefix')->default('PED'); // Prefixo (PED, ORD, etc)
            $table->integer('order_number_start')->default(1); // Número inicial
            $table->integer('order_number_current')->default(1); // Número atual
            $table->integer('order_number_padding')->default(6); // Zeros à esquerda (ex: 000001)
            $table->boolean('reset_order_number_daily')->default(false); // Reiniciar diariamente

            // NFCe - Nota Fiscal de Consumidor Eletrônica
            $table->boolean('nfce_enabled')->default(false);
            $table->string('nfce_environment')->default('homologacao'); // homologacao, producao
            $table->string('nfce_certificate_path')->nullable(); // Caminho do certificado A1
            $table->string('nfce_certificate_password')->nullable(); // Senha do certificado
            $table->integer('nfce_series')->default(1); // Série da NFCe
            $table->integer('nfce_last_number')->default(0); // Último número emitido
            $table->string('nfce_csc')->nullable(); // Código de Segurança do Contribuinte
            $table->string('nfce_csc_id')->nullable(); // ID do CSC
            $table->decimal('nfce_tax_regime', 2, 0)->default(1); // Regime tributário (1=Simples)
            $table->boolean('nfce_auto_emit')->default(false); // Emitir automaticamente
            $table->text('nfce_additional_info')->nullable(); // Informações adicionais

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
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
