<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remover constraint antiga
        DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_payment_status_check");

        // Criar nova constraint com 'awaiting_delivery'
        DB::statement("
            ALTER TABLE orders
            ADD CONSTRAINT orders_payment_status_check
            CHECK (payment_status IN ('pending', 'awaiting_delivery', 'paid', 'failed', 'refunded'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover constraint nova
        DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_payment_status_check");

        // Recriar constraint antiga (sem 'awaiting_delivery')
        DB::statement("
            ALTER TABLE orders
            ADD CONSTRAINT orders_payment_status_check
            CHECK (payment_status IN ('pending', 'paid', 'failed', 'refunded'))
        ");
    }
};
