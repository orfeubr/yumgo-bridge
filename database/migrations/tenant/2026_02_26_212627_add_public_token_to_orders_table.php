<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'public_token')) {
                $table->string('public_token', 16)->nullable()->unique()->after('order_number');
            }
        });

        // Adicionar índice separadamente (se não existir)
        if (!Schema::hasColumn('orders', 'public_token')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index('public_token');
            });
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['public_token']);
            $table->dropColumn('public_token');
        });
    }
};
