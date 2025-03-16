<?php

namespace App\Models;

use App\Services\DocumentUrlService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_url',
        'product_id',
        'active'
    ];

    // Relación con la tabla products (muchos a uno)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
