<?php

namespace App\Http\Resources;

use App\Services\DocumentUrlService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GalleryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $documentUrlService = app(DocumentUrlService::class);

        return [
            'id' => $this->id, // ID de la imagen en la galería
            'imageUrl' =>  $documentUrlService->getFullUrl($this->image_url), // URL completa de la imagen
            'productId' => $this->product_id, // ID del producto asociado
            'active' => $this->active, // Estado activo/inactivo de la imagen
        ];
    }
}
