<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\TouristController;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('home');

Route::get('login', function () {
    return Inertia::render('Auth/Login');
})->name('login');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/tourist', function () {
    return Inertia::render('tourist/index'); 
})->name('tourist.index');

// En routes/web.php o routes/api.php
Route::get('/tourists', [App\Http\Controllers\TouristController::class, 'index']);
Route::post('/tourists', [App\Http\Controllers\TouristController::class, 'store']);
Route::put('/tourists/{id}', [App\Http\Controllers\TouristController::class, 'update']);
Route::delete('/tourists/{id}', [App\Http\Controllers\TouristController::class, 'destroy']);

Route::get('/tourist/maps', function () {
    return Inertia::render('tourist/maps');
});
Route::get('/tourist/MapComponent', function () {
    return Inertia::render('tourist/MapComponent');
});
Route::get('/tourist/distance', function () {
    return Inertia::render('tourist/distance');
});
// Cargar rutas adicionales
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';