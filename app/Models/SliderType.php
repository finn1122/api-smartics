<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SliderType extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'color',
        'icon',
        'active'
    ];

    public function sliders(): HasMany
    {
        return $this->hasMany(Slider::class);
    }
}
