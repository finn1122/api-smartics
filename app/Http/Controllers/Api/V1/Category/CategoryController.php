<?php

namespace App\Http\Controllers\Api\V1\Category;

use App\Http\Controllers\Api\V1\ShopProduct\ShopProductController;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ShopProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    private $shopProductController;
    public function __construct(ShopProductController $shopProductController)
    {
        $this->shopProductController = $shopProductController;
    }
    /**
     * Obtiene las categorías marcadas como top (destacadas) con su estructura jerárquica
     * que están activas y tienen al menos un producto con precios válidos.
     *
     * @return JsonResponse
     *
     * @example Respuesta exitosa:
     * [
     *     {
     *         "id": 1,
     *         "name": "Electrónica",
     *         "top": true,
     *         "children": [
     *             {
     *                 "id": 2,
     *                 "name": "Computadoras",
     *                 "top": false,
     *                 "children": [
     *                     {
     *                         "id": 3,
     *                         "name": "Laptops",
     *                         "top": true,
     *                         "products_count": 15
     *                     }
     *                 ],
     *                 "products_count": 15
     *             }
     *         ],
     *         "products_count": 30
     *     }
     * ]
     */
    public function getTopCategories(): JsonResponse
    {
        Log::info('getTopCategories');

        // Obtener categorías marcadas como top con sus descendientes
        $topCategories = Category::where('top', true)
            ->with(['descendants' => function($query) {
                $query->withCount(['products' => function($productQuery) {
                    $productQuery->whereHas('externalProductData', function($externalQuery) {
                        $externalQuery->where('price', '>', 0)
                            ->where('sale_price', '>', 0)
                            ->where('new_sale_price', '>', 0);
                    });
                }]);
            }])
            ->withCount(['products' => function($query) {
                $query->whereHas('externalProductData', function($subQuery) {
                    $subQuery->where('price', '>', 0)
                        ->where('sale_price', '>', 0)
                        ->where('new_sale_price', '>', 0);
                });
            }])
            ->get();

        // Filtrar categorías que tienen productos o descendientes con productos
        $filteredCategories = $topCategories->filter(function($category) {
            return $this->categoryHasValidProducts($category);
        });

        return response()->json(CategoryResource::collection($filteredCategories));
    }
    /**
     * Obtiene todas las categorías principales (raíz) con sus subcategorías anidadas
     * que están activas y tienen al menos un producto con precios válidos.
     *
     * @return JsonResponse
     *
     * @example Respuesta exitosa:
     * [
     *     {
     *         "id": 1,
     *         "name": "Electrónica",
     *         "children": [
     *             {
     *                 "id": 2,
     *                 "name": "Computadoras",
     *                 "children": [
     *                     {
     *                         "id": 3,
     *                         "name": "Laptops",
     *                         "products_count": 15
     *                     }
     *                 ],
     *                 "products_count": 15
     *             }
     *         ],
     *         "products_count": 30
     *     }
     * ]
     */
    public function getCategoriesHierarchy(): JsonResponse
    {
        Log::info('getCategoriesHierarchy');

        // Obtener categorías raíz con sus descendientes
        $rootCategories = Category::with(['descendants' => function($query) {
            $query->withCount(['products' => function($productQuery) {
                $productQuery->whereHas('externalProductData', function($externalQuery) {
                    $externalQuery->where('price', '>', 0)
                        ->where('sale_price', '>', 0)
                        ->where('new_sale_price', '>', 0);
                });
            }]);
        }])
            ->whereIsRoot()
            ->withCount(['products' => function($query) {
                $query->whereHas('externalProductData', function($subQuery) {
                    $subQuery->where('price', '>', 0)
                        ->where('sale_price', '>', 0)
                        ->where('new_sale_price', '>', 0);
                });
            }])
            ->get();

        // Filtrar categorías que tienen productos o descendientes con productos
        $filteredCategories = $rootCategories->filter(function($category) {
            return $this->categoryHasValidProducts($category);
        });

        return response()->json(CategoryResource::collection($filteredCategories));
    }

    /**
     * Función recursiva para verificar si una categoría o sus descendientes tienen productos válidos
     */
    private function categoryHasValidProducts($category): bool
    {
        if ($category->products_count > 0) {
            return true;
        }

        if ($category->relationLoaded('descendants')) {
            foreach ($category->descendants as $descendant) {
                if ($this->categoryHasValidProducts($descendant)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Obtiene las subcategorías directas de una categoría específica
     *
     * @param int $categoryId
     * @return JsonResponse
     */
    public function getSubcategories($categoryId): JsonResponse
    {
        Log::info("Fetching subcategories for category $categoryId");

        $subcategories = Category::withCount(['products' => function($query) {
            $query->whereHas('externalProductData', function($subQuery) {
                $subQuery->where('price', '>', 0)
                    ->where('sale_price', '>', 0)
                    ->where('new_sale_price', '>', 0);
            });
        }])
            ->where('parent_id', $categoryId)
            ->get()
            ->filter(function($category) {
                return $category->products_count > 0;
            });

        return response()->json(CategoryResource::collection($subcategories));
    }
    /**
     * Obtiene una categoría por su path completo (ej: "electronica/computadoras/laptops")
     * incluyendo sus productos con precios válidos.
     *
     * @param string $path El path completo de la categoría (ej: "electronica/computadoras/laptops")
     * @return JsonResponse
     */
    public function getCategoryByPath(string $path): JsonResponse
    {
        Log::info('getCategoryByPath', ['path' => $path]);

        // Buscar la categoría por nombre y verificar que su path completo coincida
        $category = Category::where('path', $path)
            ->withCount('products')
            ->first();

        // Verificar si la categoría existe y si el path coincide
        if (!$category) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        // Cargar relaciones adicionales si es necesario
        $category->load(['parent', 'children' => function($query) {
            $query->withCount(['products' => function($query) {
                $query->whereHas('externalProductData', function($subQuery) {
                    $subQuery->where('price', '>', 0)
                        ->where('sale_price', '>', 0)
                        ->where('new_sale_price', '>', 0);
                });
            }]);
        }]);

        return response()->json(new CategoryResource($category), 200);
    }
    /**
     * Obtiene todos los productos de una categoría específica con sus mejores precios
     *
     * @param int $categoryId ID de la categoría
     * @return JsonResponse
     */
    public function getProductsByCategoryId(int $categoryId): JsonResponse
    {
        Log::info('getProductsByCategoryId', ['categoryId' => $categoryId]);

        // 1. Buscar la categoría con sus productos básicos
        $category = Category::with(['products' => function($query) {
            $query->with(['brand', 'gallery']);
        }])->find($categoryId);

        if (!$category) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        // 2. Obtener productos y procesarlos
        $productsWithBestPrice = $category->products->map(function ($product) {
            // Usar el método del otro controlador para obtener el mejor precio
            $bestPriceResponse = app()->make(ShopProductController::class)
                ->getBestSupplierForProduct($product->id);

            // Si no hay proveedor válido, descartar el producto
            if (!$bestPriceResponse || $bestPriceResponse->getStatusCode() != 200) {
                return null;
            }

            $bestPriceData = json_decode($bestPriceResponse->getContent(), true);

            // Crear el resource del producto y agregar bestPrice
            $productResource = new ShopProductResource($product);
            $productResource->additional(['bestPrice' => $bestPriceData]);

            return $productResource;
        })->filter()->values();

        if ($productsWithBestPrice->isEmpty()) {
            return response()->json(['message' => 'No hay productos disponibles con precios válidos'], 404);
        }

        return response()->json([
            'category' => new CategoryResource($category),
            'products' => $productsWithBestPrice
        ], 200);
    }
}
