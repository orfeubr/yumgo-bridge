<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Neighborhood extends Model
{
    protected $fillable = [
        'city',
        'name',
        'is_active',
        'delivery_fee',
        'delivery_time',
        'minimum_order',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'delivery_fee' => 'decimal:2',
        'minimum_order' => 'decimal:2',
    ];

    /**
     * Apenas bairros habilitados para delivery
     */
    public static function is_active()
    {
        return static::where('is_active', true)
            ->orderBy('order')
            ->orderBy('name');
    }

    /**
     * Bairros de uma cidade específica
     */
    public static function byCity(string $city)
    {
        return static::where('city', $city)
            ->orderBy('order')
            ->orderBy('name');
    }

    /**
     * Bairros habilitados de uma cidade
     */
    public static function is_activeByCity(string $city)
    {
        return static::where('city', $city)
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('name');
    }

    /**
     * Buscar taxa de um bairro específico
     */
    public static function getFeeByName(string $city, string $neighborhood): ?float
    {
        $record = static::where('city', $city)
            ->where('name', $neighborhood)
            ->where('is_active', true)
            ->first();

        return $record ? (float) $record->delivery_fee : null;
    }
}
