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

        // 3. ⚠️ IMPORTANTE: Rodar migrations ANTES de criar usuário
        $this->runMigrations($tenant);

        // 4. Criar usuário admin automaticamente (requer tabela users criada)
        $this->createAdminUser($tenant);

        // 5. ⚠️ Asaas REMOVIDO - usar Pagar.me (configurar manualmente no painel)
        // $this->createAsaasAccount($tenant);
    }

    /**
     * Cria estrutura de diretórios de storage para o tenant
     */
    protected function createStorageStructure(Tenant $tenant): void
    {
        try {
            $tenantId = $tenant->id;

            // 🔒 SEGURANÇA: Validar tenant ID para prevenir command injection
            // Aceitar apenas: letras, números, hífens, underscores (max 255 caracteres)
            if (!preg_match('/^[a-zA-Z0-9\-_]{1,255}$/', $tenantId)) {
                throw new \Exception("Tenant ID inválido: contém caracteres não permitidos");
            }

            $baseDir = storage_path('tenant' . $tenantId);

            // 🔒 Validar que path está dentro de storage/
            $realBasePath = realpath(storage_path());
            if (strpos($baseDir, $realBasePath) !== 0) {
                throw new \Exception("Tentativa de acesso fora do diretório storage");
            }

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
            // 🔒 SEGURANÇA: Usar escapeshellarg() para prevenir injection
            if (function_exists('exec')) {
                $safePath = escapeshellarg($baseDir);
                exec("sudo chown -R www-data:www-data {$safePath}");
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

        // Se o plano mudou, sincronizar assinatura automaticamente
        if ($tenant->isDirty('plan_id')) {
            $this->syncSubscription($tenant);
        }

        // Se a logo mudou, sincronizar com settings do tenant
        if ($tenant->isDirty('logo')) {
            $this->syncLogoToSettings($tenant);
        }
    }

    /**
     * Sincroniza a logo do tenant para o settings.
     *
     * ARQUITETURA PROFISSIONAL (Storage Central):
     * - Arquivo fica em: /storage/app/public/tenants/logos/xxx.png (storage ÚNICO)
     * - Apenas o PATH é sincronizado entre tenants.logo → settings.logo
     * - Não copia arquivos (mantém storage centralizado)
     *
     * Vantagens:
     * - Um único storage para backup
     * - Fácil migração futura para S3/CDN
     * - Performance melhor
     */
    protected function syncLogoToSettings(Tenant $tenant): void
    {
        try {
            // Inicializar contexto do tenant
            tenancy()->initialize($tenant);

            // Buscar settings
            $settings = \App\Models\Settings::first();

            if ($settings) {
                // Sincronizar apenas o PATH (não copia arquivo físico)
                $settings->update([
                    'logo' => $tenant->logo,
                ]);

                Log::info("✅ Logo path sincronizado", [
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                    'logo_path' => $tenant->logo,
                    'storage' => 'central',
                ]);
            } else {
                Log::warning("⚠️ Settings não encontrado para tenant {$tenant->name}");
            }

            // Finalizar contexto
            tenancy()->end();

        } catch (\Exception $e) {
            Log::error("❌ Erro ao sincronizar logo: " . $e->getMessage());
            tenancy()->end();
        }
    }

    /**
     * Sincroniza a assinatura quando o plano do tenant é alterado.
     *
     * Quando o administrador altera o plano no painel central (TenantResource),
     * esta função garante que a assinatura na tabela subscriptions seja
     * atualizada automaticamente para refletir a mudança.
     */
    protected function syncSubscription(Tenant $tenant): void
    {
        try {
            // Se não tem plano configurado, não fazer nada
            if (!$tenant->plan_id) {
                Log::info("ℹ️ Tenant {$tenant->name} sem plano configurado, pulando sincronização de assinatura");
                return;
            }

            $plan = $tenant->plan;
            if (!$plan) {
                Log::warning("⚠️ Plano ID {$tenant->plan_id} não encontrado para tenant {$tenant->name}");
                return;
            }

            // Buscar assinatura ativa existente
            $activeSubscription = \App\Models\Subscription::where('tenant_id', $tenant->id)
                ->whereIn('status', ['active', 'trialing'])
                ->first();

            if ($activeSubscription) {
                // Atualizar assinatura existente
                $oldPlanId = $activeSubscription->plan_id;
                $activeSubscription->update([
                    'plan_id' => $plan->id,
                    'amount' => $plan->price_monthly,
                ]);

                Log::info("✅ Assinatura atualizada automaticamente", [
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                    'subscription_id' => $activeSubscription->id,
                    'old_plan_id' => $oldPlanId,
                    'new_plan_id' => $plan->id,
                    'new_plan_name' => $plan->name,
                    'new_amount' => $plan->price_monthly,
                ]);
            } else {
                // Criar nova assinatura
                $subscription = \App\Models\Subscription::create([
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'amount' => $plan->price_monthly,
                    'starts_at' => now(),
                    'ends_at' => null,
                    'trial_ends_at' => $plan->trial_period_days > 0 ? now()->addDays($plan->trial_period_days) : null,
                    'next_billing_date' => now()->addMonth(),
                ]);

                Log::info("✅ Assinatura criada automaticamente", [
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                    'subscription_id' => $subscription->id,
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->name,
                    'amount' => $plan->price_monthly,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("❌ Erro ao sincronizar assinatura para tenant {$tenant->id}: " . $e->getMessage());
            // Não bloqueia a atualização do tenant se falhar
        }
    }

    /**
     * Roda migrations automaticamente para o tenant
     *
     * ⚠️ IMPORTANTE: Deve ser chamado ANTES de createAdminUser()
     * pois precisa criar a tabela users primeiro
     */
    protected function runMigrations(Tenant $tenant): void
    {
        try {
            Log::info("🔄 Rodando migrations para tenant {$tenant->name}...");

            // Inicializar tenancy
            tenancy()->initialize($tenant);

            // Rodar migrations do tenant
            \Artisan::call('migrate', [
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);

            // Finalizar tenancy
            tenancy()->end();

            Log::info("✅ Migrations executadas com sucesso para tenant {$tenant->name}");

        } catch (\Exception $e) {
            Log::error("❌ Erro ao rodar migrations para tenant {$tenant->id}: " . $e->getMessage());
            // Não lançar exception para não bloquear criação do tenant
            // Migrations podem ser executadas manualmente depois
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
