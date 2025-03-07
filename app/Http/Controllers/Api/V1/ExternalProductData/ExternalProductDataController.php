<?php

namespace App\Http\Controllers\Api\V1\ExternalProductData;

use App\Http\Controllers\Controller;
use App\Models\ExternalProductData;
use App\Models\Product;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExternalProductDataController extends Controller
{
    function updateExternalProductData(int $product_id, $supplier_id, string $currency_code, float $price, int $quantity)
    {
        Log::info('updateExternalProductData');
        $product = Product::findOrFail($product_id);
        $supplier = Supplier::findOrFail($supplier_id);

        // Buscar si ya existe un registro de ExternalProductData para el producto y proveedor
        $externalProductData = ExternalProductData::where('product_id', $product_id)
            ->where('supplier_id', $supplier_id)
            ->first();

        // Si el registro no existe, crear uno nuevo con el sale_price como 0
        if (!$externalProductData) {
            ExternalProductData::create([
                'product_id' => $product_id,
                'supplier_id' => $supplier_id,
                'price' => $price,
                'sale_price' => 0, // Asignar el precio de venta como 0 cuando se crea
                'currency_code' => $currency_code,
                'quantity' => $quantity,
                'consulted_at' => Carbon::now(),
            ]);
        } else {
            // Si ya existe el registro, actualizarlo conservando el sale_price anterior
            $externalProductData->update([
                'price' => $price,
                'sale_price' => $externalProductData->sale_price ?? 0, // Conservar el sale_price anterior si existe
                'currency_code' => $currency_code,
                'quantity' => $quantity,
                'consulted_at' => Carbon::now(),
            ]);
        }
    }

}
