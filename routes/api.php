<?php

use App\Http\Controllers\Api\V1\User\UserController;
use App\Http\Controllers\Auth\JWTAuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ShopCategory\ShopCategoryController;
use App\Http\Controllers\Api\V1\Slider\SliderController;
use App\Http\Controllers\Api\V1\Tag\TagProductController;
use App\Http\Controllers\Api\V1\Tag\TagController;
use App\Http\Controllers\Api\V1\Category\CategoryController;

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

    // [[ SHOP CATEGORIES]]
    Route::prefix('shop-categories/')->group(function () {
        Route::get('top', [ShopCategoryController::class, 'getTopShopCategories']);
        Route::get('{path}', [ShopCategoryController::class, 'getShopCategoryByPath']);
        Route::get('{category_id}/products', [ShopCategoryController::class, 'getProductsByCategory']);
        Route::get('/', [ShopCategoryController::class, 'getAllShopCategories']);
        Route::get('products/search', [ShopCategoryController::class, 'searchProducts']);
    });

    // [[ CATEGORIES ]]
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


    // Grupo de rutas que requieren autenticación
    Route::middleware([JwtMiddleware::class])->group(function () {

        Route::post('logout', [JWTAuthController::class, 'logout']);
        // [User]
        Route::prefix('user/{user_id}')->group(function () {
            Route::get('/', [UserController::class, 'getUserById']);
        });
    });
});


