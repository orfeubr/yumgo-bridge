<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'opened_at',
        'closed_at',
        'status',
        'opening_balance',
        'closing_balance',
        'expected_balance',
        'difference',
        'total_cash',
        'total_pix',
        'total_credit_card',
        'total_debit_card',
        'total_other',
        'orders_count',
        'cancelled_count',
        'total_withdrawals',
        'total_deposits',
        'opening_notes',
        'closing_notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'expected_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'total_cash' => 'decimal:2',
        'total_pix' => 'decimal:2',
        'total_credit_card' => 'decimal:2',
        'total_debit_card' => 'decimal:2',
        'total_other' => 'decimal:2',
        'total_withdrawals' => 'decimal:2',
        'total_deposits' => 'decimal:2',
    ];

    // ============================================
    // RELACIONAMENTOS
    // ============================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ============================================
    // ACCESSORS
    // ============================================

    public function getIsOpenAttribute(): bool
    {
        return $this->status === 'open';
    }

    public function getIsClosedAttribute(): bool
    {
        return $this->status === 'closed';
    }

    public function getTotalSalesAttribute(): float
    {
        return $this->total_cash + $this->total_pix + $this->total_credit_card + $this->total_debit_card + $this->total_other;
    }

    public function getFinalBalanceAttribute(): float
    {
        // Saldo final = abertura + vendas em dinheiro + reforços - sangrias
        return $this->opening_balance + $this->total_cash + $this->total_deposits - $this->total_withdrawals;
    }

    // ============================================
    // MÉTODOS DE CÁLCULO
    // ============================================

    /**
     * Calcula os totais baseado nos pedidos do caixa
     */
    public function calculateTotals(): void
    {
        $orders = $this->orders()
            ->where('payment_status', 'paid')
            ->get();

        $this->total_cash = $orders->where('payment_method', 'cash')->sum('total');
        $this->total_pix = $orders->where('payment_method', 'pix')->sum('total');
        $this->total_credit_card = $orders->where('payment_method', 'credit_card')->sum('total');
        $this->total_debit_card = $orders->where('payment_method', 'debit_card')->sum('total');
        $this->total_other = $orders->whereNotIn('payment_method', ['cash', 'pix', 'credit_card', 'debit_card'])->sum('total');

        $this->orders_count = $orders->count();
        $this->cancelled_count = $this->orders()->where('status', 'cancelled')->count();

        // Atualizar sangrias e reforços
        $this->total_withdrawals = $this->movements()->where('type', 'withdrawal')->sum('amount');
        $this->total_deposits = $this->movements()->where('type', 'deposit')->sum('amount');

        // Calcular saldo esperado (apenas dinheiro + reforços - sangrias)
        $this->expected_balance = $this->opening_balance + $this->total_cash + $this->total_deposits - $this->total_withdrawals;

        $this->save();
    }

    /**
     * Fecha o caixa com o valor declarado
     */
    public function close(float $closingBalance, ?string $notes = null): bool
    {
        if ($this->is_closed) {
            return false;
        }

        // Recalcular totais antes de fechar
        $this->calculateTotals();

        $this->closing_balance = $closingBalance;
        $this->difference = $closingBalance - $this->expected_balance; // Quebra de caixa
        $this->closed_at = now();
        $this->status = 'closed';
        $this->closing_notes = $notes;

        return $this->save();
    }

    /**
     * Reabre um caixa fechado (em caso de erro)
     */
    public function reopen(): bool
    {
        if ($this->is_open) {
            return false;
        }

        $this->status = 'open';
        $this->closed_at = null;
        $this->closing_balance = null;
        $this->difference = null;
        $this->closing_notes = null;

        return $this->save();
    }

    // ============================================
    // MÉTODOS ESTÁTICOS
    // ============================================

    /**
     * Busca o caixa aberto atual (se existir)
     */
    public static function currentOpen(?int $userId = null): ?self
    {
        $query = self::query()->open()->latest('opened_at');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->first();
    }

    /**
     * Verifica se há algum caixa aberto
     */
    public static function hasOpenRegister(?int $userId = null): bool
    {
        return self::currentOpen($userId) !== null;
    }

    /**
     * Abre um novo caixa
     */
    public static function openNew(int $userId, float $openingBalance = 0, ?string $notes = null): self
    {
        $user = User::findOrFail($userId);

        return self::create([
            'user_id' => $userId,
            'user_name' => $user->name,
            'opened_at' => now(),
            'status' => 'open',
            'opening_balance' => $openingBalance,
            'opening_notes' => $notes,
        ]);
    }
}
