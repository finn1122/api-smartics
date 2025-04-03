<?php

namespace App\Http\Resources;

use App\Services\DocumentUrlService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryHierarchyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'path' => $this->path,
            'imageUrl' => app(DocumentUrlService::class)->getFullUrl($this->image_url),
            'top' => $this->top,
            'active' => $this->active,
            'children' => $this->buildCompleteHierarchy()
        ];
    }

    protected function buildCompleteHierarchy()
    {
        if (!$this->relationLoaded('descendants')) {
            return [];
        }

        // Obtener todos los descendientes de esta categoría
        $allDescendants = $this->descendants;

        // Construir la jerarquía recursivamente
        return $this->buildChildren($this->id, $allDescendants);
    }

    protected function buildChildren($parentId, $allDescendants)
    {
        // Filtrar hijos directos
        $children = $allDescendants->where('parent_id', $parentId);

        return $children->map(function($child) use ($allDescendants) {
            return [
                'id' => $child->id,
                'name' => $child->name,
                'path' => $child->path,
                'imageUrl' => app(DocumentUrlService::class)->getFullUrl($child->image_url),
                'top' => $child->top,
                'active' => $child->active,
                'children' => $this->buildChildren($child->id, $allDescendants)
            ];
        })->sortBy('name')->values();
    }
}
