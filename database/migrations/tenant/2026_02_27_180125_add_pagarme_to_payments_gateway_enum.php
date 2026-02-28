<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL: Adicionar 'pagarme' ao enum de gateway
        DB::statement("ALTER TYPE payments_gateway_type ADD VALUE IF NOT EXISTS 'pagarme'");
    }

    public function down(): void
    {
        // Não é possível remover valores de ENUM no PostgreSQL facilmente
        // Deixa como está
    }
};
