<?php

namespace App\Http\Resources;

use App\Services\DocumentUrlService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopProductResource extends JsonResource
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
            'description' => $this->description,
            'sku' => $this->sku,
            'warranty' => $this->warranty,
            'active' => $this->active,

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
            'supplier' => $this->when(isset($this->additional['supplier']), function () {
                return $this->additional['supplier'];
            }),
        ];
    }
}
