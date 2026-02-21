<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashback_settings', function (Blueprint $table) {
            $table->id();
            
            // Configurações por Tier
            $table->decimal('bronze_percentage', 5, 2)->default(2.00);
            $table->integer('bronze_min_orders')->default(0);
            $table->decimal('bronze_min_spent', 10, 2)->default(0.00);
            
            $table->decimal('silver_percentage', 5, 2)->default(3.50);
            $table->integer('silver_min_orders')->default(5);
            $table->decimal('silver_min_spent', 10, 2)->default(200.00);
            
            $table->decimal('gold_percentage', 5, 2)->default(5.00);
            $table->integer('gold_min_orders')->default(15);
            $table->decimal('gold_min_spent', 10, 2)->default(500.00);
            
            $table->decimal('platinum_percentage', 5, 2)->default(7.00);
            $table->integer('platinum_min_orders')->default(30);
            $table->decimal('platinum_min_spent', 10, 2)->default(1000.00);
            
            // Bônus
            $table->boolean('birthday_bonus_enabled')->default(true);
            $table->decimal('birthday_multiplier', 5, 2)->default(2.00);
            
            $table->boolean('referral_enabled')->default(true);
            $table->decimal('referral_bonus_referrer', 10, 2)->default(10.00);
            $table->decimal('referral_bonus_referred', 10, 2)->default(5.00);
            
            // Validade
            $table->integer('expiration_days')->default(180);
            $table->decimal('min_order_value_to_earn', 10, 2)->default(10.00);
            $table->decimal('min_cashback_to_use', 10, 2)->default(5.00);
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashback_settings');
    }
};
