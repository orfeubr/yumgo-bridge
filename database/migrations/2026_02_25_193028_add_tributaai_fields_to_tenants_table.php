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
            // Tributa AI
            $table->string('tributaai_token')->nullable()->after('asaas_wallet_id');
            $table->boolean('tributaai_enabled')->default(false)->after('tributaai_token');
            $table->enum('tributaai_environment', ['sandbox', 'production'])->default('sandbox')->after('tributaai_enabled');

            // Dados Fiscais
            $table->string('cnpj')->nullable()->after('tributaai_environment');
            $table->string('razao_social')->nullable()->after('cnpj');
            $table->string('inscricao_estadual')->nullable()->after('razao_social');
            $table->string('inscricao_municipal')->nullable()->after('inscricao_estadual');

            // Regime Tributário
            $table->enum('regime_tributario', ['simples_nacional', 'lucro_presumido', 'lucro_real', 'mei'])
                ->default('simples_nacional')
                ->after('inscricao_municipal');

            // Certificado Digital A1 (base64)
            $table->text('certificate_a1')->nullable()->after('regime_tributario');
            $table->string('certificate_password')->nullable()->after('certificate_a1');

            // Configurações NFC-e
            $table->integer('nfce_serie')->default(1)->after('certificate_password');
            $table->integer('nfce_numero')->default(1)->after('nfce_serie');
            $table->string('csc_id')->nullable()->after('nfce_numero'); // Código de Segurança do Contribuinte
            $table->string('csc_token')->nullable()->after('csc_id');

            // Endereço Fiscal (pode ser diferente do endereço de entrega)
            $table->string('fiscal_address')->nullable()->after('csc_token');
            $table->string('fiscal_number')->nullable()->after('fiscal_address');
            $table->string('fiscal_complement')->nullable()->after('fiscal_number');
            $table->string('fiscal_neighborhood')->nullable()->after('fiscal_complement');
            $table->string('fiscal_city')->nullable()->after('fiscal_neighborhood');
            $table->string('fiscal_state')->nullable()->after('fiscal_city');
            $table->string('fiscal_zipcode')->nullable()->after('fiscal_state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'tributaai_token',
                'tributaai_enabled',
                'tributaai_environment',
                'cnpj',
                'razao_social',
                'inscricao_estadual',
                'inscricao_municipal',
                'regime_tributario',
                'certificate_a1',
                'certificate_password',
                'nfce_serie',
                'nfce_numero',
                'csc_id',
                'csc_token',
                'fiscal_address',
                'fiscal_number',
                'fiscal_complement',
                'fiscal_neighborhood',
                'fiscal_city',
                'fiscal_state',
                'fiscal_zipcode',
            ]);
        });
    }
};
