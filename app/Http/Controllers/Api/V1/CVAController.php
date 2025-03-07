<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\ExternalProductData\ExternalProductDataController;
use App\Http\Controllers\Api\V1\Product\ProductController;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Group;
use App\Models\Product;
use App\Models\Supplier;
use App\Repositories\CVARepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Utils\CurrencyHelper;
use App\Jobs\CVAJob\CreateProductJob;
use App\Jobs\CVAJob\UpdateExternalProductDataJob;

class CVAController extends Controller
{
    protected CVARepository $cvaRepository;
    protected ProductController $productController;
    protected ExternalProductDataController $externalProductDataController;

    public function __construct(CVARepository $cvaRepository, ProductController $productController, ExternalProductDataController $externalProductDataController)
    {
        $this->cvaRepository = $cvaRepository;
        $this->productController = $productController;
        $this->externalProductDataController = $externalProductDataController;
    }

    /**
     * Obtiene la lista de precios por marca.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllProductsByBranchId($brand_id): JsonResponse
    {
        try {
            Log::info('getAllProductsByBranchId');
            // Buscar la marca por ID
            $brand = Brand::findOrFail($brand_id);

            // Verificar si la marca está activa
            if (!$brand->active) {
                return response()->json([
                    'message' => "La marca con ID {$brand_id} no está activa.",
                ], 400); // Código 400 para indicar un error del cliente
            }

            // Obtener los productos del repositorio
            $products = $this->cvaRepository->getProductsByBrandId($brand->id);
            $supplierCVA = Supplier::where('name', 'CVA')->first();

            if($supplierCVA){
                foreach ($products['item'] as $productArray) {
                    $product = Product::where('sku', $productArray['codigo_fabricante'])->first();

                    // Actualizar inventario
                    $quantity = $productArray['disponible'];
                    $price = $productArray['precio'];
                    $currencyCode = CurrencyHelper::getCurrencyCode($productArray['moneda']); // Retorna "MXN"


                    if(!$product) {

                        $productRequest = [
                            'name' => $productArray['descripcion'],
                            'cvaKey' => $productArray['clave'],
                            'sku' => $productArray['codigo_fabricante'],
                            'warranty' => $productArray['garantia'],
                            'brandId' => $brand->id,
                            'active' => true

                        ];

                        $productRequest = new Request($productRequest);

                        // Llamada al controlador para crear el producto
                        $productResponse = $this->productController->createProduct($productRequest, $supplierCVA->id);

                        // Obtener el contenido de la respuesta (decodificado como array)
                        $product = json_decode($productResponse->getContent(), true); // Decodifica el JSON a array

                        $productId = $product['id'];
                    }else{
                        $productId = $product->id;
                    }

                    $this->externalProductDataController->updateExternalProductData($productId, $supplierCVA->id, $currencyCode, $price, $quantity);

                }
                return response()->json(['message' => 'success'], 200);

            }



            // Devolver la respuesta exitosa
            return response()->json([
                'message' => 'success'
            ]);

        } catch (\Exception $e) {
            // Registrar el error en el log
            Log::error($e->getMessage());

            // Devolver una respuesta de error
            return response()->json([
                'message' => $e->getMessage(),
            ], 422); // Código 422 para otros errores de validación o lógica de negocio
        }
    }
    /**
     * Obtiene todos los productos
     * @return JsonResponse
     */
    public function getAllProducts(): JsonResponse
    {
        DB::beginTransaction(); // Iniciar transacción

        try {
            Log::info('getAllProducts');

            // Obtener los productos del repositorio
            $products = $this->cvaRepository->getAllProducts();
            $supplierCVA = Supplier::where('name', 'CVA')->first();

            if ($supplierCVA) {
                foreach ($products['item'] as $productArray) {
                    $groupName = $productArray['grupo'];
                    $brandName = $productArray['marca'];

                    // Verificar si el grupo existe, si no, crearlo
                    $group = Group::where('name', $groupName)->first();
                    if (!$group) {
                        $group = Group::create([
                            'name' => $groupName,
                            'active' => true
                        ]);
                    }

                    // Verificar si la marca existe, si no, crearla
                    $brand = Brand::where('name', $brandName)->first();
                    if (!$brand) {
                        $brand = Brand::create([
                            'name' => $brandName,
                            'active' => true
                        ]);
                    }

                    // Verificar si el producto existe
                    $product = Product::where('sku', $productArray['codigo_fabricante'])->first();

                    // Actualizar inventario
                    $quantity = $productArray['disponible'];
                    $price = $productArray['precio'];
                    $currencyCode = CurrencyHelper::getCurrencyCode($productArray['moneda']); // Retorna "MXN"

                    if (!$product) {
                        // Crear el producto si no existe
                        $productData = [
                            'name' => $productArray['descripcion'],
                            'cvaKey' => $productArray['clave'],
                            'sku' => $productArray['codigo_fabricante'],
                            'warranty' => $productArray['garantia'],
                            'brandId' => $brand->id,
                            'groupId' => $group->id,
                            'active' => true
                        ];

                        // Despachar el job para crear el producto
                        CreateProductJob::dispatch($productData, $supplierCVA->id);
                    } else {
                        $productId = $product->id;

                        // Despachar el job para actualizar los datos externos del producto
                        UpdateExternalProductDataJob::dispatch($productId, $supplierCVA->id, $currencyCode, $price, $quantity);
                    }
                }

                DB::commit(); // Confirmar transacción
                return response()->json(['message' => 'success'], 200);
            }

            DB::commit(); // Confirmar transacción si no hay proveedor CVA
            return response()->json(['message' => 'success']);

        } catch (\Exception $e) {
            DB::rollBack(); // Revertir transacción en caso de error
            Log::error('Error en getAllProducts: ' . $e->getMessage());

            return response()->json([
                'message' => $e->getMessage(),
            ], 422); // Código 422 para otros errores de validación o lógica de negocio
        }
    }
}
