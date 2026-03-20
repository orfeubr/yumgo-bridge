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
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();

            // Operador (usuário que abriu o caixa)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('user_name'); // Nome do operador (snapshot)

            // Datas e Status
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');

            // Valores de Abertura
            $table->decimal('opening_balance', 10, 2)->default(0); // Fundo de troco inicial

            // Valores de Fechamento
            $table->decimal('closing_balance', 10, 2)->nullable(); // Total declarado
            $table->decimal('expected_balance', 10, 2)->nullable(); // Total esperado (calculado)
            $table->decimal('difference', 10, 2)->nullable(); // Quebra de caixa (closing - expected)

            // Totais por Método de Pagamento (calculados automaticamente)
            $table->decimal('total_cash', 10, 2)->default(0); // Dinheiro
            $table->decimal('total_pix', 10, 2)->default(0); // PIX
            $table->decimal('total_credit_card', 10, 2)->default(0); // Cartão Crédito
            $table->decimal('total_debit_card', 10, 2)->default(0); // Cartão Débito
            $table->decimal('total_other', 10, 2)->default(0); // Outros métodos

            // Contadores
            $table->integer('orders_count')->default(0); // Total de pedidos
            $table->integer('cancelled_count')->default(0); // Pedidos cancelados

            // Sangrias e Reforços
            $table->decimal('total_withdrawals', 10, 2)->default(0); // Total sangrias
            $table->decimal('total_deposits', 10, 2)->default(0); // Total reforços

            // Observações
            $table->text('opening_notes')->nullable(); // Notas de abertura
            $table->text('closing_notes')->nullable(); // Notas de fechamento

            $table->timestamps();

            // Índices
            $table->index('status');
            $table->index('opened_at');
            $table->index('closed_at');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_registers');
    }
};
