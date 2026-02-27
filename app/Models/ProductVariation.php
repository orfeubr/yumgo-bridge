<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariation extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'price_modifier',
        'modifier_type',
        'serves',
        'description',
        'is_active',
        'order',
    ];

    protected $casts = [
        'price_modifier' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $appends = ['price'];

    /**
     * Produto desta variação
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope para variações ativas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordenado
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    /**
     * Calcula preço final com modificador
     */
    public function getFinalPrice(float $basePrice): float
    {
        if ($this->modifier_type === 'percentage') {
            return $basePrice + (($basePrice * $this->price_modifier) / 100);
        }

        return $basePrice + $this->price_modifier;
    }

    /**
     * Accessor para calcular preço automaticamente
     * Usado pelas views que acessam $variation->price
     */
    public function getPriceAttribute(): float
    {
        // Tentar pegar do relacionamento carregado
        $product = $this->relationLoaded('product') ? $this->product : null;

        // Se não estiver carregado, buscar do banco
        if (!$product) {
            $product = Product::find($this->product_id);
        }

        if (!$product) {
            return 0;
        }

        $basePrice = $product->price ?? 0;

        if ($this->modifier_type === 'percentage') {
            return $basePrice + (($basePrice * $this->price_modifier) / 100);
        }

        return $basePrice + $this->price_modifier;
    }
}
