<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Cria índice apenas se não existir
     */
    private function createIndexIfNotExists(string $table, string $column): void
    {
        $indexName = "{$table}_{$column}_index";

        // Verifica se o índice já existe
        $indexExists = DB::select("
            SELECT 1
            FROM pg_indexes
            WHERE tablename = ?
            AND indexname = ?
        ", [$table, $indexName]);

        if (empty($indexExists)) {
            Schema::table($table, function (Blueprint $blueprint) use ($column) {
                $blueprint->index($column);
            });
        }
    }

    /**
     * Run the migrations.
     *
     * IMPORTANTE: Esta migration REMOVE a tabela customers do schema do tenant
     * e atualiza as tabelas para apontar para customers centrais (schema public).
     */
    public function up(): void
    {
        // 1. Dropar foreign keys antigas que apontam para customers local
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });

        Schema::table('cashback_transactions', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });

        Schema::table('loyalty_badges', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });

        // 2. Dropar tabela customers do tenant (vai ser central)
        Schema::dropIfExists('customers');

        // 3. As foreign keys NÃO podem apontar para outra schema no PostgreSQL
        // Então vamos manter customer_id como bigInteger sem FK constraint
        // A integridade será garantida pelo código da aplicação

        // 4. Adicionar índices nas tabelas que usam customer_id (se não existirem)
        $this->createIndexIfNotExists('orders', 'customer_id');
        $this->createIndexIfNotExists('cashback_transactions', 'customer_id');
        $this->createIndexIfNotExists('reviews', 'customer_id');
        $this->createIndexIfNotExists('addresses', 'customer_id');
        $this->createIndexIfNotExists('loyalty_badges', 'customer_id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recriar tabela customers no tenant (rollback)
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('phone', 20)->unique();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable();
            $table->string('avatar')->nullable();
            $table->string('verification_code', 6)->nullable();
            $table->timestamp('verification_code_expires_at')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('cpf', 14)->nullable()->unique();
            $table->decimal('cashback_balance', 10, 2)->default(0);
            $table->enum('loyalty_tier', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze');
            $table->integer('total_orders')->default(0);
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        // Recriar foreign keys
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });

        Schema::table('cashback_transactions', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });

        Schema::table('loyalty_badges', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }
};
