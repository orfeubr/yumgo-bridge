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
            ['email' => 'admin@yumgo.com.br'],
            [
                'name' => 'Admin YumGo',
                'email' => 'admin@yumgo.com.br',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );

        echo "✅ Admin user criado: admin@yumgo.com.br / admin123\n";
    }
}
