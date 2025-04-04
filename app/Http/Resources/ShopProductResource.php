<?php

namespace App\Http\Resources;

use App\Services\DocumentUrlService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ShopProductResource extends JsonResource
{
    public function toArray($request)
    {
        // Obtener la categoría principal (puedes ajustar la lógica de selección según tus necesidades)
        $mainCategory = $this->categories->sortByDesc('depth')->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            'warranty' => $this->warranty,
            'active' => $this->active,

            // Path completo listo para usar en URLs
            'fullPath' => $mainCategory
                ? $mainCategory->getFullPath() . '/' . Str::slug($this->name)
                : Str::slug($this->name),

            // Jerarquía estructurada para navegación
            'hierarchy' => $mainCategory ? [
                'current' => [
                    'id' => $this->id,
                    'name' => $this->name,
                    'type' => 'product'
                ],
                'parents' => $mainCategory->ancestors->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'type' => 'category',
                        'path' => $category->path
                    ];
                })->toArray(),
                'category' => [
                    'id' => $mainCategory->id,
                    'name' => $mainCategory->name,
                    'type' => 'category',
                    'path' => $mainCategory->path
                ]
            ] : null,

            // Marca del producto
            'brand' => $this->brand ? [
                'id' => $this->brand->id,
                'name' => $this->brand->name,
                'active' => $this->brand->active,
            ] : null,

            // Galería de imágenes del producto
            'gallery' => $this->whenLoaded('gallery', function () {
                return GalleryResource::collection($this->gallery);
            }),

            // Proveedor con el mejor precio
            'bestPrice' => $this->when(isset($this->additional['bestPrice']), function () {
                return $this->additional['bestPrice'];
            }),
        ];
    }
}
