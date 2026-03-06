<?php

namespace App\Observers;

use App\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Support\Facades\Log;

class TenantObserver
{
    // AsaasService removido - agora usa apenas Pagar.me

    /**
     * Handle the Tenant "created" event.
     */
    public function created(Tenant $tenant): void
    {
        // 1. Criar estrutura de storage
        $this->createStorageStructure($tenant);

        // 2. Criar domínio automaticamente
        $this->createDomain($tenant);

        // 3. ⚠️ Asaas REMOVIDO - usar Pagar.me (configurar manualmente no painel)
        // $this->createAsaasAccount($tenant);

        // 4. Criar usuário admin automaticamente
        $this->createAdminUser($tenant);
    }

    /**
     * Cria estrutura de diretórios de storage para o tenant
     */
    protected function createStorageStructure(Tenant $tenant): void
    {
        try {
            $tenantId = $tenant->id;
            $baseDir = storage_path('tenant' . $tenantId);

            $directories = [
                $baseDir,
                $baseDir . '/app',
                $baseDir . '/app/public',
                $baseDir . '/framework',
                $baseDir . '/framework/cache',
                $baseDir . '/framework/cache/data',
                $baseDir . '/framework/sessions',
                $baseDir . '/framework/testing',
                $baseDir . '/framework/views',
                $baseDir . '/logs',
            ];

            foreach ($directories as $dir) {
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
            }

            // Corrigir permissões para www-data
            if (function_exists('exec')) {
                exec("sudo chown -R www-data:www-data {$baseDir}");
            }

            Log::info("✅ Estrutura de storage criada para tenant {$tenant->name}");

        } catch (\Exception $e) {
            Log::error("❌ Erro ao criar estrutura de storage para tenant {$tenant->id}: " . $e->getMessage());
        }
    }

    /**
     * Cria domínio para o tenant
     */
    protected function createDomain(Tenant $tenant): void
    {
        try {
            $domain = $tenant->slug . '.yumgo.com.br';

            // Verificar se já existe
            $existingDomain = Domain::where('domain', $domain)->first();

            if (!$existingDomain) {
                Domain::create([
                    'domain' => $domain,
                    'tenant_id' => $tenant->id,
                ]);

                Log::info("✅ Domínio criado automaticamente: {$domain} para tenant {$tenant->name}");
            } else {
                Log::warning("⚠️ Domínio {$domain} já existe para outro tenant");
            }
        } catch (\Exception $e) {
            Log::error("❌ Erro ao criar domínio automático para tenant {$tenant->id}: " . $e->getMessage());
        }
    }

    /**
     * Cria sub-conta Asaas para o tenant
     *
     * @deprecated Asaas foi substituído por Pagar.me como gateway principal
     * Mantido apenas para compatibilidade com tenants antigos
     */
    protected function createAsaasAccount(Tenant $tenant): void
    {
        // ⚠️ MÉTODO DESABILITADO - Asaas é legado, usar Pagar.me
        // Para criar recebedor Pagar.me, use o painel admin ou o TenantResource

        Log::info("ℹ️ Criação automática de conta Asaas desabilitada para tenant {$tenant->name}. Use Pagar.me.");
        return;

        /*
        try {
            // Pular se já tem asaas_account_id
            if (!empty($tenant->asaas_account_id)) {
                Log::info("ℹ️ Tenant {$tenant->name} já possui asaas_account_id: {$tenant->asaas_account_id}");
                return;
            }

            // Criar sub-conta no Asaas
            $accountData = [
                'name' => $tenant->name,
                'email' => $tenant->email,
                'cpfCnpj' => $tenant->cnpj ?? null,
                'companyType' => $tenant->cnpj ? 'MEI' : null,
                'phone' => $tenant->phone ?? null,
            ];

            $account = $this->asaasService->createSubAccount($accountData);

            if (isset($account['id'])) {
                // Salvar asaas_account_id no tenant
                $tenant->update([
                    'asaas_account_id' => $account['id'],
                    'asaas_wallet_id' => $account['walletId'] ?? null,
                ]);

                Log::info("✅ Sub-conta Asaas criada automaticamente: {$account['id']} para tenant {$tenant->name}");
            } else {
                Log::error("❌ Resposta inesperada do Asaas ao criar sub-conta para tenant {$tenant->id}: " . json_encode($account));
            }
        } catch (\Exception $e) {
            Log::error("❌ Erro ao criar sub-conta Asaas para tenant {$tenant->id}: " . $e->getMessage());
            // Não bloqueia a criação do tenant se falhar
        }
        */
    }

    /**
     * Cria usuário admin para o tenant
     */
    protected function createAdminUser(Tenant $tenant): void
    {
        try {
            // Inicializar contexto do tenant (para criar usuário no schema correto)
            tenancy()->initialize($tenant);

            // Verificar se já existe algum usuário
            $userCount = \App\Models\User::count();

            if ($userCount > 0) {
                Log::info("ℹ️ Tenant {$tenant->name} já possui {$userCount} usuário(s)");
                tenancy()->end();
                return;
            }

            // Criar usuário admin padrão
            $admin = \App\Models\User::create([
                'name' => 'Administrador',
                'email' => $tenant->email, // Usa o e-mail do tenant
                'password' => \Illuminate\Support\Facades\Hash::make('senha123'), // Senha padrão
                'role' => 'admin',
                'active' => true,
                'email_verified_at' => now(),
            ]);

            Log::info("✅ Usuário admin criado automaticamente para tenant {$tenant->name}");
            Log::info("🔑 LOGIN: {$admin->email} / senha123");

            // Finalizar contexto do tenant
            tenancy()->end();

            // Opcional: Enviar e-mail com credenciais (implementar depois)
            // Mail::to($tenant->email)->send(new WelcomeRestaurantMail($tenant, 'senha123'));

        } catch (\Exception $e) {
            Log::error("❌ Erro ao criar usuário admin para tenant {$tenant->id}: " . $e->getMessage());
            tenancy()->end();
            // Não bloqueia a criação do tenant se falhar
        }
    }

    /**
     * Handle the Tenant "updated" event.
     */
    public function updated(Tenant $tenant): void
    {
        // Se o slug mudou, atualizar o domínio principal (.yumgo.com.br)
        if ($tenant->isDirty('slug')) {
            try {
                $oldSlug = $tenant->getOriginal('slug');
                $newDomain = $tenant->slug . '.yumgo.com.br';

                // Buscar domínio principal (*.yumgo.com.br) - pode estar com slug antigo OU com ID
                $domain = Domain::where('tenant_id', $tenant->id)
                    ->where('domain', 'like', '%.yumgo.com.br')
                    ->whereNotIn('domain', function($query) use ($tenant) {
                        // Excluir domínios personalizados/extras
                        $query->select('domain')
                            ->from('domains')
                            ->where('tenant_id', $tenant->id)
                            ->whereNotLike('domain', '%.yumgo.com.br');
                    })
                    ->first();

                if ($domain) {
                    $oldDomain = $domain->domain;
                    $domain->update(['domain' => $newDomain]);
                    Log::info("✅ Domínio atualizado: {$oldDomain} → {$newDomain}");
                } else {
                    // Se não encontrou domínio, criar um novo
                    Domain::create([
                        'domain' => $newDomain,
                        'tenant_id' => $tenant->id,
                    ]);
                    Log::info("✅ Domínio criado: {$newDomain}");
                }
            } catch (\Exception $e) {
                Log::error("❌ Erro ao atualizar domínio: " . $e->getMessage());
            }
        }
    }

    /**
     * Handle the Tenant "deleted" event.
     */
    public function deleted(Tenant $tenant): void
    {
        // Os domínios são deletados automaticamente por cascata
        Log::info("🗑️ Tenant {$tenant->name} deletado (domínios removidos automaticamente)");
    }
}
