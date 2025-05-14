<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Neighborhood extends Model
{
    protected $fillable = [
        'postal_code_id',
        'name',
        'settlement_type',
        'c_settlement_type',
        'zone_type'
    ];

    public function postalCode(): BelongsTo
    {
        return $this->belongsTo(PostalCode::class);
    }
}
