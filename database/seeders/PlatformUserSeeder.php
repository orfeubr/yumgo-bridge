<?php

namespace Database\Seeders;

use App\Models\PlatformUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PlatformUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin YumGo',
                'email' => 'admin@yumgo.com.br',
                'password' => Hash::make('admin123'),
                'role' => 'super_admin',
                'is_active' => true,
            ],
            [
                'name' => 'Suporte YumGo',
                'email' => 'suporte@yumgo.com.br',
                'password' => Hash::make('suporte123'),
                'role' => 'support',
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            PlatformUser::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }

        $this->command->info('✅ Usuários da plataforma criados!');
        $this->command->info('   📧 Email: admin@yumgo.com.br');
        $this->command->info('   🔑 Senha: admin123');
    }
}
