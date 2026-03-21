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
        'access_token',
        'token_generated_at',
        'last_access_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'token_generated_at' => 'datetime',
        'last_access_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Gerar token automaticamente ao criar
        static::creating(function ($driver) {
            if (empty($driver->access_token)) {
                $driver->access_token = bin2hex(random_bytes(32));
                $driver->token_generated_at = now();
            }
        });
    }

    /**
     * Regenera o token de acesso
     */
    public function regenerateToken(): void
    {
        $this->access_token = bin2hex(random_bytes(32));
        $this->token_generated_at = now();
        $this->save();
    }

    /**
     * Atualiza último acesso
     */
    public function recordAccess(): void
    {
        $this->last_access_at = now();
        $this->save();
    }

    /**
     * URL completa de acesso
     */
    public function getAccessUrlAttribute(): string
    {
        return url("/entregador/{$this->access_token}");
    }

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
