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
            // Verificar e adicionar apenas se não existir
            if (!Schema::hasColumn('tenants', 'cnpj')) {
                $table->string('cnpj', 14)->nullable()->after('phone');
            }

            if (!Schema::hasColumn('tenants', 'cep')) {
                $table->string('cep', 8)->nullable()->after('cnpj');
            }

            if (!Schema::hasColumn('tenants', 'street')) {
                $table->string('street')->nullable()->after('cep');
            }

            if (!Schema::hasColumn('tenants', 'number')) {
                $table->string('number', 20)->nullable()->after('street');
            }

            if (!Schema::hasColumn('tenants', 'complement')) {
                $table->string('complement', 100)->nullable()->after('number');
            }

            if (!Schema::hasColumn('tenants', 'neighborhood')) {
                $table->string('neighborhood', 100)->nullable()->after('complement');
            }

            if (!Schema::hasColumn('tenants', 'city')) {
                $table->string('city', 100)->nullable()->after('neighborhood');
            }

            if (!Schema::hasColumn('tenants', 'state')) {
                $table->string('state', 2)->nullable()->after('city');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $columns = ['cnpj', 'cep', 'street', 'number', 'complement', 'neighborhood', 'city', 'state'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('tenants', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
