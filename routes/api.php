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

// Broadcasting authentication (Reverb/Pusher)
Route::post('/broadcasting/auth', function (\Illuminate\Http\Request $request) {
    try {
        // Validar token Sanctum manualmente
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Unauthorized - No token'], 401);
        }

        $channelName = $request->input('channel_name');
        $socketId = $request->input('socket_id');

        // Validar que o usuário pode acessar o canal
        // Por enquanto, apenas validar que o token é válido
        // TODO: Adicionar validação de permissões do canal

        // Gerar assinatura Pusher
        $appKey = config('broadcasting.connections.reverb.key');
        $appSecret = config('broadcasting.connections.reverb.secret');

        // Pusher auth format: socket_id:channel_name
        $stringToSign = $socketId . ':' . $channelName;
        $signature = hash_hmac('sha256', $stringToSign, $appSecret);

        // Auth string format: app_key:signature
        $authString = $appKey . ':' . $signature;

        return response()->json([
            'auth' => $authString
        ]);

    } catch (\Exception $e) {
        \Log::error('Broadcasting auth error: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
