<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateInitialRestaurantAdmins extends Seeder
{
    /**
     * Cria usuários admin para cada restaurante existente
     */
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            echo "📍 Processando tenant: {$tenant->name} (ID: {$tenant->id})\n";

            // Inicializa contexto do tenant
            tenancy()->initialize($tenant);

            // Verifica se já existe algum usuário
            $userCount = User::count();

            if ($userCount > 0) {
                echo "  ✅ Tenant já possui {$userCount} usuário(s)\n";
                continue;
            }

            // Cria usuário admin padrão
            $admin = User::create([
                'name' => 'Administrador',
                'email' => "admin@{$tenant->id}.com",
                'password' => Hash::make('password'),
                'role' => 'admin',
                'active' => true,
                'email_verified_at' => now(),
            ]);

            echo "  ✅ Usuário admin criado: {$admin->email} (senha: password)\n";
            echo "  🔑 LOGIN: admin@{$tenant->id}.com / password\n";

            // Finaliza contexto do tenant
            tenancy()->end();
        }

        echo "\n✅ Processo concluído!\n";
    }
}
