<?php

namespace App\Http\Resources;

use App\Services\DocumentUrlService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SliderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $documentUrlService = app(DocumentUrlService::class);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'promoMessage' => $this->promo_message,
            'price' => $this->price,
            'buttonText' => $this->button_text,
            'buttonLink' => $this->button_link,
            'imageUrl' => $documentUrlService->getFullUrl($this->image_url),
            'bgColor' => $this->bg_color,
            'textPosition' => $this->text_position,
            'type' => [
                'name' => $this->type->name,
                'displayName' => $this->type->display_name,
                'color' => $this->type->color,
                'icon' => $this->type->icon,
            ],
            'order' => $this->order,
        ];
    }
}
