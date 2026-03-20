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
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();

            // Relacionamento com o caixa
            $table->foreignId('cash_register_id')->constrained()->onDelete('cascade');

            // Usuário responsável
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('user_name'); // Snapshot do nome

            // Tipo de movimentação
            $table->enum('type', ['withdrawal', 'deposit']); // Sangria ou Reforço
            $table->decimal('amount', 10, 2); // Valor

            // Justificativa
            $table->string('reason'); // Motivo da movimentação
            $table->text('notes')->nullable(); // Observações adicionais

            // Comprovante (opcional)
            $table->string('receipt_path')->nullable(); // Caminho do arquivo

            $table->timestamps();

            // Índices
            $table->index('cash_register_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};
