<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variation_id',
        'product_name',
        'quantity',
        'unit_price',
        'subtotal',
        'addons',
        'half_and_half',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'addons' => 'array',
        'half_and_half' => 'array',
    ];

    /**
     * Pedido do item
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Produto do item
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Variação do produto
     */
    public function productVariation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class);
    }

    /**
     * Calcula total com adicionais
     */
    public function getTotalWithAddonsAttribute(): float
    {
        $total = $this->subtotal;

        if ($this->addons) {
            foreach ($this->addons as $addon) {
                $total += ($addon['price'] ?? 0) * ($addon['quantity'] ?? 1);
            }
        }

        return $total;
    }

    /**
     * Verifica se é meio a meio
     */
    public function isHalfAndHalf(): bool
    {
        return !empty($this->half_and_half);
    }
}
