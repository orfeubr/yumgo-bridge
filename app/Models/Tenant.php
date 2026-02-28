<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'name',
        'slug',
        'email',
        'phone',
        'mobile_phone',
        // Dados da empresa
        'company_name',
        'company_type',
        'cpf_cnpj',
        // Asaas (legado)
        'asaas_account_id',
        // Pagar.me (atual)
        'pagarme_recipient_id',
        'pagarme_api_key',
        'pagarme_encryption_key',
        'pagarme_split_rules',
        'payment_gateway',
        // Dados bancários
        'bank_code',
        'bank_name',
        'bank_agency',
        'bank_branch_digit',
        'bank_account',
        'bank_account_digit',
        'bank_account_type',
        // Outros
        'plan_id',
        'status',
        'trial_ends_at',
    ];

    protected $casts = [
        'pagarme_split_rules' => 'array',
        'trial_ends_at' => 'datetime',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'slug',
            'email',
            'phone',
            'asaas_account_id',
            'pagarme_recipient_id',
            'pagarme_api_key',
            'payment_gateway',
            'plan_id',
            'status',
            'trial_ends_at',
        ];
    }

    /**
     * Plano do tenant
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Assinaturas do tenant
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Faturas do tenant
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Scope ativos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope em trial
     */
    public function scopeTrial($query)
    {
        return $query->where('status', 'trial');
    }
}
