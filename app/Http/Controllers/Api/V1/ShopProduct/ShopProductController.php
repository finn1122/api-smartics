<?php

namespace App\Http\Controllers\Api\V1\ShopProduct;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExternalProductDataResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        // Ordenar los proveedores por precio (de menor a mayor)
        $sortedSuppliers = $externalProductData->sortBy('price');

        // Buscar el primer proveedor con quantity > 0
        $bestSupplierData = null;
        foreach ($sortedSuppliers as $supplier) {
            if ($supplier->quantity > 0) {
                $bestSupplierData = $supplier;
                break;
            }
        }

        // Si ningún proveedor tiene quantity > 0, seleccionar el de menor precio
        if (!$bestSupplierData) {
            $bestSupplierData = $sortedSuppliers->first();
        }

        // Retornar el proveedor seleccionado
        return response()->json(new ExternalProductDataResource($bestSupplierData), 200);
    }
}
