<?php

use App\Http\Controllers\Api\V1\User\UserController;
use App\Http\Controllers\Auth\JWTAuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return response()->json([
        'message' => 'Bienvenido a la API de Bakery',
        'version' => '1.0',
        'documentation' => url('/api/documentation') // Cambia esto si tienes documentación
    ]);
});

//Route::post('register', [RegisterController::class, 'register']);


Route::prefix('v1')->namespace('App\Http\Controllers\Api\V1')->group(function () {
    // Rutas públicas que no requieren autenticación
    Route::post('login', [JWTAuthController::class, 'login']);
    Route::post('register', [RegisterController::class, 'register']);
    Route::get('/email/verify/{id}/{hash}', [RegisterController::class, 'verifyEmail'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [RegisterController::class, 'resendVerification'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');

    // Grupo de rutas que requieren autenticación
    Route::middleware([JwtMiddleware::class])->group(function () {

        Route::post('logout', [JWTAuthController::class, 'logout']);
        // [User]
        Route::prefix('user/{user_id}')->group(function () {
            Route::get('/', [UserController::class, 'getUserById']);
        });

    });
});


