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
                'name' => 'Admin DeliveryPro',
                'email' => 'admin@deliverypro.com.br',
                'password' => Hash::make('admin123'),
                'role' => 'super_admin',
                'is_active' => true,
            ],
            [
                'name' => 'Suporte DeliveryPro',
                'email' => 'suporte@deliverypro.com.br',
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
        $this->command->info('   📧 Email: admin@deliverypro.com.br');
        $this->command->info('   🔑 Senha: admin123');
    }
}
