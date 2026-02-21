<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use Notifiable, SoftDeletes;

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
        'password' => 'hashed',
    ];

    /**
     * Pedidos do cliente
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Transações de cashback
     */
    public function cashbackTransactions(): HasMany
    {
        return $this->hasMany(CashbackTransaction::class);
    }

    /**
     * Badges de fidelidade
     */
    public function loyaltyBadges(): HasMany
    {
        return $this->hasMany(LoyaltyBadge::class);
    }

    /**
     * Avaliações do cliente
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
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
}
