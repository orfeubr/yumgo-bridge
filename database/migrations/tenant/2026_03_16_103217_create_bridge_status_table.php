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
        Schema::create('bridge_status', function (Blueprint $table) {
            $table->id();
            $table->timestamp('last_heartbeat')->nullable();
            $table->string('version')->nullable();
            $table->json('printers')->nullable(); // Lista de impressoras conectadas
            $table->enum('status', ['online', 'offline'])->default('offline');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bridge_status');
    }
};
