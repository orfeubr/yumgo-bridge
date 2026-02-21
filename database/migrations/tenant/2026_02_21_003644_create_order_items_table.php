<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->foreignId('product_variation_id')->nullable()->constrained('product_variations')->onDelete('set null');
            
            $table->string('product_name'); // Nome no momento da compra
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            
            // Adicionais selecionados (JSON)
            $table->json('addons')->nullable(); // [{addon_id, name, price, quantity}]
            
            // Para pizzas meio a meio
            $table->json('half_and_half')->nullable(); // {flavor1_id, flavor2_id, border_id}
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
