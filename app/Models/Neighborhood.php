<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Neighborhood extends Model
{
    protected $fillable = [
        'city',
        'name',
        'enabled',
        'delivery_fee',
        'delivery_time',
        'minimum_order',
        'order',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'delivery_fee' => 'decimal:2',
        'minimum_order' => 'decimal:2',
    ];

    /**
     * Apenas bairros habilitados para delivery
     */
    public static function enabled()
    {
        return static::where('enabled', true)
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
    public static function enabledByCity(string $city)
    {
        return static::where('city', $city)
            ->where('enabled', true)
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
            ->where('enabled', true)
            ->first();

        return $record ? (float) $record->delivery_fee : null;
    }
}
