<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\WeeklyMenu;

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
        'filling',
        'pizza_config',
        'marmitex_config',
        'has_stock_control',
        'stock_quantity',
        'min_stock_alert',
        'preparation_time',
        'is_active',
        'is_featured',
        'is_pizza',
        'allows_half_and_half',
        'available_sizes',
        'available_borders',
        'size_prices',
        'border_prices',
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
        'is_pizza' => 'boolean',
        'allows_half_and_half' => 'boolean',
        'available_sizes' => 'array',
        'available_borders' => 'array',
        'size_prices' => 'array',
        'border_prices' => 'array',
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
     * Scope para produtos no cardápio semanal de hoje
     */
    public function scopeInTodaysMenu($query)
    {
        $activeMenu = WeeklyMenu::getActive();

        if (!$activeMenu) {
            return $query; // Sem filtro se não há cardápio ativo
        }

        $today = WeeklyMenu::getCurrentDayOfWeek();
        $todayProductIds = $activeMenu->items()
            ->where('day_of_week', $today)
            ->where('is_available', true)
            ->pluck('product_id')
            ->toArray();

        return $query->whereIn('id', $todayProductIds);
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
