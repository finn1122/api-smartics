<?php

namespace App\Http\Controllers\Api\V1\Tag;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\ShopProduct\ShopProductController;
use Illuminate\Support\Facades\Log;

class TagController extends Controller
{
    protected $shopProductController;

    public function __construct()
    {
        $this->shopProductController = app(ShopProductController::class);
    }

    public function getActiveTagsWithValidProductsCount(): JsonResponse
    {
        Log::info('getActiveTagsWithValidProductsCount');

        $tags = Tag::where('active', true)
            ->with(['products' => function($query) {
                $query->where('active', true);
            }])
            ->get()
            ->map(function ($tag) {
                // Filtrar productos con bestPrice válido
                $validProducts = $tag->products->filter(function ($product) {
                    $bestSupplierResponse = $this->shopProductController->getBestPriceData($product->id);

                    if (!$bestSupplierResponse) {
                        return false;
                    }

                    $bestSupplierData = json_decode($bestSupplierResponse->getContent(), true);
                    return !empty($bestSupplierData);
                });

                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'validProductsCount' => $validProducts->count(),
                    'active' => $tag->active
                ];
            })
            ->filter(function ($tag) {
                return $tag['validProductsCount'] > 0; // Solo tags con productos válidos
            })
            ->values();

        if ($tags->isEmpty()) {
            return response()->json(['message' => 'No se encontraron etiquetas con productos válidos'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tags
        ], 200);
    }
}
