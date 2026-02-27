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
        Schema::create('neighborhoods', function (Blueprint $table) {
            $table->id();
            $table->string('city'); // Cidade (ex: São Paulo)
            $table->string('name'); // Nome do bairro (ex: Centro)
            $table->boolean('enabled')->default(false); // Restaurante atende?
            $table->decimal('delivery_fee', 10, 2)->nullable(); // Taxa de entrega
            $table->integer('delivery_time')->nullable(); // Tempo estimado (minutos)
            $table->decimal('minimum_order', 10, 2)->nullable(); // Pedido mínimo (opcional)
            $table->integer('order')->default(0); // Ordem de exibição
            $table->timestamps();

            // Índices para busca rápida
            $table->index(['city', 'enabled']);
            $table->index('enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('neighborhoods');
    }
};
