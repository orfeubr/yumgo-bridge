<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_menus', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Ex: Cardápio da Semana - Fevereiro');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('starts_at')->nullable()->comment('Data de início (opcional)');
            $table->date('ends_at')->nullable()->comment('Data de término (opcional)');
            $table->timestamps();
        });

        Schema::create('weekly_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->decimal('special_price', 10, 2)->nullable()->comment('Preço especial para este dia (opcional)');
            $table->integer('order')->default(0)->comment('Ordem de exibição');
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            // Índices
            $table->index(['weekly_menu_id', 'day_of_week']);
            $table->unique(['weekly_menu_id', 'product_id', 'day_of_week'], 'unique_menu_product_day');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_menu_items');
        Schema::dropIfExists('weekly_menus');
    }
};
