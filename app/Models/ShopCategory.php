<?php

namespace App\Models;

use App\Services\DocumentUrlService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ShopCategory extends Model
{
    protected $fillable = [
        'id',
        'name',
        'description',
        'image_url',
        'path',
        'top',
        'active',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'shop_category_products', 'category_id', 'product_id')
            ->withPivot(['created_at', 'updated_at']);
    }

}
