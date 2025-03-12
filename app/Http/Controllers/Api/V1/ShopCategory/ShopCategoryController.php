<?php

namespace App\Http\Controllers\Api\V1\ShopCategory;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShopCategoryResource;
use App\Models\ShopCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopCategoryController extends Controller
{
    /**
     * Obtener las categorías donde top y active son true.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getTopShopCategories():JsonResponse
    {
        Log::info('getTopShopCategories');
        // Filtrar las categorías donde top y active son true
        $shopCategories = ShopCategory::where('top', true)
            ->where('active', true)
            ->withCount('products') // Agregar el contador de productos
            ->get();

        // Devolver la respuesta usando ShopCategoryResource
        return response()->json(ShopCategoryResource::collection($shopCategories), 200);
    }
}
