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
            // Estatísticas agregadas (atualizadas por job diário)
            $table->integer('total_orders')->default(0)->after('birth_date');
            $table->integer('total_orders_30d')->default(0)->after('total_orders'); // Últimos 30 dias
            $table->decimal('avg_rating', 3, 2)->default(0.00)->after('total_orders_30d');
            $table->integer('total_reviews')->default(0)->after('avg_rating');
            $table->timestamp('stats_updated_at')->nullable()->after('total_reviews');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'total_orders',
                'total_orders_30d',
                'avg_rating',
                'total_reviews',
                'stats_updated_at',
            ]);
        });
    }
};
