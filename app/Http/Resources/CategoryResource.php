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
            'imageUrl' => $documentUrlService->getFullUrl($this->image_url), // Usar el servicio aquí
            'path' => $this->path,
            'top' => $this->top,
            'active' => $this->active,
            'productsCount' => $this->products_count,
            'children' => $this->children->map(function($child) {
                // Si necesitas filtrar solo las categorías con productos, puedes hacerlo aquí
                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'imageUrl' => $child->image_url,
                    'path' => $child->path,
                    'top' => $child->top,
                    'active' => $child->active,
                    'productsCount' => $child->products_count,
                ];
            }),
        ];
    }
}
