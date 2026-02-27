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
     * Migra sistema para login universal:
     * 1. Adiciona campos OAuth em customers
     * 2. Cria tabela customer_tenant
     * 3. Migra dados de cashback/loyalty para customer_tenant
     */
    public function up(): void
    {
        // 1. Adicionar campos OAuth em customers (se não existirem)
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'provider')) {
                $table->string('provider')->nullable()->after('email_verified_at');
            }
            if (!Schema::hasColumn('customers', 'provider_id')) {
                $table->string('provider_id')->nullable()->after('provider');
            }
            if (!Schema::hasColumn('customers', 'avatar')) {
                $table->string('avatar')->nullable()->after('provider_id');
            }
            if (!Schema::hasColumn('customers', 'verification_code')) {
                $table->string('verification_code', 6)->nullable()->after('avatar');
            }
            if (!Schema::hasColumn('customers', 'verification_code_expires_at')) {
                $table->timestamp('verification_code_expires_at')->nullable()->after('verification_code');
            }
            if (!Schema::hasColumn('customers', 'phone_verified_at')) {
                $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            }
            
            // Adicionar índices
            if (!Schema::hasColumn('customers', 'provider')) {
                $table->index(['provider', 'provider_id']);
            }
        });

        // 2. Criar tabela customer_tenant
        if (!Schema::hasTable('customer_tenant')) {
            Schema::create('customer_tenant', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
                $table->string('tenant_id');

                // Dados específicos do tenant
                $table->decimal('cashback_balance', 10, 2)->default(0);
                $table->enum('loyalty_tier', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze');
                $table->integer('total_orders')->default(0);
                $table->decimal('total_spent', 12, 2)->default(0);

                // Controle
                $table->timestamp('first_order_at')->nullable();
                $table->timestamp('last_order_at')->nullable();
                $table->boolean('is_active')->default(true);

                $table->timestamps();

                // Constraints
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->unique(['customer_id', 'tenant_id']);

                // Indexes
                $table->index('tenant_id');
                $table->index('customer_id');
            });
        }

        // 3. Migrar dados existentes para customer_tenant
        // NOTA: Como customers já está no schema public, vamos assumir que
        // são dados de um tenant específico e migrar para ele
        $defaultTenantId = 'marmitaria-gi'; // Tenant padrão para migração
        
        DB::statement("
            INSERT INTO customer_tenant (
                customer_id, 
                tenant_id, 
                cashback_balance, 
                loyalty_tier, 
                total_orders, 
                total_spent,
                is_active,
                created_at,
                updated_at
            )
            SELECT 
                id,
                '{$defaultTenantId}',
                COALESCE(cashback_balance, 0),
                COALESCE(loyalty_tier, 'bronze'),
                COALESCE(total_orders, 0),
                COALESCE(total_spent, 0),
                COALESCE(is_active, true),
                created_at,
                updated_at
            FROM customers
            WHERE NOT EXISTS (
                SELECT 1 FROM customer_tenant 
                WHERE customer_tenant.customer_id = customers.id 
                AND customer_tenant.tenant_id = '{$defaultTenantId}'
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover campos OAuth
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'provider',
                'provider_id',
                'avatar',
                'verification_code',
                'verification_code_expires_at',
                'phone_verified_at',
            ]);
        });

        // Dropar customer_tenant
        Schema::dropIfExists('customer_tenant');
    }
};
