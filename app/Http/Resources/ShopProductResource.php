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
        $documentUrlService = app(DocumentUrlService::class);

        // Obtener el precio y los datos del proveedor desde ExternalProductData
        $externalData = $this->externalProductData->first(); // Obtener el primer registro de ExternalProductData
        $supplier = $externalData ? $externalData->supplier : null; // Obtener el proveedor asociado

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $externalData ? $externalData->price : null, // Precio desde ExternalProductData
            'sale_price' => $externalData ? $externalData->sale_price : null, // Precio de venta
            'currency_code' => $externalData ? $externalData->currency_code : null, // Código de moneda
            'imageUrl' => $documentUrlService->getFullUrl($this->image_url),
            'supplier' => $supplier ? [ // Datos del proveedor
                'id' => $supplier->id,
                'name' => $supplier->name,
                'contact' => $supplier->contact,
                'address' => $supplier->address,
            ] : null,
        ];
    }
}
