<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class Category extends Model
{
    use NodeTrait;

    protected $fillable = ['name', 'parent_id','path', 'image_url', 'top', 'active'];


    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
    // Obtener la ruta completa de la categoría
    public function getFullPath()
    {
        return $this->ancestors()->pluck('name')->concat([$this->name])->implode('/');
    }
}
