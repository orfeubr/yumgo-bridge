<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Table extends Model
{
    protected $fillable = [
        'number',
        'seats',
        'qr_token',
        'status',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'seats' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        // Gerar token automaticamente ao criar
        static::creating(function ($table) {
            if (empty($table->qr_token)) {
                $table->qr_token = Str::random(32);
            }
        });
    }

    /**
     * URL do QR Code para esta mesa
     */
    public function getQrUrlAttribute(): string
    {
        return url("/mesa/{$this->qr_token}");
    }

    /**
     * Pedidos desta mesa
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Pedidos ativos (não finalizados)
     */
    public function activeOrders(): HasMany
    {
        return $this->orders()->whereNotIn('status', ['delivered', 'cancelled']);
    }

    /**
     * Regenerar token do QR Code
     */
    public function regenerateToken(): void
    {
        $this->qr_token = Str::random(32);
        $this->save();
    }

    /**
     * Badge de status
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'available' => '🟢 Disponível',
            'occupied' => '🔴 Ocupada',
            'reserved' => '🟡 Reservada',
            default => $this->status,
        };
    }
}
