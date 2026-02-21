<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'image',
        'images',
        'pizza_config',
        'marmitex_config',
        'has_stock_control',
        'stock_quantity',
        'min_stock_alert',
        'preparation_time',
        'is_active',
        'is_featured',
        'order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'images' => 'array',
        'pizza_config' => 'array',
        'marmitex_config' => 'array',
        'has_stock_control' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Categoria do produto
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Variações do produto
     */
    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }

    /**
     * Adicionais do produto
     */
    public function addons(): HasMany
    {
        return $this->hasMany(ProductAddon::class);
    }

    /**
     * Itens de pedidos
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope para produtos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para produtos em destaque
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope para produtos com estoque
     */
    public function scopeInStock($query)
    {
        return $query->where(function($q) {
            $q->where('has_stock_control', false)
              ->orWhere('stock_quantity', '>', 0);
        });
    }

    /**
     * Scope ordenado
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    /**
     * Verifica se é pizza
     */
    public function isPizza(): bool
    {
        return !empty($this->pizza_config);
    }

    /**
     * Verifica se é marmitex
     */
    public function isMarmitex(): bool
    {
        return !empty($this->marmitex_config);
    }

    /**
     * Verifica se tem estoque disponível
     */
    public function hasStock(int $quantity = 1): bool
    {
        if (!$this->has_stock_control) {
            return true;
        }

        return $this->stock_quantity >= $quantity;
    }

    /**
     * Decrementa estoque
     */
    public function decrementStock(int $quantity = 1): void
    {
        if ($this->has_stock_control) {
            $this->decrement('stock_quantity', $quantity);
        }
    }

    /**
     * Incrementa estoque
     */
    public function incrementStock(int $quantity = 1): void
    {
        if ($this->has_stock_control) {
            $this->increment('stock_quantity', $quantity);
        }
    }
}
