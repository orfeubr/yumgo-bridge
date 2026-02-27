<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class EditTenant extends EditRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Atualizar domínios automaticamente após editar o tenant
     *
     * IMPORTANTE: No sistema stancl/tenancy, o ID do tenant NÃO pode ser alterado
     * após a criação, pois está vinculado ao schema do PostgreSQL.
     *
     * Esta função adiciona NOVOS domínios se o campo 'slug' for atualizado,
     * mas mantém os domínios antigos para não quebrar links.
     */
    protected function afterSave(): void
    {
        $tenant = $this->record;
        $slug = $tenant->id; // O ID do tenant é usado como base

        // Domínios base
        $baseDomains = [
            env('APP_DOMAIN', 'yumgo.com.br'),
            'yumgo.com.br',
        ];

        // Criar novos domínios se não existirem
        foreach ($baseDomains as $baseDomain) {
            $domain = $slug . '.' . $baseDomain;

            // Verificar se já existe
            $exists = $tenant->domains()->where('domain', $domain)->exists();

            if (!$exists) {
                $tenant->domains()->create([
                    'domain' => $domain,
                ]);

                \Filament\Notifications\Notification::make()
                    ->title('Domínio adicionado')
                    ->body("Novo domínio criado: {$domain}")
                    ->success()
                    ->send();
            }
        }

        // 🔥 LIMPAR CACHE AUTOMATICAMENTE após salvar
        try {
            // Limpar cache de aplicação
            Cache::flush();

            // Limpar caches do Laravel
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            // Limpar cache do Filament
            Artisan::call('filament:clear-cached-components');

            \Filament\Notifications\Notification::make()
                ->title('Cache limpo')
                ->body('Todas as alterações foram aplicadas e o cache foi limpo automaticamente!')
                ->success()
                ->duration(3000)
                ->send();
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Aviso')
                ->body('Tenant salvo com sucesso, mas houve erro ao limpar cache: ' . $e->getMessage())
                ->warning()
                ->send();
        }
    }
}
