<?php

namespace App\Filament\Admin\Resources\TenantResource\Pages;

use App\Filament\Admin\Resources\TenantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Gerar ID baseado no slug
        $data['id'] = $data['slug'];

        return $data;
    }

    protected function afterCreate(): void
    {
        // O Observer já cria:
        // - Domínio ({slug}.yumgo.com.br)
        // - Sub-conta Asaas
        // - Usuário Admin

        $domain = $this->record->slug . '.yumgo.com.br';
        $email = $this->record->email;

        // Notificação com credenciais de acesso
        \Filament\Notifications\Notification::make()
            ->title('✅ Restaurante criado com sucesso!')
            ->success()
            ->body("
                **Domínio:** https://{$domain}/painel

                **Credenciais de Acesso:**
                📧 E-mail: {$email}
                🔑 Senha: senha123

                ⚠️ **IMPORTANTE:** Repasse essas credenciais ao cliente e oriente-o a trocar a senha no primeiro acesso!
            ")
            ->duration(30000) // 30 segundos
            ->persistent() // Não fecha automaticamente
            ->send();

        // Log para registro
        \Illuminate\Support\Facades\Log::info("🎉 Tenant criado com sucesso", [
            'tenant' => $this->record->name,
            'slug' => $this->record->slug,
            'domain' => $domain,
            'email' => $email,
            'default_password' => 'senha123',
        ]);
    }
}
