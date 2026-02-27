<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashbackTransaction extends Model
{
    protected $fillable = [
        'customer_id',
        'order_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'expires_at',
        'expired_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'expires_at' => 'date',
        'expired_at' => 'datetime',
    ];

    /**
     * Cliente da transação
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Pedido relacionado (se houver)
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope para cashback ganho
     */
    public function scopeEarned($query)
    {
        return $query->where('type', 'earned');
    }

    /**
     * Scope para cashback usado
     */
    public function scopeUsed($query)
    {
        return $query->where('type', 'used');
    }

    /**
     * Scope para cashback expirado
     */
    public function scopeExpired($query)
    {
        return $query->where('type', 'expired');
    }

    /**
     * Scope para cashback não expirado
     */
    public function scopeNotExpired($query)
    {
        return $query->whereNull('expired_at')
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Nome do tipo formatado
     */
    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'earned' => 'Ganho',
            'used' => 'Usado',
            'expired' => 'Expirado',
            'bonus' => 'Bônus',
            'referral' => 'Indicação',
            default => 'Desconhecido',
        };
    }
}
