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
        Schema::table('neighborhoods', function (Blueprint $table) {
            $table->renameColumn('enabled', 'is_active');
        });

        // Atualizar índices
        Schema::table('neighborhoods', function (Blueprint $table) {
            $table->dropIndex(['city', 'enabled']);
            $table->dropIndex(['enabled']);
            $table->index(['city', 'is_active']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('neighborhoods', function (Blueprint $table) {
            $table->dropIndex(['city', 'is_active']);
            $table->dropIndex(['is_active']);
            $table->index(['city', 'enabled']);
            $table->index('enabled');
        });

        Schema::table('neighborhoods', function (Blueprint $table) {
            $table->renameColumn('is_active', 'enabled');
        });
    }
};
