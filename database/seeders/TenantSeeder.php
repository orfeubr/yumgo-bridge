<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Seed do tenant (executa dentro do schema do tenant)
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding Tenant Database...');

        $this->call([
            CashbackSettingsSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            CouponSeeder::class,
        ]);

        $this->command->info('✅ Tenant seeding completo!');
    }
}
