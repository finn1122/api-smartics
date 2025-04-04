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
    public function getFullPathProduct(): string
    {
        return $this->ancestors()->pluck('path')->concat([$this->path])->implode('/');
    }

    public function getFullPathCategoryAttribute()
    {
        if (!$this->relationLoaded('ancestors')) {
            $this->load('ancestors');
        }

        return $this->ancestors->pluck('path')
            ->push($this->path)
            ->filter()
            ->implode('/');
    }
}
