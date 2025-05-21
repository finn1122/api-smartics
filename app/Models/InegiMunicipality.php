<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InegiMunicipality extends Model
{
    protected $primaryKey = ['c_estado', 'c_mnpio'];
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
        return $this->hasMany(InegiCity::class, ['c_estado', 'c_mnpio'], ['c_estado', 'c_mnpio']);
    }
}
