<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'name', 'active'
    ];

    /**
     * Obtener los productos asociados a esta marca.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
