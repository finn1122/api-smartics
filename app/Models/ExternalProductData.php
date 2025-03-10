<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalProductData extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'supplier_id',
        'price',
        'sale_price',
        'currency_code',
        'quantity',
        'consulted_at',
    ];

    // Relación con el producto
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relación con el proveedor
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
