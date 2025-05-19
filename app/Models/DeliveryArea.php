<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryArea extends Model
{

    protected $casts = [
        'coordinates' => 'array', // Conversión automática JSON <> array
    ];



}
