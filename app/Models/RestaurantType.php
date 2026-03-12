<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantType extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relacionamento: Um tipo tem vários restaurantes
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'restaurant_type_id');
    }

    /**
     * Scope: Apenas tipos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Ordenados
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Templates de categorias para este tipo de restaurante
     */
    public function getCategoryTemplates(): array
    {
        return config("category-templates.{$this->slug}", []);
    }
}
