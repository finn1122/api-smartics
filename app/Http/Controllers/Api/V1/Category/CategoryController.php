<?php

namespace App\Http\Controllers\Api\V1\Category;

use App\Http\Controllers\Api\V1\ShopProduct\ShopProductController;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryHierarchyResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ShopProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    private $shopProductController;
    public function __construct(ShopProductController $shopProductController)
    {
        $this->shopProductController = $shopProductController;
    }
    /**
     * Obtiene categorías top con estructura jerárquica y conteo de productos válidos.
     *
     * @operationId getTopCategories
     * @tags Categories
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Electrónica",
     *       "top": true,
     *       "products_count": 42,
     *       "children": [
     *         {
     *           "id": 5,
     *           "name": "Computadoras",
     *           "products_count": 15,
     *           "children": []
     *         }
     *       ]
     *     }
     *   ]
     * }
     */
    public function getTopCategories(): JsonResponse
    {
        Log::debug('Inicio: getTopCategories');

        $categories = Category::query()
            ->where('top', true)
            ->with(['descendants' => function ($query) {
                $query->withCount(['products' => fn($q) => $q->withBestSupplier()]);
            }])
            ->withCount(['products' => fn($q) => $q->withBestSupplier()])
            ->get()
            ->filter(fn($cat) => $this->categoryHasValidProducts($cat));

        Log::debug('Categorías procesadas: '.$categories->count());

        return response()->json([
            'data' => CategoryResource::collection($categories),
            'meta' => [
                'total_categories' => $categories->count(),
                'timestamp' => now()->toDateTimeString()
            ]
        ]);
    }

    public function getCategoriesHierarchy(): JsonResponse
    {
        $categories = Category::whereIsRoot()
            ->with(['descendants' => function($query) {
                $query->orderBy('_lft'); // Importante para mantener el orden jerárquico
            }])
            ->orderBy('name')
            ->get();

        return response()->json(CategoryHierarchyResource::collection($categories));
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
        Log::info('getCategoryByPath');
        $category = Category::where('path', $path)
            ->withCount('products')
            ->with(['ancestors' => function($query) {
                $query->orderBy('_lft'); // Ordenar por left value para obtener el orden jerárquico correcto
            }])
            ->first();

        if (!$category) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        $category->load(['children' => function($query) {
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
        $category = Category::with([
            'products' => function($query) {
                $query->with(['brand', 'gallery', 'categories' => function($q) {
                    $q->with('ancestors');
                }]);
            }
        ])->find($categoryId);

        if (!$category) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        // 2. Obtener productos y procesarlos
        $productsWithBestPrice = $category->products->map(function ($product) {
            // Usar el método del otro controlador para obtener el mejor precio
            $bestPriceResponse = app()->make(ShopProductController::class)
                ->getBestPriceData($product->id);

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

        return response()->json($productsWithBestPrice, 200);
    }
}
