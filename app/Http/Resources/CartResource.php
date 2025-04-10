<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'userId' => $this->user_id,
            'sessionId' => $this->session_id,
            'sharedBy' => $this->shared_by,
            'clonedFrom' => $this->cloned_from,
            'name' => $this->name,
            'isActive' => $this->is_active,
            'isShared' => $this->is_shared,
            'expiresAt' => $this->expires_at ? $this->expires_at->toDateTimeString() : null,
            'total' => $this->total,
            'items' => CartItemResource::collection($this->whenLoaded('items')), // Suponiendo que tienes un CartItemResource
        ];
    }
}
