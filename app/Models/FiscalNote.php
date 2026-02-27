<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiscalNote extends Model
{
    protected $fillable = [
        'order_id',
        'tributaai_note_id',
        'note_number',
        'serie',
        'status',
        'chave_acesso',
        'protocolo',
        'pdf_url',
        'xml_url',
        'emission_date',
        'authorization_date',
        'cancellation_date',
        'error_message',
        'cancellation_reason',
        'raw_response',
        'total_value',
        'tax_value',
    ];

    protected $casts = [
        'emission_date' => 'datetime',
        'authorization_date' => 'datetime',
        'cancellation_date' => 'datetime',
        'raw_response' => 'array',
        'total_value' => 'decimal:2',
        'tax_value' => 'decimal:2',
    ];

    /**
     * Relacionamento com Order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope para notas autorizadas
     */
    public function scopeAuthorized($query)
    {
        return $query->where('status', 'authorized');
    }

    /**
     * Scope para notas canceladas
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope para notas com erro
     */
    public function scopeError($query)
    {
        return $query->where('status', 'error');
    }

    /**
     * Verifica se a nota foi autorizada
     */
    public function isAuthorized(): bool
    {
        return $this->status === 'authorized';
    }

    /**
     * Verifica se a nota foi cancelada
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Verifica se a nota teve erro
     */
    public function hasError(): bool
    {
        return $this->status === 'error' || $this->status === 'rejected';
    }
}
