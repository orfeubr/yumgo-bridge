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
        Schema::create('fiscal_notes', function (Blueprint $table) {
            $table->id();

            // Relacionamento com pedido
            $table->foreignId('order_id')->constrained()->onDelete('cascade');

            // Dados Tributa AI
            $table->string('tributaai_note_id')->nullable()->unique();
            $table->integer('note_number');
            $table->integer('serie');

            // Status
            $table->enum('status', ['pending', 'processing', 'authorized', 'rejected', 'cancelled', 'error'])
                ->default('pending');

            // Chave de Acesso (44 dígitos)
            $table->string('chave_acesso', 44)->nullable();

            // Protocolo SEFAZ
            $table->string('protocolo')->nullable();

            // URLs dos arquivos
            $table->text('pdf_url')->nullable();
            $table->text('xml_url')->nullable();

            // Datas
            $table->timestamp('emission_date')->nullable();
            $table->timestamp('authorization_date')->nullable();
            $table->timestamp('cancellation_date')->nullable();

            // Motivo de cancelamento ou erro
            $table->text('error_message')->nullable();
            $table->string('cancellation_reason')->nullable();

            // Response completa da API (JSON)
            $table->json('raw_response')->nullable();

            // Totais da nota (para consulta rápida)
            $table->decimal('total_value', 10, 2);
            $table->decimal('tax_value', 10, 2)->default(0);

            $table->timestamps();

            // Índices
            $table->index('status');
            $table->index('emission_date');
            $table->index('note_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiscal_notes');
    }
};
