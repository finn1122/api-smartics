<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InegiMunicipality extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'c_estado',
        'c_mnpio',
        'D_mnpio'
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(InegiState::class, 'c_estado', 'c_estado');
    }

    public function cities(): HasMany
    {
        return $this->hasMany(InegiCity::class, 'c_mnpio', 'c_mnpio')
            ->whereColumn('inegi_cities.c_estado', 'inegi_municipalities.c_estado');
    }
}
