<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InegiPostalData extends Model
{
    protected $table = 'inegi_postal_data';
    protected $primaryKey = ['d_codigo', 'id_asenta_cpcons'];
    public $incrementing = false;

    protected $fillable = [
        'd_codigo',
        'd_asenta',
        'd_tipo_asenta',
        'D_mnpio',
        'd_estado',
        'd_ciudad',
        'd_CP',
        'c_estado',
        'c_oficina',
        'c_CP',
        'c_tipo_asenta',
        'c_mnpio',
        'id_asenta_cpcons',
        'd_zona',
        'c_cve_ciudad',
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

    public function city(): BelongsTo
    {
        return $this->belongsTo(InegiCity::class, 'c_cve_ciudad', 'c_cve_ciudad');
    }

    public function settlementType(): BelongsTo
    {
        return $this->belongsTo(InegiSettlementType::class, 'c_tipo_asenta', 'c_tipo_asenta');
    }
}
