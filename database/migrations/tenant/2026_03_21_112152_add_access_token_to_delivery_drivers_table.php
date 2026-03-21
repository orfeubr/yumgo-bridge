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
        Schema::table('delivery_drivers', function (Blueprint $table) {
            $table->string('access_token', 64)->unique()->nullable()->after('is_active');
            $table->timestamp('token_generated_at')->nullable()->after('access_token');
            $table->timestamp('last_access_at')->nullable()->after('token_generated_at');
        });

        // Gerar tokens para entregadores existentes
        DB::statement("UPDATE delivery_drivers SET access_token = MD5(RANDOM()::text || id || NOW()::text) WHERE access_token IS NULL");
        DB::statement("UPDATE delivery_drivers SET token_generated_at = NOW() WHERE token_generated_at IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_drivers', function (Blueprint $table) {
            $table->dropColumn(['access_token', 'token_generated_at', 'last_access_at']);
        });
    }
};
