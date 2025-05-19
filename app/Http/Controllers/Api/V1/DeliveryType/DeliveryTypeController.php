<?php

namespace App\Http\Controllers\Api\V1\DeliveryType;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryTypeResource;
use App\Models\DeliveryType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryTypeController extends Controller
{
    /**
     * Display a listing of active delivery methods.
     *
     * @OA\Get(
     *     path="/api/v1/delivery-methods",
     *     operationId="getActiveDeliveryMethods",
     *     tags={"Delivery Methods"},
     *     summary="Get all active delivery methods",
     *     description="Returns list of active delivery methods ordered by sort_order",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/DeliveryTypeResource")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $deliveryTypes = DeliveryType::query()
                ->active()
                ->orderBy('sort_order')
                ->get();

            return response()->json([
                'success' => true,
                'data' => DeliveryTypeResource::collection($deliveryTypes)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve delivery methods',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified delivery method.
     *
     * @OA\Get(
     *     path="/api/v1/delivery-methods/{id}",
     *     operationId="getDeliveryMethodById",
     *     tags={"Delivery Methods"},
     *     summary="Get specific delivery method",
     *     description="Returns delivery method data",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of delivery method",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/DeliveryTypeResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $deliveryType = DeliveryType::active()->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new DeliveryTypeResource($deliveryType)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery method not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DeliveryType $deliveryType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeliveryType $deliveryType)
    {
        //
    }
}
