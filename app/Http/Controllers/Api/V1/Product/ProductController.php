<?php
namespace App\Http\Controllers\Api\V1\Product;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use function Laravel\Prompts\error;

class ProductController extends Controller
{
    public function createProduct(Request $request, $supplier_id):JsonResponse
    {
        try {
            Log::info("createProduct");

            // Validación de los datos del request
            $validatedData = $request->validate([
                'name'              => 'required|string|max:255',
                'cvaKey'           => 'nullable|string|max:100',
                'sku'               => 'required|string|max:100|unique:products,sku',
                'warranty'          => 'nullable|string|max:100',
                'brandId'          => 'required|exists:brands,id',
                'groupId'          => 'required|exists:groups,id',
                'active'            => 'required|boolean',
            ]);

            // Creación del producto usando variables individuales
            $product = Product::create([
                'name'              => $validatedData['name'],
                'cva_key'           => $validatedData['cvaKey'], // Asignando el valor de 'cva_key'
                'sku'               => $validatedData['sku'],
                'warranty'          => $validatedData['warranty'],
                'brand_id'          => $validatedData['brandId'], // Asignando el valor de 'brand_id'
                'group_id'          => $validatedData['groupId'],
                'active'            => $validatedData['active'],
            ]);

            return response()->json($product);

        }catch (\Exception $e){
            Log:error($e->getMessage());
        }
    }
}
