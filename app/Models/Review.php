<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'order_id',
        'customer_id',
        'rating',
        'comment',
        'food_rating',
        'delivery_rating',
        'service_rating',
        'is_public',
        'response',
        'responded_at',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'responded_at' => 'datetime',
    ];

    /**
     * Pedido da avaliação
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Cliente que avaliou
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Scope públicas
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope com resposta
     */
    public function scopeWithResponse($query)
    {
        return $query->whereNotNull('response');
    }

    /**
     * Scope sem resposta
     */
    public function scopeWithoutResponse($query)
    {
        return $query->whereNull('response');
    }

    /**
     * Scope por rating mínimo
     */
    public function scopeMinRating($query, int $rating)
    {
        return $query->where('rating', '>=', $rating);
    }

    /**
     * Verifica se tem resposta
     */
    public function hasResponse(): bool
    {
        return !empty($this->response);
    }

    /**
     * Média geral
     */
    public function getAverageRatingAttribute(): float
    {
        $ratings = array_filter([
            $this->food_rating,
            $this->delivery_rating,
            $this->service_rating,
        ]);

        if (empty($ratings)) {
            return $this->rating;
        }

        return round(array_sum($ratings) / count($ratings), 1);
    }
}
