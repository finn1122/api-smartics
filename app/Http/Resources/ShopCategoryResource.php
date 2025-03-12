<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\DocumentUrlService;

class ShopCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        // Resuelve DocumentUrlService desde el contenedor de Laravel
        $documentUrlService = app(DocumentUrlService::class);


        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'imageUrl' => $documentUrlService->getFullUrl($this->image_url), // Usar el servicio aquí
            'top' => $this->top,
            'active' => $this->active,
            'productsCount' => $this->products_count, // Agregar el contador de productos

        ];
    }
}
