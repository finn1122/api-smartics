<?php

use App\Http\Controllers\Api\V1\User\UserController;
use App\Http\Controllers\Auth\JWTAuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Middleware\HandleCart;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Slider\SliderController;
use App\Http\Controllers\Api\V1\Tag\TagProductController;
use App\Http\Controllers\Api\V1\Tag\TagController;
use App\Http\Controllers\Api\V1\Category\CategoryController;
use App\Http\Controllers\Api\V1\ShopProduct\ShopProductController;
use App\Http\Controllers\Api\V1\Cart\CartItemController;
use App\Http\Controllers\Api\V1\Cart\CartController;


Route::get('/', function () {
    return response()->json([
        'message' => 'Bienvenido a la API',
        'version' => '1.0',
        'documentation' => url('/api/documentation')
    ]);
});

Route::prefix('v1')->group(function () {
    // Rutas públicas
    Route::post('login', [JWTAuthController::class, 'login']);
    Route::post('register', [RegisterController::class, 'register']);
    Route::get('/email/verify/{id}/{hash}', [RegisterController::class, 'verifyEmail'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [RegisterController::class, 'resendVerification'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');

    // Rutas de catálogo público
    Route::prefix('categories')->group(function() {
        Route::get('/', [CategoryController::class, 'getCategoriesHierarchy']);
        Route::get('/top', [CategoryController::class, 'getTopCategories']);
        Route::get('/{categoryId}/subcategories', [CategoryController::class, 'getSubcategories']);
        Route::get('/by-path/{path}', [CategoryController::class, 'getCategoryByPath']);
        Route::get('{categoryId}/products', [CategoryController::class, 'getProductsByCategoryId']);
    });

    Route::prefix('tags')->group(function () {
        Route::get('{tagId}/products', [TagProductController::class, 'getProductsByTag']);
        Route::get('/', [TagController::class, 'getActiveTagsWithValidProductsCount']);
    });

    Route::prefix('sliders')->group(function () {
        Route::get('/', [SliderController::class, 'getAllActiveSliders']);
    });

    Route::prefix('product')->group(function() {
        Route::get('/', [ShopProductController::class, 'getProductByPath']);
        Route::get('/{product_id}/best-price', [ShopProductController::class, 'getBestPriceData']);
    });

    // Este endpoint estará accesible tanto con sesión (invitado) como con JWT
    Route::get('cart', [CartController::class, 'getActiveCart'])->middleware(HandleCart::class);

    // Rutas protegidas por JWT
    Route::middleware(JwtMiddleware::class)->group(function () {
        Route::post('logout', [JWTAuthController::class, 'logout']);

        Route::prefix('user/{user_id}')->group(function () {
            Route::get('/', [UserController::class, 'getUserById']);
        });

        Route::prefix('cart')->middleware(HandleCart::class)->group(function () {
            Route::post('/items', [CartItemController::class, 'store']);
            Route::put('/items/{item}', [CartItemController::class, 'update']);
            Route::delete('/items/{item}', [CartItemController::class, 'destroy']);

            Route::get('/saved', [CartController::class, 'listSavedCarts']);
            Route::post('/save', [CartController::class, 'saveCart']);
            Route::post('/{cart}/activate', [CartController::class, 'activateCart']);
            Route::post('/{cart}/share', [CartController::class, 'shareCart']);
        });

    });

    // Rutas de carrito para invitados
    Route::prefix('guest-cart')->middleware(HandleCart::class)->group(function () {
        Route::get('/', [CartController::class, 'getActiveCart']);

        Route::post('/items', [CartItemController::class, 'storeGuestCart']);
        Route::put('/items/{item}', [CartItemController::class, 'updateGuestCart']);
        Route::delete('/items/{item}', [CartItemController::class, 'destroy']);
    });

    // Rutas públicas para carritos compartidos
    Route::prefix('shared-cart')->group(function () {
        Route::get('/{token}', [CartController::class, 'getSharedCart']);
        Route::post('/{token}/clone', [CartController::class, 'cloneSharedCart']);
    });
});

