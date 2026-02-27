<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@deliverypro.com.br'],
            [
                'name' => 'Admin DeliveryPro',
                'email' => 'admin@deliverypro.com.br',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );
        
        echo "✅ Admin user criado: admin@deliverypro.com.br / admin123\n";
    }
}
