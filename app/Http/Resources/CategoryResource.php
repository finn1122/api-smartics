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
        $documentUrlService = app(DocumentUrlService::class);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'imageUrl' => $documentUrlService->getFullUrl($this->image_url),
            'path' => $this->path,
            'top' => $this->top,
            'active' => $this->active,
            'productsCount' => $this->products_count ?? 0,
            'hierarchy' => $this->whenLoaded('ancestors', function() {
                return $this->ancestors->map(function($ancestor) {
                    return [
                        'id' => $ancestor->id,
                        'name' => $ancestor->name,
                        'path' => $ancestor->path,
                        'parentId' => $ancestor->parent_id, // ← Añade esto
                        'imageUrl' => app(DocumentUrlService::class)->getFullUrl($ancestor->image_url)
                    ];
                });
            }),
            'children' => $this->whenLoaded('children', function() {
                return $this->children->map(function($child) {
                    return [
                        'id' => $child->id,
                        'name' => $child->name,
                        'path' => $child->path,
                        'parentId' => $child->parent_id, // ← Añade esto
                        'imageUrl' => app(DocumentUrlService::class)->getFullUrl($child->image_url),
                        'productsCount' => $child->products_count ?? 0
                    ];
                });
            }),
        ];
    }
}
