<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 🚀 PERFORMANCE: Adiciona índices para queries frequentes
     *
     * Impacto esperado:
     * - Busca por email/phone: 100x+ mais rápida
     * - Busca por order_number: 50x+ mais rápida
     * - Busca por coupon code: 20x+ mais rápida
     * - Filtros por status: 10x+ mais rápido
     */
    public function up(): void
    {
        // 📧 CUSTOMERS: Busca por email/phone (login, pedidos)
        if (!$this->indexExists('customers', 'idx_customers_email')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->index('email', 'idx_customers_email');
            });
        }
        if (!$this->indexExists('customers', 'idx_customers_phone')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->index('phone', 'idx_customers_phone');
            });
        }

        // 📦 ORDERS: Busca por order_number, filtros por status
        if (!$this->indexExists('orders', 'idx_orders_order_number')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index('order_number', 'idx_orders_order_number');
            });
        }
        if (!$this->indexExists('orders', 'idx_orders_payment_status')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index('payment_status', 'idx_orders_payment_status');
            });
        }
        if (!$this->indexExists('orders', 'idx_orders_customer_payment')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index(['customer_id', 'payment_status'], 'idx_orders_customer_payment');
            });
        }

        // 🎟️ COUPONS: Busca por código (validação de cupom)
        if (!$this->indexExists('coupons', 'idx_coupons_code_active')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->index(['code', 'is_active'], 'idx_coupons_code_active');
            });
        }

        // 🛍️ PRODUCTS: Busca por slug, filtros por ativo
        if (!$this->indexExists('products', 'idx_products_slug')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index('slug', 'idx_products_slug');
            });
        }
        if (!$this->indexExists('products', 'idx_products_category_active')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index(['category_id', 'is_active'], 'idx_products_category_active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->indexExists('customers', 'idx_customers_email')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropIndex('idx_customers_email');
            });
        }
        if ($this->indexExists('customers', 'idx_customers_phone')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropIndex('idx_customers_phone');
            });
        }
        if ($this->indexExists('orders', 'idx_orders_order_number')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex('idx_orders_order_number');
            });
        }
        if ($this->indexExists('orders', 'idx_orders_payment_status')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex('idx_orders_payment_status');
            });
        }
        if ($this->indexExists('orders', 'idx_orders_customer_payment')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex('idx_orders_customer_payment');
            });
        }
        if ($this->indexExists('coupons', 'idx_coupons_code_active')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->dropIndex('idx_coupons_code_active');
            });
        }
        if ($this->indexExists('products', 'idx_products_slug')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex('idx_products_slug');
            });
        }
        if ($this->indexExists('products', 'idx_products_category_active')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex('idx_products_category_active');
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $schema = $connection->getConfig('schema') ?? $connection->getTablePrefix();

        $result = $connection->select("
            SELECT 1
            FROM pg_indexes
            WHERE schemaname = ? AND indexname = ?
        ", [$schema, $index]);

        return !empty($result);
    }
};
