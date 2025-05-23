<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'active'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_tags');
    }
}
