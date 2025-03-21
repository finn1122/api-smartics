<?php

namespace App\Http\Controllers\Api\V1\ShopCategory;

use App\Http\Controllers\Api\V1\ShopProduct\ShopProductController;
use App\Http\Controllers\Controller;
use App\Http\Resources\ShopCategoryResource;
use App\Http\Resources\ShopProductResource;
use App\Models\Product;
use App\Models\ShopCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ShopCategoryController extends Controller
{
    private $shopProductController;
    public function __construct(ShopProductController $shopProductController)
    {
        $this->shopProductController = $shopProductController;
    }

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
     * Obtener todos los productos de una categoría, incluyendo el proveedor con el mejor precio.
     *
     * @param int $categoryId
     * @return JsonResponse
     */
    public function getProductsByCategory(int $categoryId): JsonResponse
    {
        // Buscar la categoría por su ID
        $category = ShopCategory::find($categoryId);

        if (!$category) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        // Obtener los productos de la categoría
        $products = $category->products()->with(['gallery'])->get();

        // Verificar si hay productos
        if ($products->isEmpty()) {
            return response()->json(['message' => 'No se encontraron productos para esta categoría'], 404);
        }

        // Iterar sobre cada producto para encontrar el proveedor con el mejor precio
        $productsWithBestSupplier = $products->map(function ($product) {
            // Obtener el proveedor más económico usando el método reutilizable
            $bestSupplierResponse = $this->shopProductController->getBestSupplierForProduct($product->id);

            Log::debug($bestSupplierResponse);

            // Si no hay datos de proveedores, continuar con valores nulos
            if (!$bestSupplierResponse) {
                // Crear el recurso del producto sin información del proveedor
                $productResource = new ShopProductResource($product);
                $productResource->additional(['bestPrice' => null]);
                return $productResource;
            }

            // Decodificar la respuesta JSON para obtener los datos del proveedor
            $bestSupplierData = json_decode($bestSupplierResponse->getContent(), true);

            // Crear el recurso del producto y agregar el proveedor
            $productResource = new ShopProductResource($product);
            $productResource->additional(['bestPrice' => $bestSupplierData]);

            return $productResource;
        });

        // Devolver la colección de productos con el proveedor más económico
        return response()->json($productsWithBestSupplier, 200);
    }
    /**
     * Obtiene todas las categorías de la tienda que están activas y tienen al menos un producto.
     *
     * Este método filtra las categorías de la tienda que están marcadas como activas (`active = true`)
     * y que tienen al menos un producto asociado. Luego, devuelve una respuesta JSON con las categorías
     * filtradas, utilizando el recurso `ShopCategoryResource` para formatear la salida.
     *
     * @return JsonResponse
     *
     * @example Respuesta exitosa:
     * [
     *     {
     *         "id": 1,
     *         "name": "Sillas gamer",
     *         "active": true,
     *         "productsCount": 7
     *     },
     *     {
     *         "id": 2,
     *         "name": "SSDs",
     *         "active": true,
     *         "productsCount": 1
     *     },
     *     {
     *         "id": 4,
     *         "name": "Tendencias",
     *         "active": true,
     *         "productsCount": 5
     *     }
     * ]
     */
    public function getAllShopCategories(): JsonResponse
    {
        Log::info('getAllShopCategories');

        // Paso 1: Obtener todas las categorías activas con el conteo de productos
        $shopCategories = ShopCategory::where('active', true)
            ->withCount('products') // Agregar el contador de productos
            ->get();

        // Paso 2: Filtrar las categorías que tienen al menos un producto
        $filteredCategories = $shopCategories->filter(function ($category) {
            return $category->products_count > 0; // Solo categorías con al menos un producto
        });

        // Paso 3: Devolver la respuesta usando ShopCategoryResource
        return response()->json(ShopCategoryResource::collection($filteredCategories), 200);
    }

}
