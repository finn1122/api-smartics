<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InegiState extends Model
{
    protected $primaryKey = 'c_estado';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'c_estado',
        'd_estado',
        'abrev'
    ];

    public function municipalities(): HasMany
    {
        return $this->hasMany(InegiMunicipality::class, 'c_estado', 'c_estado');
    }

    public function cities(): HasMany
    {
        return $this->hasMany(InegiCity::class, 'c_estado', 'c_estado');
    }
}
