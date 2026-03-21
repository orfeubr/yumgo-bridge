<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryDriver extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'phone',
        'email',
        'cpf',
        'vehicle_type',
        'vehicle_plate',
        'photo',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'driver_id');
    }

    public function getActiveDeliveriesCountAttribute(): int
    {
        return $this->deliveries()
            ->whereHas('order', function ($query) {
                $query->where('status', 'out_for_delivery');
            })
            ->count();
    }
}
