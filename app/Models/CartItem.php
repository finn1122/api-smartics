<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'supplier_id', 'price', 'original_price', 'quantity', 'options'];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'options' => 'array',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Helper para subtotal
    public function getSubtotalAttribute()
    {
        return $this->price * $this->quantity;
    }
}
