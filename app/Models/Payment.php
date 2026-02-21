<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'gateway',
        'method',
        'amount',
        'fee',
        'net_amount',
        'transaction_id',
        'asaas_payment_url',
        'pix_qrcode',
        'pix_copy_paste',
        'status',
        'paid_at',
        'refunded_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Pedido do pagamento
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope pagos
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Verifica se está confirmado
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Verifica se é PIX
     */
    public function isPix(): bool
    {
        return $this->method === 'pix';
    }

    /**
     * Nome do método formatado
     */
    public function getMethodNameAttribute(): string
    {
        return match($this->method) {
            'pix' => 'PIX',
            'credit_card' => 'Cartão de Crédito',
            'debit_card' => 'Cartão de Débito',
            'cash' => 'Dinheiro',
            default => 'Outro',
        };
    }

    /**
     * Nome do gateway formatado
     */
    public function getGatewayNameAttribute(): string
    {
        return match($this->gateway) {
            'asaas' => 'Asaas',
            'cash' => 'Dinheiro',
            'card_machine' => 'Maquininha',
            default => 'Outro',
        };
    }
}
