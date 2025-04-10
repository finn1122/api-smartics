<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
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
            'cartId' => $this->cart_id,
            'productId' => $this->product_id,
            'supplierId' => $this->supplier_id,
            'price' => $this->price,
            'originalPrice' => $this->original_price,
            'quantity' => $this->quantity,
            'options' => $this->options,
            'subtotal' => $this->subtotal, // Usamos el accesor "subtotal"
            'product' => new ShopProductResource($this->whenLoaded('product')), // Si es necesario
            //'supplier' => new SupplierResource($this->whenLoaded('supplier')), // Si es necesario
        ];
    }
}
