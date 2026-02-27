<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->text('pix_qrcode')->nullable()->change();
            $table->text('pix_copy_paste')->nullable()->change();
            $table->text('asaas_payment_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('pix_qrcode')->nullable()->change();
            $table->string('pix_copy_paste')->nullable()->change();
            $table->string('asaas_payment_url')->nullable()->change();
        });
    }
};
