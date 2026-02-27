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
            // Dados da empresa (phone e asaas_account_id já existem)
            $table->string('company_name')->nullable()->after('name');
            $table->string('cpf_cnpj', 18)->nullable()->after('company_name');
            $table->string('mobile_phone', 20)->nullable()->after('phone');

            // Endereço
            $table->string('address_street')->nullable();
            $table->string('address_number', 10)->nullable();
            $table->string('address_complement')->nullable();
            $table->string('address_neighborhood')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_state', 2)->nullable();
            $table->string('address_zipcode', 9)->nullable();

            // Tipo de empresa
            $table->enum('company_type', ['MEI', 'LIMITED', 'INDIVIDUAL', 'ASSOCIATION'])->default('MEI');

            // Dados bancários
            $table->enum('bank_account_type', ['CONTA_CORRENTE', 'CONTA_POUPANCA'])->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_agency', 10)->nullable();
            $table->string('bank_account', 20)->nullable();
            $table->string('bank_account_digit', 2)->nullable();

            // Status Asaas
            $table->enum('asaas_status', ['pending', 'approved', 'rejected'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'company_name',
                'cpf_cnpj',
                'mobile_phone',
                'address_street',
                'address_number',
                'address_complement',
                'address_neighborhood',
                'address_city',
                'address_state',
                'address_zipcode',
                'company_type',
                'bank_account_type',
                'bank_name',
                'bank_agency',
                'bank_account',
                'bank_account_digit',
                'asaas_status',
            ]);
        });
    }
};
