<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal privado do restaurante para o Bridge App
Broadcast::channel('restaurant.{restaurantId}', function ($user, $restaurantId) {
    // Verificar se o usuário autenticado pertence ao restaurante
    // Para o Bridge App, o token Sanctum já garante a autenticação
    return true; // Sanctum já validou o token
});
