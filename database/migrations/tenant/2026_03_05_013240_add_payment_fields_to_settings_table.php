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
            // Adicionar colunas de habilitar/desabilitar métodos de pagamento
            $table->boolean('payment_pix_enabled')->default(true)->after('accept_pix');
            $table->boolean('payment_credit_card_enabled')->default(true)->after('accept_credit_card');
            $table->boolean('payment_debit_card_enabled')->default(true)->after('accept_debit_card');
            $table->boolean('payment_on_delivery_enabled')->default(true)->after('accept_payment_on_delivery');
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
            ]);
        });
    }
};
