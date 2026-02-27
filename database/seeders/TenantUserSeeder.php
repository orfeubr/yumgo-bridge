<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TenantUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Pizzaria Bella',
            'email' => 'admin@pizzariabella.com.br',
            'password' => Hash::make('senha123'),
            'role' => 'admin',
            'active' => true,
            'email_verified_at' => now()
        ]);
    }
}
