<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('name'); // P, M, G, Brotinho, Família
            $table->decimal('price_modifier', 10, 2)->default(0.00); // Diferença do preço base
            $table->enum('modifier_type', ['fixed', 'percentage'])->default('fixed');
            $table->integer('serves')->nullable(); // Serve X pessoas
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variations');
    }
};
