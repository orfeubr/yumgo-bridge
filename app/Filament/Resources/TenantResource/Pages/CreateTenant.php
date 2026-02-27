<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    /**
     * Criar domínios e usuário admin automaticamente após criar o tenant
     */
    protected function afterCreate(): void
    {
        $tenant = $this->record;
        $slug = $tenant->slug;

        // Domínios base
        $baseDomains = [
            env('APP_DOMAIN', 'yumgo.com.br'),
            'yumgo.com.br',
        ];

        // Lista de novos domínios criados
        $newDomains = [];

        // Criar domínio para cada base
        foreach ($baseDomains as $baseDomain) {
            $domain = $slug . '.' . $baseDomain;

            if (!$tenant->domains()->where('domain', $domain)->exists()) {
                $tenant->domains()->create(['domain' => $domain]);
                $newDomains[] = $domain;
            }
        }

        // 🔥 CRIAR USUÁRIO ADMIN NO TENANT
        $adminName = $this->data['admin_name'] ?? 'Admin ' . $tenant->name;
        $adminEmail = $this->data['admin_email'] ?? 'admin@' . $slug . '.com.br';
        $isSuper = $this->data['admin_is_super'] ?? true;

        // Gerar senha aleatória forte
        $password = \Str::random(12);

        // Inicializar tenancy para criar usuário no schema do tenant
        tenancy()->initialize($tenant);

        try {
            // Criar usuário
            $user = \App\Models\User::create([
                'name' => $adminName,
                'email' => $adminEmail,
                'password' => \Hash::make($password),
                'email_verified_at' => now(),
            ]);

            // Atribuir role de super_admin se marcado
            if ($isSuper) {
                // Verificar se role existe, senão criar
                $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
                $user->assignRole($role);
            }

            $userCreated = true;
            $userMessage = "👤 Usuário Admin criado:\nEmail: {$adminEmail}\nSenha: {$password}\n\n⚠️ IMPORTANTE: Salve estas credenciais!";
        } catch (\Exception $e) {
            $userCreated = false;
            $userMessage = "❌ Erro ao criar usuário: " . $e->getMessage();
        }

        // Finalizar tenancy
        tenancy()->end();

        // Notificação de sucesso
        if (count($newDomains) > 0 || $userCreated) {
            $oauthUrls = collect($newDomains)
                ->map(fn($domain) => "https://{$domain}/auth/google/callback")
                ->implode("\n");

            $body = "✅ Tenant criado com sucesso!\n\n";

            if (count($newDomains) > 0) {
                $body .= "🌐 Domínios criados:\n" . implode("\n", $newDomains) . "\n\n";
            }

            if ($userCreated) {
                $body .= $userMessage . "\n\n";
                $body .= "🔗 Login: https://{$slug}.yumgo.com.br/painel/login\n\n";
            }

            if (count($newDomains) > 0) {
                $body .= "⚠️ Adicione estas URLs no Google OAuth:\n" . $oauthUrls;
            }

            \Filament\Notifications\Notification::make()
                ->title('Tenant criado!')
                ->body($body)
                ->success()
                ->persistent()
                ->send();
        }
    }
}
