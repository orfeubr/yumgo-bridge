<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->string('slug')->unique()->after('name');
            $table->string('email')->after('slug');
            $table->string('phone')->nullable()->after('email');
            $table->string('asaas_account_id')->nullable()->after('phone');
            $table->foreignId('plan_id')->nullable()->after('asaas_account_id')->constrained('plans')->onDelete('set null');
            $table->enum('status', ['active', 'inactive', 'suspended', 'trial'])->default('trial')->after('plan_id');
            $table->timestamp('trial_ends_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn(['name', 'slug', 'email', 'phone', 'asaas_account_id', 'plan_id', 'status', 'trial_ends_at']);
        });
    }
};
