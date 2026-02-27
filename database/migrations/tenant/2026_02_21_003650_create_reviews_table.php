<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            
            $table->integer('rating'); // 1-5
            $table->text('comment')->nullable();
            
            // Avaliações detalhadas
            $table->integer('food_rating')->nullable();
            $table->integer('delivery_rating')->nullable();
            $table->integer('service_rating')->nullable();
            
            $table->boolean('is_public')->default(true);
            $table->text('response')->nullable(); // Resposta do restaurante
            $table->timestamp('responded_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['customer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
