<?php

namespace App\Observers;

use App\Models\Tenant;
use App\Services\AsaasService;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Support\Facades\Log;

class TenantObserver
{
    protected AsaasService $asaasService;

    public function __construct(AsaasService $asaasService)
    {
        $this->asaasService = $asaasService;
    }

    /**
     * Handle the Tenant "created" event.
     */
    public function created(Tenant $tenant): void
    {
        // 1. Criar domínio automaticamente
        $this->createDomain($tenant);

        // 2. Criar sub-conta Asaas automaticamente
        $this->createAsaasAccount($tenant);

        // 3. Criar usuário admin automaticamente
        $this->createAdminUser($tenant);
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
     */
    protected function createAsaasAccount(Tenant $tenant): void
    {
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
        // Se o slug mudou, atualizar o domínio
        if ($tenant->isDirty('slug')) {
            try {
                $oldSlug = $tenant->getOriginal('slug');
                $oldDomain = $oldSlug . '.yumgo.com.br';
                $newDomain = $tenant->slug . '.yumgo.com.br';

                $domain = Domain::where('domain', $oldDomain)
                    ->where('tenant_id', $tenant->id)
                    ->first();

                if ($domain) {
                    $domain->update(['domain' => $newDomain]);
                    Log::info("✅ Domínio atualizado: {$oldDomain} → {$newDomain}");
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
