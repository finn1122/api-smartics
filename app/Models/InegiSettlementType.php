<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InegiSettlementType extends Model
{
    protected $primaryKey = 'c_tipo_asenta';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'c_tipo_asenta',
        'd_tipo_asenta',
        'short_name'
    ];

    public function postalData(): HasMany
    {
        return $this->hasMany(InegiPostalData::class, 'c_tipo_asenta', 'c_tipo_asenta');
    }
}
