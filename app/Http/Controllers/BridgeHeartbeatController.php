<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BridgeHeartbeatController extends Controller
{
    /**
     * Recebe heartbeat do Bridge (a cada 30s)
     * Atualiza cache para o Monitor saber que está online
     */
    public function heartbeat(Request $request)
    {
        $tenantId = tenant('id');

        if (!$tenantId) {
            return response()->json([
                'error' => 'Tenant não identificado',
            ], 400);
        }

        // Armazenar timestamp do heartbeat
        Cache::put("bridge_heartbeat_{$tenantId}", now(), 300); // 5 minutos

        // Opcional: Armazenar info das impressoras
        if ($request->has('printers')) {
            Cache::put("bridge_printers_{$tenantId}", $request->printers, 300);
        }

        // Opcional: Armazenar versão do Bridge
        if ($request->has('version')) {
            Cache::put("bridge_version_{$tenantId}", $request->version, 3600);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Heartbeat recebido',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Status do Bridge (para o Monitor)
     */
    public function status()
    {
        $tenantId = tenant('id');
        $lastHeartbeat = Cache::get("bridge_heartbeat_{$tenantId}");

        if (!$lastHeartbeat) {
            return response()->json([
                'status' => 'offline',
                'message' => 'Bridge não conectado',
            ]);
        }

        $secondsAgo = now()->diffInSeconds($lastHeartbeat);

        if ($secondsAgo < 60) {
            return response()->json([
                'status' => 'online',
                'message' => 'Conectado há ' . $secondsAgo . 's',
                'last_heartbeat' => $lastHeartbeat->toISOString(),
            ]);
        }

        return response()->json([
            'status' => 'stale',
            'message' => 'Última conexão há ' . round($secondsAgo / 60) . ' minutos',
            'last_heartbeat' => $lastHeartbeat->toISOString(),
        ]);
    }
}
