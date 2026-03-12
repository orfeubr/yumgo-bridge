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
        Schema::create('restaurant_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100); // Pizzaria, Hamburgueria, etc
            $table->string('slug', 100)->unique();
            $table->string('icon', 50)->nullable(); // emoji ou class CSS
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_types');
    }
};
