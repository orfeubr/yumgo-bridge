<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    protected $fillable = [
        'order_id',
        'driver_id',
        'pickup_address',
        'delivery_address',
        'distance_km',
        'delivery_fee',
        'status',
        'picked_up_at',
        'delivered_at',
        'notes',
    ];

    protected $casts = [
        'distance_km' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * Pedido da entrega
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Entregador
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Scope aguardando entregador
     */
    public function scopeWaitingDriver($query)
    {
        return $query->where('status', 'waiting_driver');
    }

    /**
     * Scope em trânsito
     */
    public function scopeInTransit($query)
    {
        return $query->where('status', 'in_transit');
    }

    /**
     * Nome do status formatado
     */
    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            'waiting_driver' => 'Aguardando Entregador',
            'driver_assigned' => 'Entregador Atribuído',
            'picked_up' => 'Coletado',
            'in_transit' => 'Em Trânsito',
            'delivered' => 'Entregue',
            'failed' => 'Falhou',
            default => 'Desconhecido',
        };
    }

    /**
     * Tempo de entrega em minutos
     */
    public function getDeliveryTimeAttribute(): ?int
    {
        if (!$this->picked_up_at || !$this->delivered_at) {
            return null;
        }

        return $this->picked_up_at->diffInMinutes($this->delivered_at);
    }
}
