<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('restrict');
            
            // Valores
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->default(0.00);
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->decimal('cashback_used', 10, 2)->default(0.00); // Cashback aplicado
            $table->decimal('total', 10, 2);
            
            // Cashback ganho neste pedido
            $table->decimal('cashback_earned', 10, 2)->default(0.00);
            $table->decimal('cashback_percentage', 5, 2)->default(0.00);
            
            // Status
            $table->enum('status', [
                'pending', 'confirmed', 'preparing', 
                'ready', 'out_for_delivery', 'delivered', 
                'canceled', 'refunded'
            ])->default('pending');
            
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            
            // Entrega
            $table->enum('delivery_type', ['delivery', 'pickup'])->default('delivery');
            $table->text('delivery_address')->nullable();
            $table->integer('estimated_time')->nullable(); // minutos
            
            // Observações
            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['customer_id', 'created_at']);
            $table->index('order_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
