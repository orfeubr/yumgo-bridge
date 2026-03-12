<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Verifica se índice existe antes de criar
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $exists = DB::select("
            SELECT 1
            FROM pg_indexes
            WHERE tablename = ?
            AND indexname = ?
        ", [$table, $indexName]);

        return !empty($exists);
    }

    /**
     * Cria índice apenas se não existir
     */
    private function createIndexIfNotExists(string $table, $columns, string $indexName): void
    {
        if (!$this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
                $blueprint->index($columns, $indexName);
            });
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Índices para products (performance em listagem e busca)
        $this->createIndexIfNotExists('products', ['is_active', 'category_id'], 'idx_products_is_active_category');
        $this->createIndexIfNotExists('products', 'name', 'idx_products_name');
        $this->createIndexIfNotExists('products', 'price', 'idx_products_price');

        // Índices para orders (performance em histórico e filtros)
        $this->createIndexIfNotExists('orders', ['customer_id', 'created_at'], 'idx_orders_customer_date');
        $this->createIndexIfNotExists('orders', ['status', 'created_at'], 'idx_orders_status_date');
        $this->createIndexIfNotExists('orders', 'payment_status', 'idx_orders_payment_status');

        // Índices para customers (performance em busca e autenticação)
        $this->createIndexIfNotExists('customers', 'phone', 'idx_customers_phone');
        $this->createIndexIfNotExists('customers', 'loyalty_tier', 'idx_customers_loyalty_tier');

        // Índices para cashback_transactions (performance em histórico)
        $this->createIndexIfNotExists('cashback_transactions', ['customer_id', 'created_at'], 'idx_cashback_customer_date');
        $this->createIndexIfNotExists('cashback_transactions', 'type', 'idx_cashback_type');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_is_active_category');
            $table->dropIndex('idx_products_name');
            $table->dropIndex('idx_products_price');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_customer_date');
            $table->dropIndex('idx_orders_status_date');
            $table->dropIndex('idx_orders_payment_status');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('idx_customers_phone');
            $table->dropIndex('idx_customers_loyalty_tier');
        });

        Schema::table('cashback_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_cashback_customer_date');
            $table->dropIndex('idx_cashback_type');
        });
    }
};
