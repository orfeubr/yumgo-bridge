<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    // ⚠️ IMPORTANTE: Subscriptions está no schema PUBLIC (central)
    // Força usar conexão central mesmo quando tenancy está ativo
    protected $connection = 'pgsql';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'canceled_at',
        // Pagar.me fields
        'pagarme_subscription_id',
        'pagarme_customer_id',
        'pagarme_status',
        'next_billing_date',
        'last_payment_date',
        'amount',
        'payment_method',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'canceled_at' => 'datetime',
        'next_billing_date' => 'datetime',
        'last_payment_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Tenant da assinatura
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Plano da assinatura
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Scope para assinaturas ativas
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Verifica se está em trial
     */
    public function isOnTrial(): bool
    {
        return $this->status === 'trialing' && 
               $this->trial_ends_at && 
               $this->trial_ends_at->isFuture();
    }

    /**
     * Verifica se está ativa
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Verifica se está cancelada
     */
    public function isCanceled(): bool
    {
        return $this->status === 'canceled';
    }
}
