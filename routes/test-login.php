<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/test-login', function () {
    return view('test-login');
});

Route::post('/test-login', function (Request $request) {
    $credentials = [
        'email' => $request->email,
        'password' => $request->password,
    ];
    
    if (Auth::attempt($credentials)) {
        return response()->json([
            'success' => true,
            'message' => 'Login OK!',
            'user' => Auth::user()->name,
            'email' => Auth::user()->email,
        ]);
    }
    
    return response()->json([
        'success' => false,
        'message' => 'Credenciais incorretas',
        'attempted_email' => $request->email,
    ], 401);
});
