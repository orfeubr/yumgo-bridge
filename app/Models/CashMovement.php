<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_register_id',
        'user_id',
        'user_name',
        'type',
        'amount',
        'reason',
        'notes',
        'receipt_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // ============================================
    // RELACIONAMENTOS
    // ============================================

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopeWithdrawals($query)
    {
        return $query->where('type', 'withdrawal');
    }

    public function scopeDeposits($query)
    {
        return $query->where('type', 'deposit');
    }

    public function scopeForCashRegister($query, $cashRegisterId)
    {
        return $query->where('cash_register_id', $cashRegisterId);
    }

    // ============================================
    // ACCESSORS
    // ============================================

    public function getIsWithdrawalAttribute(): bool
    {
        return $this->type === 'withdrawal';
    }

    public function getIsDepositAttribute(): bool
    {
        return $this->type === 'deposit';
    }

    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'withdrawal' => 'Sangria',
            'deposit' => 'Reforço',
            default => 'Desconhecido',
        };
    }

    // ============================================
    // MÉTODOS ESTÁTICOS
    // ============================================

    /**
     * Registra uma sangria (retirada de dinheiro)
     */
    public static function withdraw(
        int $cashRegisterId,
        int $userId,
        float $amount,
        string $reason,
        ?string $notes = null,
        ?string $receiptPath = null
    ): self {
        $user = User::findOrFail($userId);

        $movement = self::create([
            'cash_register_id' => $cashRegisterId,
            'user_id' => $userId,
            'user_name' => $user->name,
            'type' => 'withdrawal',
            'amount' => $amount,
            'reason' => $reason,
            'notes' => $notes,
            'receipt_path' => $receiptPath,
        ]);

        // Recalcular totais do caixa
        $movement->cashRegister->calculateTotals();

        return $movement;
    }

    /**
     * Registra um reforço (adição de dinheiro)
     */
    public static function deposit(
        int $cashRegisterId,
        int $userId,
        float $amount,
        string $reason,
        ?string $notes = null,
        ?string $receiptPath = null
    ): self {
        $user = User::findOrFail($userId);

        $movement = self::create([
            'cash_register_id' => $cashRegisterId,
            'user_id' => $userId,
            'user_name' => $user->name,
            'type' => 'deposit',
            'amount' => $amount,
            'reason' => $reason,
            'notes' => $notes,
            'receipt_path' => $receiptPath,
        ]);

        // Recalcular totais do caixa
        $movement->cashRegister->calculateTotals();

        return $movement;
    }
}
