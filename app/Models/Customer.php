<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use Notifiable, SoftDeletes, HasApiTokens;

    /**
     * Customer é SEMPRE do schema CENTRAL (public.customers)
     * Dados específicos por tenant ficam em customer_tenant (pivot table)
     * Tokens Sanctum ficam em public.personal_access_tokens
     */
    protected $connection = 'pgsql'; // Schema CENTRAL (PUBLIC)
    protected $table = 'customers';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'cpf',
        'birth_date',
        'password',
        'cashback_balance',
        'loyalty_tier',
        'total_orders',
        'total_spent',
        'address_street',
        'address_number',
        'address_complement',
        'address_neighborhood',
        'address_city',
        'address_state',
        'address_zipcode',
        'is_active',
        // OAuth fields
        'provider',
        'provider_id',
        'avatar',
        'phone_verified_at',
        'verification_code',
        'verification_code_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'cashback_balance' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'verification_code_expires_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Pedidos do cliente
     * 🔒 PROTEÇÃO: Requer tenancy inicializado (previne vazamento cross-tenant)
     */
    public function orders(): Builder
    {
        if (!tenancy()->initialized) {
            throw new \Exception('Tenancy must be initialized to access orders. This prevents cross-tenant data leakage.');
        }
        // Usar query builder do Order (que usa conexão tenant) em vez de herdar conexão do Customer
        return Order::where('customer_id', $this->id);
    }

    /**
     * Transações de cashback
     * 🔒 PROTEÇÃO: Requer tenancy inicializado (previne vazamento cross-tenant)
     */
    public function cashbackTransactions(): Builder
    {
        if (!tenancy()->initialized) {
            throw new \Exception('Tenancy must be initialized to access cashback transactions. This prevents cross-tenant data leakage.');
        }
        return CashbackTransaction::where('customer_id', $this->id);
    }

    /**
     * Badges de fidelidade
     * 🔒 PROTEÇÃO: Requer tenancy inicializado (previne vazamento cross-tenant)
     */
    public function loyaltyBadges(): Builder
    {
        if (!tenancy()->initialized) {
            throw new \Exception('Tenancy must be initialized to access loyalty badges. This prevents cross-tenant data leakage.');
        }
        return LoyaltyBadge::where('customer_id', $this->id);
    }

    /**
     * Avaliações do cliente
     * 🔒 PROTEÇÃO: Requer tenancy inicializado (previne vazamento cross-tenant)
     */
    public function reviews(): Builder
    {
        if (!tenancy()->initialized) {
            throw new \Exception('Tenancy must be initialized to access reviews. This prevents cross-tenant data leakage.');
        }
        return Review::where('customer_id', $this->id);
    }

    /**
     * Scope para clientes VIP (tier ouro ou platina)
     */
    public function scopeVip($query)
    {
        return $query->whereIn('loyalty_tier', ['gold', 'platinum']);
    }

    /**
     * Scope para clientes com saldo
     */
    public function scopeWithBalance($query)
    {
        return $query->where('cashback_balance', '>', 0);
    }

    /**
     * Verifica se é aniversário hoje
     */
    public function isBirthdayToday(): bool
    {
        if (!$this->birth_date) {
            return false;
        }

        return $this->birth_date->isBirthday();
    }

    /**
     * Endereço completo formatado
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_street,
            $this->address_number,
            $this->address_complement,
            $this->address_neighborhood,
            $this->address_city,
            $this->address_state,
            $this->address_zipcode,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Nome do tier formatado
     */
    public function getTierNameAttribute(): string
    {
        return match($this->loyalty_tier) {
            'bronze' => 'Bronze',
            'silver' => 'Prata',
            'gold' => 'Ouro',
            'platinum' => 'Platina',
            default => 'Bronze',
        };
    }

    /**
     * Relacionamento many-to-many com tenants
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(
            Tenant::class,
            'customer_tenant',
            'customer_id',
            'tenant_id'
        )
        ->using(CustomerTenant::class)
        ->withPivot([
            'cashback_balance',
            'loyalty_tier',
            'total_orders',
            'total_spent',
            'first_order_at',
            'last_order_at',
            'is_active',
        ])
        ->withTimestamps();
    }

    /**
     * Criar ou obter relacionamento com tenant
     *
     * @param string $tenantId
     * @return CustomerTenant
     */
    public function getOrCreateTenantRelation(string $tenantId): CustomerTenant
    {
        // Buscar relacionamento existente
        $relation = CustomerTenant::where('customer_id', $this->id)
            ->where('tenant_id', $tenantId)
            ->first();

        // Se não existe, criar
        if (!$relation) {
            $relation = CustomerTenant::create([
                'customer_id' => $this->id,
                'tenant_id' => $tenantId,
                'cashback_balance' => 0,
                'loyalty_tier' => 'bronze',
                'total_orders' => 0,
                'total_spent' => 0,
                'is_active' => true,
            ]);
        }

        return $relation;
    }

    /**
     * Obter dados do relacionamento com tenant específico
     *
     * @param string $tenantId
     * @return array
     */
    public function getTenantData(string $tenantId): array
    {
        $relation = $this->getOrCreateTenantRelation($tenantId);

        return [
            'cashback_balance' => $relation->cashback_balance,
            'loyalty_tier' => $relation->loyalty_tier,
            'total_orders' => $relation->total_orders,
            'total_spent' => $relation->total_spent,
            'first_order_at' => $relation->first_order_at,
            'last_order_at' => $relation->last_order_at,
        ];
    }
}
