<?php

namespace App\Http\Controllers\Api\V1\ShopProduct;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExternalProductDataResource;
use App\Http\Resources\ShopProductResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShopProductController extends Controller
{
    /**
     * Obtener el proveedor con el mejor precio para un producto específico.
     *
     * @param int $productId
     * @return JsonResponse|null
     */
    public function getBestPriceData($productId): ?JsonResponse
    {
        // Buscar el producto por su ID
        $product = Product::find($productId);

        if (!$product) {
            return null; // Producto no encontrado
        }

        // Obtener los datos de los proveedores externos para este producto
        $externalProductData = $product->externalProductData;

        if ($externalProductData->isEmpty()) {
            return null; // No hay datos de proveedores
        }

        // Filtrar proveedores con price, sale_price y new_sale_price mayores a 0
        $filteredSuppliers = $externalProductData->filter(function ($supplier) {
            return $supplier->price > 0 &&
                $supplier->sale_price > 0 &&
                $supplier->new_sale_price > 0;
        });

        if ($filteredSuppliers->isEmpty()) {
            return null; // No hay proveedores válidos
        }

        // Ordenar los proveedores filtrados por precio (de menor a mayor)
        $sortedSuppliers = $filteredSuppliers->sortBy('price');

        // Buscar el primer proveedor con quantity > 0
        $bestSupplierData = $sortedSuppliers->firstWhere('quantity', '>', 0);

        // Si ningún proveedor tiene quantity > 0, seleccionar el de menor precio
        if (!$bestSupplierData) {
            $bestSupplierData = $sortedSuppliers->first();
        }

        // Retornar el proveedor seleccionado
        return response()->json(new ExternalProductDataResource($bestSupplierData), 200);
    }

    public function getProductByPath(Request $request): JsonResponse
    {
        try {
            $path = $request->input('path');
            Log::info('Buscando producto por path', ['path' => $path]);

            $segments = explode('/', $path);
            $productSlug = end($segments);
            $cacheKey = "product.path.{$productSlug}";

            // 1. Caché para datos estáticos (producto + relaciones)
            $product = Cache::remember($cacheKey, now()->addHours(12), function () use ($productSlug) {
                return Product::with([
                    'categories' => function($query) {
                        $query->with('ancestors')->orderBy('_lft', 'desc');
                    },
                    'brand',
                ])->where('slug', $productSlug)->firstOrFail();
            });

            // Carga gallery siempre fresca
            $product->load('gallery');

            // 2. Validación de categoría (siempre fresca)
            $mainCategory = $product->categories->sortByDesc('_lft')->first();
            if (!$mainCategory) {
                throw new \RuntimeException('El producto no tiene categoría asignada');
            }

            // 3. Path canónico (sin caché para redirecciones precisas)
            $expectedPath = $mainCategory->getFullPathProduct() . '/' . $product->slug;
            if ($expectedPath !== $path) {
                return response()->json([
                    'redirect_to' => $expectedPath,
                    'canonical_url' => url("/{$expectedPath}")
                ], 301);
            }

            $bestPriceResponse = app()->make(ShopProductController::class)
                ->getBestPriceData($product->id);

            // Si hay proveedor válido, no descartar el producto
            if ($bestPriceResponse || $bestPriceResponse->getStatusCode() == 200) {
                $bestPriceData = json_decode($bestPriceResponse->getContent(), true);

                // 4. Crear el resource del producto y agregar bestPrice
                $productResource = new ShopProductResource($product);
                $productResource->additional(['bestPrice' => $bestPriceData]);


                // 5. Respuesta combinada
                return response()->json([
                    'data' => $productResource,
                    'meta' => [
                        'canonical_url' => url("/{$expectedPath}"),
                        'schema_type' => 'Product',
                        'cache_hit' => Cache::has($cacheKey) // Para debugging
                    ],
                ]);
            }
        } catch (ModelNotFoundException $e) {
            Log::error("Producto no encontrado: {$path}");
            return response()->json(['error' => 'Producto no encontrado'], 404);

        } catch (\RuntimeException $e) {
            Log::error("Error de categoría: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 422);

        } catch (\Exception $e) {
            Log::error("Error inesperado: " . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
        }
    }

    /**
     * Obtiene SOLO los datos del bestPrice (sin metadata ni recursos adicionales).
     *
     * @param int $productId
     * @return JsonResponse
     */
    public function getBestPriceOnly($productId): JsonResponse
    {
        try {
            // Reutilizamos la lógica existente
            $bestPriceResponse = $this->getBestPriceData($productId);

            if (!$bestPriceResponse || $bestPriceResponse->getStatusCode() !== 200) {
                return response()->json([
                    'error' => 'No hay precios disponibles para este producto.'
                ], 404);
            }

            // Decodificamos la respuesta JSON del método existente
            $bestPriceData = json_decode($bestPriceResponse->getContent(), true);

            // Extraemos solo los campos necesarios
            return response()->json([
                'price' => $bestPriceData['price'] ?? null,
                'sale_price' => $bestPriceData['sale_price'] ?? null,
                'new_sale_price' => $bestPriceData['new_sale_price'] ?? null,
                'currency' => $bestPriceData['currency'] ?? 'MXN',
                'has_stock' => ($bestPriceData['quantity'] ?? 0) > 0,
                'supplier_id' => $bestPriceData['supplier_id'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error("Error en getBestPriceOnly: " . $e->getMessage());
            return response()->json([
                'error' => 'Error al obtener el precio.'
            ], 500);
        }
    }
}
