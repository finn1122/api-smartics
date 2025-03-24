<?php

namespace App\Http\Controllers\Api\V1\ShopCategory;

use App\Http\Controllers\Api\V1\ShopProduct\ShopProductController;
use App\Http\Controllers\Controller;
use App\Http\Resources\ShopCategoryResource;
use App\Http\Resources\ShopProductResource;
use App\Models\Product;
use App\Models\ShopCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        Log::info('getProductsByCategory', ['categoryId' => $categoryId]);

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

            // Si no hay datos de proveedores, retornar null
            if (!$bestSupplierResponse) {
                return null;
            }

            // Decodificar la respuesta JSON para obtener los datos del proveedor
            $bestSupplierData = json_decode($bestSupplierResponse->getContent(), true);

            // Crear el recurso del producto y agregar el proveedor
            $productResource = new ShopProductResource($product);
            $productResource->additional(['bestPrice' => $bestSupplierData]);

            return $productResource;
        });

        // Filtrar productos que no tienen un bestPrice válido
        $filteredProducts = $productsWithBestSupplier->filter(function ($productResource) {
            return $productResource !== null && $productResource->additional['bestPrice'] !== null;
        });

        // Verificar si hay productos después de filtrar
        if ($filteredProducts->isEmpty()) {
            return response()->json(['message' => 'No hay productos disponibles con precios válidos'], 404);
        }

        // Devolver la colección de productos con el proveedor más económico
        return response()->json($filteredProducts->values(), 200);
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

        // Paso 1: Obtener todas las categorías activas con el conteo de productos que tienen precios válidos
        $shopCategories = ShopCategory::where('active', true)
            ->withCount(['products' => function ($query) {
                // Subconsulta para contar solo productos con precios válidos
                $query->whereHas('externalProductData', function ($subQuery) {
                    $subQuery->where('price', '>', 0)
                        ->where('sale_price', '>', 0)
                        ->where('new_sale_price', '>', 0);
                });
            }])
            ->get();

        // Paso 2: Filtrar las categorías que tienen al menos un producto con precios válidos
        $filteredCategories = $shopCategories->filter(function ($category) {
            return $category->products_count > 0; // Solo categorías con al menos un producto válido
        });

        // Paso 3: Devolver la respuesta usando ShopCategoryResource
        return response()->json(ShopCategoryResource::collection($filteredCategories), 200);
    }
    /**
     * Busca productos por categoría (path), término de búsqueda o ambos.
     *
     * @OA\Get(
     *     path="/shop-categories/products/search",
     *     tags={"Productos"},
     *     summary="Buscar productos",
     *     description="Permite buscar productos por categoría, término de búsqueda o ambos criterios combinados",
     *     @OA\Parameter(
     *         name="path",
     *         in="query",
     *         description="Path único de la categoría (ej: 'sillas-gamer')",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="search_term",
     *         in="query",
     *         description="Término para buscar en nombre, SKU o clave CVA",
     *         required=false,
     *         @OA\Schema(type="string", minLength=2)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Listado de productos encontrados",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ProductResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Se requiere al menos un parámetro",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Se requiere al menos un parámetro (path o search_term)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Categoría no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Categoría no encontrada")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Si la categoría no existe
     * @throws \Exception Si ocurre un error inesperado
     *
     * Ejemplos de uso:
     * 1. Búsqueda por categoría: GET /shop-categories/products/search?path=sillas-gamer
     * 2. Búsqueda por término: GET /shop-categories/products/search?search_term=teclado
     * 3. Búsqueda combinada: GET /shop-categories/products/search?path=sillas-gamer&search_term=ergonómica
     *
     * Estructura de respuesta exitosa:
     * [
     *    {
     *        "id": 1,
     *        "name": "Producto Ejemplo",
     *        // ... otros campos del producto
     *        "bestPrice": {
     *            "supplierId": 5,
     *            "price": 99.99
     *            // ... datos del proveedor
     *        }
     *    }
     * ]
     */
    public function searchProducts(Request $request): JsonResponse
    {
        Log::info('searchProducts');
        Log::debug($request);

        $request->validate([
            'path' => 'nullable|string|exists:shop_categories,path',
            'search_term' => 'nullable|string|min:2'
        ]);

        // Validar que al menos un parámetro esté presente
        if (!$request->path && !$request->search_term) {
            return response()->json(['message' => 'Se requiere al menos un parámetro (path o search_term)'], 400);
        }

        // Iniciar query
        $query = Product::query()->with(['gallery']);

        // Filtrar por categoría si existe
        if ($request->path) {
            $category = ShopCategory::where('path', $request->path)->firstOrFail();

            $query->join('shop_category_products', 'products.id', '=', 'shop_category_products.product_id')
                ->where('shop_category_products.category_id', $category->id);
        }

        // Filtrar por término de búsqueda si existe
        if ($request->search_term) {
            $searchTerm = $request->input('search_term');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('sku', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('cva_key', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Obtener y procesar productos
        $products = $query->get();

        $productsWithBestSupplier = $products->map(function ($product) {
            $bestSupplierResponse = $this->shopProductController->getBestSupplierForProduct($product->id);

            if (!$bestSupplierResponse) {
                return null;
            }

            $bestSupplierData = json_decode($bestSupplierResponse->getContent(), true);
            return (new ShopProductResource($product))->additional(['bestPrice' => $bestSupplierData]);
        });

        $filteredProducts = $productsWithBestSupplier->filter()->values();

        return response()->json($filteredProducts, 200);
    }
}
