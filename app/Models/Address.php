<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'customer_id',
        'label',
        'city',
        'neighborhood',
        'street',
        'number',
        'complement',
        'zipcode',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Verifica se este endereço está na área de entrega do restaurante atual
     */
    public function isInDeliveryArea(): bool
    {
        $neighborhood = Neighborhood::where('city', $this->city)
            ->where('name', $this->neighborhood)
            ->where('is_active', true)
            ->first();

        return $neighborhood !== null;
    }

    /**
     * Busca informações de entrega (taxa, tempo)
     */
    public function getDeliveryInfo(): ?object
    {
        $neighborhood = Neighborhood::where('city', $this->city)
            ->where('name', $this->neighborhood)
            ->where('is_active', true)
            ->first();

        if (!$neighborhood) {
            return null;
        }

        return (object) [
            'available' => true,
            'fee' => (float) $neighborhood->delivery_fee,
            'time' => $neighborhood->delivery_time,
            'minimum_order' => $neighborhood->minimum_order ? (float) $neighborhood->minimum_order : null,
        ];
    }

    /**
     * Scope: Apenas endereços na área de entrega
     */
    public function scopeInDeliveryArea($query)
    {
        return $query->whereExists(function ($q) {
            $q->select(\DB::raw(1))
              ->from('neighborhoods')
              ->whereColumn('neighborhoods.city', 'addresses.city')
              ->whereColumn('neighborhoods.name', 'addresses.neighborhood')
              ->where('neighborhoods.is_active', true);
        });
    }
}
