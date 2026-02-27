<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Pagamento na Entrega - Configurações
            $table->json('delivery_payment_methods')->nullable()->after('payment_methods');

            // Opções individuais (para facilitar queries)
            $table->boolean('accept_cash_on_delivery')->default(true);
            $table->boolean('accept_card_on_delivery')->default(false);
            $table->boolean('accept_vr_on_delivery')->default(false);
            $table->boolean('accept_va_on_delivery')->default(false);
            $table->boolean('accept_sodexo_on_delivery')->default(false);
            $table->boolean('accept_alelo_on_delivery')->default(false);
            $table->boolean('accept_ticket_on_delivery')->default(false);

            // Configurações adicionais
            $table->decimal('min_change_value', 10, 2)->nullable()->comment('Valor máximo de troco');
            $table->text('delivery_payment_instructions')->nullable()->comment('Instruções para entregador');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_payment_methods',
                'accept_cash_on_delivery',
                'accept_card_on_delivery',
                'accept_vr_on_delivery',
                'accept_va_on_delivery',
                'accept_sodexo_on_delivery',
                'accept_alelo_on_delivery',
                'accept_ticket_on_delivery',
                'min_change_value',
                'delivery_payment_instructions',
            ]);
        });
    }
};
