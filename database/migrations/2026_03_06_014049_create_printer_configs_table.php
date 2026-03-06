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
        Schema::create('printer_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nome da impressora (ex: "Cozinha", "Bar")
            $table->enum('type', ['kitchen', 'bar', 'counter', 'generic'])->default('kitchen');
            $table->enum('connection_type', ['network', 'usb', 'cloud', 'webhook'])->default('webhook');

            // Configurações de rede
            $table->string('ip_address')->nullable();
            $table->integer('port')->default(9100)->nullable();

            // Configurações USB
            $table->string('device_path')->nullable(); // ex: /dev/usb/lp0

            // Configurações Cloud (PrintNode, ePrint, etc.)
            $table->string('cloud_provider')->nullable(); // printnode, eprint, etc.
            $table->string('cloud_api_key')->nullable();
            $table->string('cloud_printer_id')->nullable();

            // Webhook (para app local)
            $table->string('webhook_url')->nullable();
            $table->string('webhook_token')->nullable();

            // Opções
            $table->boolean('auto_print')->default(true); // Imprimir automaticamente
            $table->boolean('is_active')->default(true);
            $table->integer('copies')->default(1); // Número de cópias

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printer_configs');
    }
};
