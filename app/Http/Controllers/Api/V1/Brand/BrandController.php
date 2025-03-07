<?php
namespace App\Http\Controllers\Api\V1\Brand;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BrandController extends Controller
{
    public function createBrand(Request $request, $supplier_id):JsonResponse{
        try {
            Log::info('createBrand');

            $brandName = $request->brand;

            $supplier = Supplier::findOrFail($supplier_id);

            Brand::createOrUpdate(
                ['name'=> $brandName],
                ['active' => true]
            );

            return response()->json(['message'=>'success'],200);

        }catch (\Exception $e){
            Log::error($e->getMessage());
        }
    }

}
