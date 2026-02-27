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
            // Métodos de pagamento online (integrados com Asaas)
            $table->boolean('payment_pix_enabled')->default(true);
            $table->boolean('payment_credit_card_enabled')->default(true);
            $table->boolean('payment_debit_card_enabled')->default(true);

            // Pagamento na entrega (apenas sinalização)
            $table->boolean('payment_on_delivery_enabled')->default(true);

            // Opções disponíveis para pagamento na entrega (JSON)
            // {cash: true, alelo: false, sodexo: false, vr: false, ticket: false}
            $table->json('payment_on_delivery_options')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'payment_pix_enabled',
                'payment_credit_card_enabled',
                'payment_debit_card_enabled',
                'payment_on_delivery_enabled',
                'payment_on_delivery_options',
            ]);
        });
    }
};
