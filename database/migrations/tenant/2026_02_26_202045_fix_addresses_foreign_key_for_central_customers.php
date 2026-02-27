<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Remove foreign key de addresses.customer_id porque agora usa central_customers
     */
    public function up(): void
    {
        try {
            // Tentar dropar foreign key se existir
            Schema::table('addresses', function (Blueprint $table) {
                $table->dropForeign(['customer_id']);
            });
        } catch (\Exception $e) {
            // Se já foi removida, ignora
        }

        // Garantir que tem índice
        try {
            Schema::table('addresses', function (Blueprint $table) {
                $table->index('customer_id');
            });
        } catch (\Exception $e) {
            // Se já existe, ignora
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não faz nada no down - não queremos recriar a FK
    }
};
