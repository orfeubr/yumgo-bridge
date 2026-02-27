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
        Schema::table('customers', function (Blueprint $table) {
            // Login Social (Google/Facebook)
            $table->string('provider')->nullable()->after('password'); // google, facebook
            $table->string('provider_id')->nullable()->after('provider');
            $table->string('avatar')->nullable()->after('provider_id');

            // Verificação de WhatsApp
            $table->string('verification_code', 6)->nullable()->after('avatar');
            $table->timestamp('verification_code_expires_at')->nullable()->after('verification_code');
            $table->timestamp('phone_verified_at')->nullable()->after('verification_code_expires_at');

            // Tornar phone nullable (será preenchido depois no login social)
            $table->string('phone')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'provider',
                'provider_id',
                'avatar',
                'verification_code',
                'verification_code_expires_at',
                'phone_verified_at'
            ]);
        });
    }
};
