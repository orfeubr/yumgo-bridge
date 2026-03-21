<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Waiter extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'photo',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Pedidos atendidos por este garçom
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Pedidos ativos do garçom
     */
    public function activeOrders(): HasMany
    {
        return $this->orders()->whereNotIn('status', ['delivered', 'cancelled']);
    }

    /**
     * Conta de pedidos ativos
     */
    public function getActiveOrdersCountAttribute(): int
    {
        return $this->activeOrders()->count();
    }
}
