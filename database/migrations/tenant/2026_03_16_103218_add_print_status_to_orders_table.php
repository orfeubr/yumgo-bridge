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
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('print_status', ['pending', 'printing', 'printed', 'failed'])->default('pending')->after('payment_status');
            $table->timestamp('printed_at')->nullable()->after('print_status');
            $table->text('print_error')->nullable()->after('printed_at');
            $table->integer('print_attempts')->default(0)->after('print_error');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['print_status', 'printed_at', 'print_error', 'print_attempts']);
        });
    }
};
