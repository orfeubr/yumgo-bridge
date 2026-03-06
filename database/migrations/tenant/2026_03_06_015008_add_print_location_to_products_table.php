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
        Schema::table('products', function (Blueprint $table) {
            $table->enum('print_location', ['kitchen', 'bar', 'both'])
                ->default('kitchen')
                ->after('is_active')
                ->comment('Onde imprimir: kitchen=cozinha, bar=bar, both=ambos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('print_location');
        });
    }
};
