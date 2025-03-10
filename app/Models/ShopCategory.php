<?php

namespace App\Models;

use App\Services\DocumentUrlService;
use Illuminate\Database\Eloquent\Model;

class ShopCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image_url',
        'top',
        'active',
    ];


}
