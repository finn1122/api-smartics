<?php

namespace App\Http\Controllers\Api\V1\ShopProduct;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExternalProductDataResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopProductController extends Controller
{
    /**
     * Obtener el proveedor con el mejor precio para un producto específico.
     *
     * @param int $productId
     * @return JsonResponse|null
     */
    public function getBestSupplierForProduct($productId): ?JsonResponse
    {
        Log::info('getBestSupplierForProduct: ' . $productId);

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
}
