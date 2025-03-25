<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Slider extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'promo_message',
        'slider_type_id',
        'price',
        'button_text',
        'button_link',
        'image_url',
        'bg_color',
        'text_position',
        'active',
        'order',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(SliderType::class, 'slider_type_id');
    }
}
