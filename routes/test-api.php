<?php

use Illuminate\Support\Facades\Route;

// Rota de teste SEM autenticação
Route::get('/test-no-auth', function () {
    try {
        return response()->json([
            'success' => true,
            'message' => 'API funcionando sem autenticação!',
            'timestamp' => now()->toDateTimeString(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});

// Rota de teste COM autenticação Sanctum
Route::middleware(['auth:sanctum'])->get('/test-with-auth', function () {
    try {
        return response()->json([
            'success' => true,
            'message' => 'API funcionando COM autenticação!',
            'user' => auth()->user()?->name,
            'timestamp' => now()->toDateTimeString(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});
