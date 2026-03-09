<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // IDs do Pagar.me
            $table->string('pagarme_subscription_id')->nullable()->after('plan_id');
            $table->string('pagarme_customer_id')->nullable()->after('pagarme_subscription_id');

            // Controle de cobrança
            $table->timestamp('next_billing_date')->nullable()->after('trial_ends_at');
            $table->timestamp('last_payment_date')->nullable()->after('next_billing_date');

            // Status e metadados do Pagar.me
            $table->string('pagarme_status')->nullable()->after('status');
            $table->decimal('amount', 10, 2)->nullable()->after('pagarme_status');
            $table->string('payment_method')->nullable()->after('amount'); // credit_card, boleto

            // Índices para performance
            $table->index('pagarme_subscription_id');
            $table->index('pagarme_customer_id');
            $table->index('next_billing_date');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['pagarme_subscription_id']);
            $table->dropIndex(['pagarme_customer_id']);
            $table->dropIndex(['next_billing_date']);

            $table->dropColumn([
                'pagarme_subscription_id',
                'pagarme_customer_id',
                'next_billing_date',
                'last_payment_date',
                'pagarme_status',
                'amount',
                'payment_method',
            ]);
        });
    }
};
