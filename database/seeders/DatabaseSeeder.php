<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding Database...');
        
        // Seeders do Schema PUBLIC (Plataforma)
        $this->call([
            PlanSeeder::class,
            PlatformUserSeeder::class,
        ]);

        $this->command->info('✅ Database seeding completo!');
    }
}
