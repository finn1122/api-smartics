<?php

namespace App\Http\Controllers\Api\V1\Tag;

use App\Http\Controllers\Api\V1\ShopProduct\ShopProductController;
use App\Http\Controllers\Controller;
use App\Http\Resources\ShopProductResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TagProductController extends Controller
{
    protected $shopProductController;

    public function __construct()
    {
        $this->shopProductController = app(ShopProductController::class);
    }

    public function getProductsByTag(int $tagId): JsonResponse
    {
        Log::info('getProductsByTag', ['tagId' => $tagId]);

        // Buscar el tag por su ID
        $tag = Tag::find($tagId);

        if (!$tag) {
            return response()->json(['message' => 'Etiqueta no encontrada'], 404);
        }

        // Obtener los productos del tag con la galería
        $products = $tag->products()->with(['gallery'])->get();

        // Verificar si hay productos
        if ($products->isEmpty()) {
            return response()->json(['message' => 'No se encontraron productos para esta etiqueta'], 404);
        }

        // Procesar productos con proveedor más económico
        $productsWithBestSupplier = $products->map(function ($product) {
            $bestSupplierResponse = $this->shopProductController->getBestPriceData($product->id);

            if (!$bestSupplierResponse) {
                return null;
            }

            $bestSupplierData = json_decode($bestSupplierResponse->getContent(), true);

            $productResource = new ShopProductResource($product);
            $productResource->additional(['bestPrice' => $bestSupplierData]);

            return $productResource;
        })->filter();

        // Filtrar productos sin precios válidos
        $filteredProducts = $productsWithBestSupplier->filter(function ($productResource) {
            return $productResource->additional['bestPrice'] !== null;
        });

        if ($filteredProducts->isEmpty()) {
            return response()->json(['message' => 'No hay productos disponibles con precios válidos'], 404);
        }

        return response()->json([
            'success' => true,
            'tag' => [
                'id' => $tag->id,
                'name' => $tag->name
            ],
            'products' => $filteredProducts->values()
        ], 200);
    }
}
