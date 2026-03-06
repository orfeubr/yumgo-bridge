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

// Broadcasting authentication
Route::post('/broadcasting/auth', function (\Illuminate\Http\Request $request) {
    return response()->json(['message' => 'Unauthorized'], 401);
})->middleware('auth:sanctum');
