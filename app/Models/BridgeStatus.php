<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BridgeStatus extends Model
{
    protected $table = 'bridge_status';

    protected $fillable = [
        'last_heartbeat',
        'version',
        'printers',
        'status',
    ];

    protected $casts = [
        'printers' => 'array',
        'last_heartbeat' => 'datetime',
    ];

    /**
     * Marca Bridge como online
     */
    public static function markOnline(string $version, array $printers = []): self
    {
        $bridge = self::firstOrNew([]);
        $bridge->last_heartbeat = now();
        $bridge->version = $version;
        $bridge->printers = $printers;
        $bridge->status = 'online';
        $bridge->save();

        return $bridge;
    }

    /**
     * Marca Bridge como offline
     */
    public static function markOffline(): void
    {
        $bridge = self::first();
        if ($bridge) {
            $bridge->status = 'offline';
            $bridge->save();
        }
    }

    /**
     * Verifica se Bridge está online (heartbeat recente)
     */
    public function isOnline(): bool
    {
        if ($this->status === 'offline') {
            return false;
        }

        // Se último heartbeat foi há mais de 2 minutos, considera offline
        if ($this->last_heartbeat && $this->last_heartbeat->diffInMinutes(now()) > 2) {
            return false;
        }

        return true;
    }

    /**
     * Retorna tempo desde último heartbeat (formato humano)
     */
    public function getLastSeenAttribute(): ?string
    {
        if (!$this->last_heartbeat) {
            return null;
        }

        return $this->last_heartbeat->diffForHumans();
    }
}
