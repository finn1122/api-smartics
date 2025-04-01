<?php

namespace App\Http\Resources;

use App\Services\DocumentUrlService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Resuelve DocumentUrlService desde el contenedor de Laravel
        $documentUrlService = app(DocumentUrlService::class);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'imageUrl' => $documentUrlService->getFullUrl($this->image_url),
            'path' => $this->path,
            'top' => $this->top,
            'active' => $this->active,
            'productsCount' => $this->products_count ?? 0, // Asegura valor por defecto
            'children' => CategoryResource::collection($this->children),
        ];
    }
}
