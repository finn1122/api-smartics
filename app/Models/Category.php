<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class Category extends Model
{
    use NodeTrait;

    protected $fillable = ['name', 'parent_id','path', 'image_url', 'top', 'active'];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
    // Obtener la ruta completa de la categoría
    public function getFullPath()
    {
        $path = [];
        $category = $this;

        // Mientras haya una categoría padre, seguimos buscando
        while ($category) {
            array_unshift($path, $category->name); // Agregar al principio del arreglo
            $category = $category->parent; // Obtener la categoría superior
        }

        return implode('/', $path); // Unir los elementos del arreglo con "/"
    }
}
