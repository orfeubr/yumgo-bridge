<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabela de Mesas
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('number', 10); // Ex: "1", "2A", "Varanda 3"
            $table->integer('seats')->default(4); // Número de lugares
            $table->string('qr_token', 32)->unique(); // Token único para QR Code
            $table->enum('status', ['available', 'occupied', 'reserved'])->default('available');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable(); // Ex: "Perto da janela"
            $table->timestamps();
        });

        // Tabela de Garçons
        Schema::create('waiters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Adicionar campos à tabela orders existente
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('order_source', ['online', 'table', 'counter'])->default('online')->after('type');
            $table->foreignId('table_id')->nullable()->constrained('tables')->nullOnDelete()->after('order_source');
            $table->foreignId('waiter_id')->nullable()->constrained('waiters')->nullOnDelete()->after('table_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['table_id']);
            $table->dropForeign(['waiter_id']);
            $table->dropColumn(['order_source', 'table_id', 'waiter_id']);
        });

        Schema::dropIfExists('waiters');
        Schema::dropIfExists('tables');
    }
};
