<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price_monthly',
        'commission_percentage',
        'pagarme_plan_id',
        'features',
        'max_products',
        'max_orders_per_month',
        'is_active',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Tenants com este plano
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    /**
     * Assinaturas deste plano
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Scope para planos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Verifica se é plano gratuito
     */
    public function isFree(): bool
    {
        return $this->price_monthly == 0;
    }
}
