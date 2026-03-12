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
            $table->uuid('restaurant_type_id')->nullable()->after('slug');
            $table->foreign('restaurant_type_id')
                  ->references('id')
                  ->on('restaurant_types')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['restaurant_type_id']);
            $table->dropColumn('restaurant_type_id');
        });
    }
};
