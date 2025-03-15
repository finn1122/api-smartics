<?php

namespace App\Http\Controllers\Api\V1\ShopCategory;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShopCategoryResource;
use App\Http\Resources\ShopProductResource;
use App\Models\ShopCategory;
use Illuminate\Http\JsonResponse;
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
    /**
     * Obtener una categoría de la tienda por su path.
     *
     * Este método busca una categoría de la tienda por su path y devuelve los detalles de la categoría,
     * incluyendo el número de productos asociados. Solo se devuelven categorías activas.
     *
     * @param string $path El path único de la categoría que se desea obtener.
     * @return JsonResponse
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "Headphones",
     *     "description": "Categoría de auriculares",
     *     "image_url": "https://example.com/headphones.jpg",
     *     "path": "headphones",
     *     "top": true,
     *     "active": true,
     *     "products_count": 10
     *   }
     * }
     * @response 404 {
     *   "message": "Categoría no encontrada"
     * }
     */
    public function getShopCategoryByPath(string $path): JsonResponse
    {
        // Registrar en el log la solicitud con el path proporcionado
        Log::info('getShopCategoryByPath', ['path' => $path]);

        // Buscar la categoría por su path, asegurándose de que esté activa
        $shopCategory = ShopCategory::where('path', $path)
            ->where('active', true) // Solo categorías activas
            ->withCount('products') // Contar los productos asociados
            ->first();

        // Verificar si se encontró la categoría
        if (!$shopCategory) {
            // Si no se encuentra la categoría, devolver un error 404 con un mensaje
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        // Devolver la respuesta usando ShopCategoryResource para formatear los datos
        return response()->json(new ShopCategoryResource($shopCategory), 200);
    }
    /**
     * Obtener los productos de una categoría específica.
     *
     * @param int $categoryId
     * @return JsonResponse
     */
    public function getProductsByCategory(int $categoryId): JsonResponse
    {
        Log::info('getProductsByCategory', ['categoryId' => $categoryId]);

        // Buscar la categoría por su ID
        $category = ShopCategory::find($categoryId);

        // Verificar si la categoría existe
        if (!$category) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        // Obtener los productos asociados a la categoría
        $products = $category->products;



        // Devolver los productos usando ProductResource
        return response()->json(ShopProductResource::collection($products), 200);
    }
}
