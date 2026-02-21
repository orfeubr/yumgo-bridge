<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\CashbackSettings;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TenantService
{
    public function __construct(
        private AsaasService $asaasService
    ) {}

    /**
     * Cria novo tenant (restaurante)
     */
    public function createTenant(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {
            // Cria tenant
            $tenant = Tenant::create([
                'id' => $data['id'] ?? Str::uuid(),
                'data' => [
                    'name' => $data['name'],
                    'slug' => $data['slug'] ?? Str::slug($data['name']),
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'plan_id' => $data['plan_id'] ?? null,
                    'status' => 'trial',
                    'trial_ends_at' => now()->addDays(15),
                ],
            ]);

            // Atualiza campos extras
            $tenant->name = $data['name'];
            $tenant->slug = $data['slug'] ?? Str::slug($data['name']);
            $tenant->email = $data['email'];
            $tenant->phone = $data['phone'] ?? null;
            $tenant->plan_id = $data['plan_id'] ?? null;
            $tenant->save();

            // Cria sub-conta Asaas
            try {
                $asaasAccountId = $this->asaasService->createSubAccount($tenant);
                if ($asaasAccountId) {
                    $tenant->asaas_account_id = $asaasAccountId;
                    $tenant->save();
                }
            } catch (\Exception $e) {
                // Log erro mas não falha a criação do tenant
                logger()->error('Erro ao criar sub-conta Asaas', [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Cria domínio
            if (isset($data['domain'])) {
                $tenant->domains()->create([
                    'domain' => $data['domain'],
                ]);
            }

            // Cria configurações padrão no schema do tenant
            tenancy()->initialize($tenant);
            $this->createDefaultSettings($tenant);
            tenancy()->end();

            return $tenant;
        });
    }

    /**
     * Cria configurações padrão no tenant
     */
    private function createDefaultSettings(Tenant $tenant): void
    {
        // Configurações de cashback padrão
        CashbackSettings::create([
            'bronze_percentage' => 2.00,
            'bronze_min_orders' => 0,
            'bronze_min_spent' => 0.00,
            
            'silver_percentage' => 3.50,
            'silver_min_orders' => 5,
            'silver_min_spent' => 200.00,
            
            'gold_percentage' => 5.00,
            'gold_min_orders' => 15,
            'gold_min_spent' => 500.00,
            
            'platinum_percentage' => 7.00,
            'platinum_min_orders' => 30,
            'platinum_min_spent' => 1000.00,
            
            'birthday_bonus_enabled' => true,
            'birthday_multiplier' => 2.00,
            
            'referral_enabled' => true,
            'referral_bonus_referrer' => 10.00,
            'referral_bonus_referred' => 5.00,
            
            'expiration_days' => 180,
            'min_order_value_to_earn' => 10.00,
            'min_cashback_to_use' => 5.00,
            
            'is_active' => true,
        ]);

        // Pode criar outras configurações padrão aqui
        // Ex: categorias padrão, produtos exemplo, etc
    }

    /**
     * Atualiza plano do tenant
     */
    public function updatePlan(Tenant $tenant, int $planId): void
    {
        $plan = Plan::findOrFail($planId);
        
        $tenant->plan_id = $plan->id;
        $tenant->save();

        // Cria/atualiza subscription
        $tenant->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
        ]);
    }

    /**
     * Suspende tenant
     */
    public function suspendTenant(Tenant $tenant, string $reason = null): void
    {
        $tenant->status = 'suspended';
        $tenant->save();

        // Registra no audit log
        \App\Models\AuditLog::create([
            'tenant_id' => $tenant->id,
            'event' => 'tenant.suspended',
            'new_values' => [
                'reason' => $reason,
                'suspended_at' => now(),
            ],
        ]);
    }

    /**
     * Reativa tenant
     */
    public function activateTenant(Tenant $tenant): void
    {
        $tenant->status = 'active';
        $tenant->save();

        \App\Models\AuditLog::create([
            'tenant_id' => $tenant->id,
            'event' => 'tenant.activated',
            'new_values' => [
                'activated_at' => now(),
            ],
        ]);
    }
}
