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
        Schema::table('tenants', function (Blueprint $table) {
            // Adicionar campos Pagar.me
            $table->string('pagarme_recipient_id')->nullable()->after('asaas_account_id');
            $table->string('pagarme_api_key')->nullable()->after('pagarme_recipient_id');
            $table->text('pagarme_encryption_key')->nullable()->after('pagarme_api_key');
            $table->json('pagarme_split_rules')->nullable()->after('pagarme_encryption_key');
            $table->enum('payment_gateway', ['asaas', 'pagarme'])->default('pagarme')->after('pagarme_split_rules');

            // Adicionar apenas campos bancários que não existem
            // Campos já existentes: bank_account_type, bank_name, bank_agency, bank_account, bank_account_digit
            $table->string('bank_code')->nullable()->after('payment_gateway'); // Código do banco (001, 237, etc)
            $table->string('bank_branch_digit')->nullable()->after('bank_code'); // Dígito da agência
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'pagarme_recipient_id',
                'pagarme_api_key',
                'pagarme_encryption_key',
                'pagarme_split_rules',
                'payment_gateway',
                'bank_code',
                'bank_branch_digit',
            ]);
        });
    }
};
