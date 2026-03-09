<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->json('available_features')->nullable()->after('features');
        });

        // Setar features padrão para planos existentes
        DB::table('plans')->where('name', 'Trial')->update([
            'available_features' => json_encode([])
        ]);

        DB::table('plans')->where('name', 'Starter')->update([
            'available_features' => json_encode([])
        ]);

        DB::table('plans')->where('name', 'Pro')->update([
            'available_features' => json_encode([
                'nfce_auto',
                'import_csv',
                'user_permissions',
                'advanced_reports',
                'auto_print',
            ])
        ]);

        DB::table('plans')->where('name', 'Enterprise')->update([
            'available_features' => json_encode([
                'nfce_auto',
                'import_csv',
                'user_permissions',
                'advanced_reports',
                'auto_print',
                'api_access',
                'webhooks',
                'multi_store',
            ])
        ]);
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('available_features');
        });
    }
};
