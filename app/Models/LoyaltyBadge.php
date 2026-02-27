<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyBadge extends Model
{
    protected $fillable = [
        'customer_id',
        'badge_type',
        'name',
        'description',
        'icon',
        'bonus_cashback',
        'earned_at',
    ];

    protected $casts = [
        'bonus_cashback' => 'decimal:2',
        'earned_at' => 'datetime',
    ];

    /**
     * Cliente do badge
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Scope por tipo
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('badge_type', $type);
    }

    /**
     * Scope recentes
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('earned_at', '>=', now()->subDays($days));
    }

    /**
     * Verifica se tem bônus
     */
    public function hasBonus(): bool
    {
        return $this->bonus_cashback > 0;
    }
}
