<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Pizza customization options
            $table->boolean('allows_half_and_half')->default(true)->after('is_pizza');
            $table->json('available_sizes')->nullable()->after('allows_half_and_half');
            $table->json('available_borders')->nullable()->after('available_sizes');
            $table->json('size_prices')->nullable()->after('available_borders')->comment('Custom prices per size');
            $table->json('border_prices')->nullable()->after('size_prices')->comment('Custom border prices');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'allows_half_and_half',
                'available_sizes',
                'available_borders',
                'size_prices',
                'border_prices',
            ]);
        });
    }
};
