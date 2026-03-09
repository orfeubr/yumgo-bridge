<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Inserir valores padrão
        DB::table('platform_settings')->insert([
            ['key' => 'platform_name', 'value' => 'YumGo', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'logo', 'value' => null, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'favicon', 'value' => null, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'primary_color', 'value' => '#EA1D2C', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
