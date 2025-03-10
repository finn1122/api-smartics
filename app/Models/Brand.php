<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla (opcional).
     */
    protected $table = 'brands';
    protected $fillable = [
        'name', 'active', 'created_at', 'updated_at'
    ];

    /**
     * Obtener los productos asociados a esta marca.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
