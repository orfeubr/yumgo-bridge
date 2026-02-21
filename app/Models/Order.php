<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number',
        'customer_id',
        'subtotal',
        'delivery_fee',
        'discount',
        'cashback_used',
        'total',
        'cashback_earned',
        'cashback_percentage',
        'status',
        'payment_status',
        'delivery_type',
        'delivery_address',
        'estimated_time',
        'customer_notes',
        'internal_notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'cashback_used' => 'decimal:2',
        'total' => 'decimal:2',
        'cashback_earned' => 'decimal:2',
        'cashback_percentage' => 'decimal:2',
    ];

    /**
     * Cliente do pedido
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Itens do pedido
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Pagamentos do pedido
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Entrega do pedido
     */
    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    /**
     * Avaliação do pedido
     */
    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    /**
     * Transações de cashback
     */
    public function cashbackTransactions(): HasMany
    {
        return $this->hasMany(CashbackTransaction::class);
    }

    /**
     * Scope por status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope confirmados
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope em preparo
     */
    public function scopePreparing($query)
    {
        return $query->where('status', 'preparing');
    }

    /**
     * Scope hoje
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope por cliente
     */
    public function scopeByCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Verifica se está pago
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Verifica se pode ser cancelado
     */
    public function canBeCanceled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    /**
     * Nome do status formatado
     */
    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'confirmed' => 'Confirmado',
            'preparing' => 'Em Preparo',
            'ready' => 'Pronto',
            'out_for_delivery' => 'Saiu para Entrega',
            'delivered' => 'Entregue',
            'canceled' => 'Cancelado',
            'refunded' => 'Reembolsado',
            default => 'Desconhecido',
        };
    }

    /**
     * Nome do tipo de entrega formatado
     */
    public function getDeliveryTypeNameAttribute(): string
    {
        return $this->delivery_type === 'delivery' ? 'Entrega' : 'Retirada';
    }
}
