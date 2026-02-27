<?php

use Illuminate\Support\Facades\Route;

// Teste 1: Sem auth, sem banco
Route::get('/test-no-auth', function() {
    return response()->json(['status' => 'OK', 'test' => 'no-auth']);
});

// Teste 2: Com auth, sem acessar customer
Route::middleware(['auth:sanctum'])->get('/test-with-auth-simple', function(\Illuminate\Http\Request $request) {
    return response()->json(['status' => 'OK', 'test' => 'with-auth-simple']);
});

// Teste 3: Com auth, acessa customer
Route::middleware(['auth:sanctum'])->get('/test-with-auth-customer', function(\Illuminate\Http\Request $request) {
    $user = $request->user();
    return response()->json([
        'status' => 'OK',
        'test' => 'with-auth-customer',
        'user_id' => $user ? $user->id : null
    ]);
});

// Teste 4: Com auth, acessa customer->name
Route::middleware(['auth:sanctum'])->get('/test-with-auth-full', function(\Illuminate\Http\Request $request) {
    $user = $request->user();
    return response()->json([
        'status' => 'OK',
        'test' => 'with-auth-full',
        'user_id' => $user ? $user->id : null,
        'user_name' => $user ? $user->name : null
    ]);
});
