<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central API Routes
|--------------------------------------------------------------------------
|
| Estas rotas são para a aplicação central (sem tenancy)
| As rotas de API dos tenants estão em routes/tenant.php
|
*/

// Rotas centrais (se necessário)
// Exemplo: webhook de pagamento, health check, etc.

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Broadcasting authentication (Reverb/Pusher) - Temporário simplificado
Route::post('/broadcasting/auth', function (\Illuminate\Http\Request $request) {
    try {
        // Validar token Sanctum manualmente
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Unauthorized - No token'], 401);
        }

        // Por enquanto, apenas retornar sucesso
        // TODO: Implementar autenticação real do canal
        $channelName = $request->input('channel_name');
        $socketId = $request->input('socket_id');

        return response()->json([
            'auth' => 'authorized',  // Placeholder
            'channel_data' => null
        ]);

    } catch (\Exception $e) {
        \Log::error('Broadcasting auth error: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
