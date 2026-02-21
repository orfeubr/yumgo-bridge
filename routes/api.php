<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CashbackController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Tenant-aware)
|--------------------------------------------------------------------------
|
| Estas rotas são executadas dentro do contexto do tenant
| Todas as requisições devem incluir o tenant identifier (subdomain ou domain)
|
*/

// Rotas públicas (sem autenticação)
Route::prefix('v1')->group(function () {
    // Autenticação
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    
    // Categorias (público)
    Route::get('/categories', [CategoryController::class, 'index']);
    
    // Produtos (público)
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/products/category/{categoryId}', [ProductController::class, 'byCategory']);
    Route::get('/products/featured', [ProductController::class, 'featured']);
});

// Rotas protegidas (requerem autenticação)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Autenticação
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'updateProfile']);
    
    // Pedidos
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::get('/orders/{id}/track', [OrderController::class, 'track']);
    
    // Cashback
    Route::get('/cashback/balance', [CashbackController::class, 'balance']);
    Route::get('/cashback/transactions', [CashbackController::class, 'transactions']);
    Route::get('/cashback/settings', [CashbackController::class, 'settings']);
    
    // Cliente
    Route::get('/profile', [CustomerController::class, 'show']);
    Route::put('/profile', [CustomerController::class, 'update']);
    Route::get('/addresses', [CustomerController::class, 'addresses']);
    Route::post('/addresses', [CustomerController::class, 'createAddress']);
    Route::put('/addresses/{id}', [CustomerController::class, 'updateAddress']);
    Route::delete('/addresses/{id}', [CustomerController::class, 'deleteAddress']);
});
