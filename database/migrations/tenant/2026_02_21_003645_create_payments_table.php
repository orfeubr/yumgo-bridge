<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            
            $table->enum('gateway', ['asaas', 'cash', 'card_machine'])->default('asaas');
            $table->enum('method', ['pix', 'credit_card', 'debit_card', 'cash'])->default('pix');
            
            $table->decimal('amount', 10, 2);
            $table->decimal('fee', 10, 2)->default(0.00); // Taxa do gateway
            $table->decimal('net_amount', 10, 2); // Valor líquido após taxas
            
            $table->string('transaction_id')->nullable(); // ID do Asaas
            $table->string('asaas_payment_url')->nullable();
            $table->string('pix_qrcode')->nullable();
            $table->string('pix_copy_paste')->nullable();
            
            $table->enum('status', ['pending', 'processing', 'confirmed', 'failed', 'refunded'])->default('pending');
            
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            
            $table->json('metadata')->nullable(); // Dados extras do gateway
            $table->timestamps();
            
            $table->index('transaction_id');
            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
