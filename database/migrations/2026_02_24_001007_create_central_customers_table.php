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
        // Criar tabela customers no schema PUBLIC (central)
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('phone', 20)->unique()->nullable(); // 🔥 NULLABLE para OAuth login
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();

            // OAuth Social Login
            $table->string('provider')->nullable(); // google, facebook
            $table->string('provider_id')->nullable();
            $table->string('avatar')->nullable();

            // WhatsApp Verification
            $table->string('verification_code', 6)->nullable();
            $table->timestamp('verification_code_expires_at')->nullable();

            // Dados pessoais
            $table->date('birth_date')->nullable();
            $table->string('cpf', 14)->nullable()->unique();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('email');
            $table->index('phone');
            $table->index(['provider', 'provider_id']);
        });

        // Tabela de relacionamento N:N entre customers e tenants
        Schema::create('customer_tenant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('tenant_id'); // FK para tenants.id

            // Dados específicos do tenant (cashback, tier, etc)
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
            $table->index(['customer_id', 'tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_tenant');
        Schema::dropIfExists('customers');
    }
};
