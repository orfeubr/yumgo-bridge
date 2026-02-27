<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'min_order_value',
        'usage_limit',
        'usage_count',
        'usage_per_customer',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Scope para cupons ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            });
    }

    /**
     * Scope por código
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', strtoupper($code));
    }

    /**
     * Verifica se o cupom é válido
     */
    public function isValid(float $orderValue = 0): bool
    {
        // Verifica se está ativo
        if (!$this->is_active) {
            return false;
        }

        // Verifica data de início
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        // Verifica expiração
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        // Verifica limite de uso
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        // Verifica valor mínimo
        if ($this->min_order_value && $orderValue < $this->min_order_value) {
            return false;
        }

        return true;
    }

    /**
     * Calcula desconto
     */
    public function calculateDiscount(float $orderValue): float
    {
        if ($this->type === 'percentage') {
            return ($orderValue * $this->value) / 100;
        }

        return $this->value;
    }

    /**
     * Incrementa uso
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
