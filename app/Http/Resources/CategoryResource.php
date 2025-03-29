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
            'children' => $this->when(
                $this->relationLoaded('descendants') && $this->descendants->isNotEmpty(),
                function() {
                    return CategoryResource::collection(
                        $this->descendants->filter(function($descendant) {
                            return $descendant->products_count > 0;
                        })
                    );
                }
            ),
            'hasChildren' => $this->when(
                $this->relationLoaded('descendants'),
                function() {
                    return $this->descendants->filter(function($descendant) {
                        return $descendant->products_count > 0;
                    })->isNotEmpty();
                }
            )
        ];
    }
}
