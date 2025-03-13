<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClientsController;
use App\Http\Controllers\GoogleMapsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Clients API Resource Routes
Route::apiResource('clients', ClientsController::class);

// If you need specific routes or custom behavior, you can define them like this:
// Route::get('clients', [ClientsController::class, 'index']);
// Route::post('clients', [ClientsController::class, 'store']);
// Route::get('clients/{id}', [ClientsController::class, 'show']);
// Route::put('clients/{id}', [ClientsController::class, 'update']);
// Route::delete('clients/{id}', [ClientsController::class, 'destroy']);


Route::get('test', function() {
    return response()->json(['message' => 'API funcionando correctamente']);
});

Route::get('/google-maps-key', 'App\Http\Controllers\GoogleMapsController@getApiKey');



Route::get('/google-maps-key', [GoogleMapsController::class, 'getApiKey']);
