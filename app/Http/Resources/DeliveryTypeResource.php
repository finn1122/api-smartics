<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryTypeResource extends JsonResource
{
    /**
     * @OA\Schema(
     *     schema="DeliveryTypeResource",
     *     type="object",
     *     title="Delivery Method Response",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="Envío Estándar"),
     *     @OA\Property(property="key", type="string", example="standard"),
     *     @OA\Property(property="description", type="string", example="Entrega en 3-5 días"),
     *     @OA\Property(property="price", type="number", format="float", example=4.99),
     *     @OA\Property(property="isFree", type="boolean", example=false),
     *     @OA\Property(property="estimatedDaysMin", type="integer", nullable=true, example=3),
     *     @OA\Property(property="estimatedDaysMax", type="integer", nullable=true, example=5),
     *     @OA\Property(property="estimatedRange", type="string", nullable=true, example="3-5 días"),
     *     @OA\Property(property="active", type="boolean", example=true),
     *     @OA\Property(property="sortOrder", type="integer", example=1),
     *     @OA\Property(
     *         property="metadata",
     *         type="object",
     *         example={"carrier": "Correos Express", "tracking": true}
     *     ),
     *     @OA\Property(property="createdAt", type="string", format="date-time"),
     *     @OA\Property(property="updatedAt", type="string", format="date-time")
     * )
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'key' => $this->key,
            'description' => $this->description,
            'price' => $this->price,
            'isFree' => $this->is_free,
            'estimatedDaysMin' => $this->estimated_days_min,
            'estimatedDaysMax' => $this->estimated_days_max,
            'estimatedRange' => $this->estimated_range,
            'active' => $this->active,
            'sortOrder' => $this->sort_order,
            'metadata' => $this->metadata,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
