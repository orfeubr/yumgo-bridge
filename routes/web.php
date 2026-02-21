<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-deliverypro', function () {
    return response()->json([
        'project' => 'DeliveryPro',
        'app_name' => config('app.name'),
        'path' => base_path(),
        'database' => config('database.default'),
        'packages' => [
            'filament' => class_exists('Filament\Filament') ? 'installed' : 'not installed',
            'tenancy' => class_exists('Stancl\Tenancy\Tenancy') ? 'installed' : 'not installed',
        ],
    ]);
});
