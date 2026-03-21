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
        'public_token',
        'customer_id',
        'cash_register_id', // ✅ Vínculo com caixa
        'subtotal',
        'delivery_fee',
        'discount',
        'coupon_code', // ✅ Permite salvar código do cupom aplicado
        'cashback_used',
        'total',
        'cashback_earned',
        'cashback_percentage',
        'status',
        'payment_status',
        'payment_method',
        'print_status', // ✅ Status de impressão
        'printed_at', // ✅ Timestamp da impressão
        'print_error', // ✅ Erro de impressão
        'print_attempts', // ✅ Tentativas de impressão
        'delivery_type',
        'service_type', // ✅ counter, table, delivery
        'table_number', // ✅ Número da mesa (se service_type = table)
        'delivery_address',
        'delivery_city',
        'delivery_neighborhood',
        'estimated_time',
        'customer_notes',
        'internal_notes',
        'expires_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->public_token)) {
                $order->public_token = bin2hex(random_bytes(6));
            }
        });
    }

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'cashback_used' => 'decimal:2',
        'total' => 'decimal:2',
        'cashback_earned' => 'decimal:2',
        'cashback_percentage' => 'decimal:2',
        'expires_at' => 'datetime',
        'printed_at' => 'datetime',
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
     * Caixa ao qual este pedido pertence
     */
    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
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
     * Nota fiscal do pedido
     */
    public function fiscalNote(): HasOne
    {
        return $this->hasOne(FiscalNote::class);
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
     * Verifica se o pedido expirou
     * Pedidos não pagos expiram após o tempo definido (padrão: final do dia)
     */
    public function isExpired(): bool
    {
        // Se já foi pago, nunca expira
        if ($this->isPaid()) {
            return false;
        }

        // Se tem expires_at definido, verificar
        if ($this->expires_at) {
            return now()->isAfter($this->expires_at);
        }

        // Fallback: pedidos de mais de 1 dia sem pagamento expiram
        return $this->created_at->addDay()->isPast();
    }

    /**
     * Verifica se o restaurante está aberto agora
     */
    public function isRestaurantOpen(): bool
    {
        $settings = \App\Models\Settings::first();

        if (!$settings) {
            return true; // Se não tem configuração, assume aberto
        }

        // Usa o método isOpenNow() do Settings que verifica business_hours corretamente
        return $settings->isOpenNow();
    }

    /**
     * Verifica se o pedido pode receber pagamento
     */
    public function canReceivePayment(): bool
    {
        // Já pago
        if ($this->isPaid()) {
            return false;
        }

        // Expirado
        if ($this->isExpired()) {
            return false;
        }

        // ⭐ REMOVIDO: Não bloqueia se restaurante fechou DEPOIS do pedido criado
        // Cliente deve poder pagar pedidos já feitos, mesmo se restaurante fechou

        return true;
    }

    /**
     * Mensagem explicando por que não pode receber pagamento
     */
    public function getPaymentBlockedReason(): ?string
    {
        if ($this->isPaid()) {
            return 'Pedido já pago';
        }

        if ($this->isExpired()) {
            return 'Pedido expirado';
        }

        // ⭐ REMOVIDO: Não bloqueia por restaurante fechado

        return null;
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

    /**
     * Marca impressão como bem-sucedida
     */
    public function markPrintSuccess(string $location): void
    {
        $this->print_status = 'printed';
        $this->printed_at = now();
        $this->print_error = null;
        $this->print_attempts = ($this->print_attempts ?? 0) + 1;
        $this->save();
    }

    /**
     * Marca impressão como falha
     */
    public function markPrintFailed(string $location, string $error): void
    {
        $this->print_status = 'failed';
        $this->print_error = $error;
        $this->print_attempts = ($this->print_attempts ?? 0) + 1;
        $this->save();

        // ⭐ Agendar retry automático (se ainda não atingiu máximo)
        if ($this->print_attempts < 3) {
            $delayMinutes = $this->print_attempts; // 1min, 2min, 3min
            \App\Jobs\RetryPrintJob::dispatch($this->id, $this->print_attempts + 1)
                ->delay(now()->addMinutes($delayMinutes));

            \Log::info("📄 Retry de impressão agendado para o pedido #{$this->order_number} em {$delayMinutes} minutos");
        } else {
            \Log::warning("⚠️ Pedido #{$this->order_number} atingiu máximo de tentativas automáticas (3)");
        }
    }

    /**
     * Marca que impressão está em andamento
     */
    public function markPrinting(): void
    {
        $this->print_status = 'printing';
        $this->save();
    }

    /**
     * Scope pedidos não impressos
     */
    public function scopeNotPrinted($query)
    {
        return $query->where('print_status', '!=', 'printed');
    }

    /**
     * Scope pedidos com falha de impressão
     */
    public function scopePrintFailed($query)
    {
        return $query->where('print_status', 'failed');
    }

    /**
     * Verifica se precisa reimprimir
     */
    public function needsReprint(): bool
    {
        return in_array($this->print_status, ['pending', 'failed']);
    }
}
