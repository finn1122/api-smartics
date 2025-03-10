<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', // Nombre del proveedor
        'contact', // Contacto del proveedor
        'address', // Dirección del proveedor
        'active'
    ];


    /**
     * Obtener los productos asociados a este proveedor.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'inventories', 'supplier_id', 'product_id')
            ->withPivot('quantity', 'purchase_date');
    }
}
