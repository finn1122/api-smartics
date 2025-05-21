<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InegiCity extends Model
{
    protected $primaryKey = 'c_cve_ciudad';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'c_cve_ciudad',
        'd_ciudad',
        'c_estado',
        'c_mnpio',
        'es_capital',
        'latitud',
        'longitud'
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(InegiState::class, 'c_estado', 'c_estado');
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(InegiMunicipality::class, ['c_estado', 'c_mnpio'], ['c_estado', 'c_mnpio']);
    }
}
