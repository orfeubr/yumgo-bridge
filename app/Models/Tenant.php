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
    use \App\Models\Concerns\HasSubscriptionLimits;

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
        'cnpj',
        'razao_social',
        'inscricao_estadual',
        'inscricao_municipal',
        'regime_tributario',
        // Marketplace
        'logo',
        'description',
        'cuisine_types',
        'business_hours',
        'accepting_orders',
        // Endereço completo
        'address',
        'address_street',
        'address_number',
        'address_complement',
        'address_neighborhood',
        'address_city',
        'address_state',
        'address_zipcode',
        'latitude',
        'longitude',
        // Endereço fiscal (NFC-e)
        'fiscal_address',
        'fiscal_number',
        'fiscal_complement',
        'fiscal_neighborhood',
        'fiscal_city',
        'fiscal_state',
        'fiscal_zipcode',
        // Asaas (legado)
        'asaas_account_id',
        'asaas_status',
        // Pagar.me (atual)
        'pagarme_recipient_id',
        'pagarme_customer_id',
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
        // Tributa AI
        'tributaai_token',
        'tributaai_enabled',
        'tributaai_environment',
        // NFC-e
        'certificate_a1',
        'certificate_password',
        'nfce_serie',
        'nfce_numero',
        'nfce_environment',
        'csc_id',
        'csc_token',
        // Outros
        'plan_id',
        'status',
        'trial_ends_at',
        'birth_date',
        // Estatísticas agregadas
        'total_orders',
        'total_orders_30d',
        'avg_rating',
        'total_reviews',
        'stats_updated_at',
        // Aprovação de restaurantes
        'approval_status',
        'approved_at',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'pagarme_split_rules' => 'array',
        'cuisine_types' => 'array',
        'business_hours' => 'array',
        'accepting_orders' => 'boolean',
        'trial_ends_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'stats_updated_at' => 'datetime',
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
