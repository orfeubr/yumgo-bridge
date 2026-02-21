<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            
            $table->string('badge_type'); // first_order, 10_orders, vip_customer, etc
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            
            $table->decimal('bonus_cashback', 10, 2)->nullable();
            $table->timestamp('earned_at');
            
            $table->timestamps();
            
            $table->index(['customer_id', 'earned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_badges');
    }
};
