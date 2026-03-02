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
        Schema::table('tenants', function (Blueprint $table) {
            // Marketplace (verificar se não existe antes de adicionar)
            if (!Schema::hasColumn('tenants', 'logo')) {
                $table->string('logo')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('tenants', 'description')) {
                $table->text('description')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('tenants', 'business_hours')) {
                // Horário de funcionamento (JSON)
                // Formato: {"monday": {"open": "08:00", "close": "22:00", "closed": false}, ...}
                $table->json('business_hours')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('tenants', 'accepting_orders')) {
                // Aceita pedidos agora?
                $table->boolean('accepting_orders')->default(true)->after('slug');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['logo', 'description', 'business_hours', 'accepting_orders']);
        });
    }
};
