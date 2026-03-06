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
        // Marketplace
        'logo',
        'description',
        'business_hours',
        'accepting_orders',
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
        'business_hours' => 'array',
        'accepting_orders' => 'boolean',
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

    /**
     * Verifica se o restaurante está aberto agora
     */
    public function isOpen(): bool
    {
        // Se não aceita pedidos, está fechado
        if (!$this->accepting_orders) {
            return false;
        }

        // Se não tem horário configurado, assume que está aberto
        if (!$this->business_hours) {
            return true;
        }

        // Pegar dia e hora atual (timezone do Brasil)
        $now = now()->timezone('America/Sao_Paulo');
        $dayOfWeek = strtolower($now->format('l')); // monday, tuesday, etc
        $currentTime = $now->format('H:i'); // 14:30

        // Verifica se existe configuração para este dia
        if (!isset($this->business_hours[$dayOfWeek])) {
            return false;
        }

        $todayHours = $this->business_hours[$dayOfWeek];

        // Se está marcado como fechado
        if (isset($todayHours['closed']) && $todayHours['closed']) {
            return false;
        }

        // Verifica se está dentro do horário
        $openTime = $todayHours['open'] ?? '00:00';
        $closeTime = $todayHours['close'] ?? '23:59';

        return $currentTime >= $openTime && $currentTime <= $closeTime;
    }

    /**
     * Retorna URL da logo ou fallback
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo && file_exists(storage_path('app/public/' . $this->logo))) {
            return asset('storage/' . $this->logo);
        }

        // Fallback: Logo YumGo cinza (SVG)
        return asset('images/logo-yumgo-gray.svg');
    }

    /**
     * Relationship: Usuários do tenant
     * Nota: Esta é uma pseudo-relationship porque os usuários estão em outro schema
     */
    public function users()
    {
        // Não podemos usar hasMany tradicional porque está em outro schema
        // O RelationManager vai inicializar o tenancy e buscar os usuários
        return $this->newQuery()->whereRaw('false'); // Retorna query vazia
    }
}
