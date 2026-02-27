<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('image')->nullable();
            $table->json('images')->nullable(); // Múltiplas imagens

            // Especificações especiais
            $table->text('filling')->nullable(); // Recheio da pizza (ex: Mussarela, Calabresa, etc)
            $table->json('pizza_config')->nullable(); // {allow_half_and_half, border_options, etc}
            $table->json('marmitex_config')->nullable(); // {max_proteins, max_sides, etc}
            
            // Estoque e disponibilidade
            $table->boolean('has_stock_control')->default(false);
            $table->integer('stock_quantity')->nullable();
            $table->integer('min_stock_alert')->nullable();
            
            $table->integer('preparation_time')->default(30); // minutos
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
