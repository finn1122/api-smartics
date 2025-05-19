<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryType extends Model
{
    protected $fillable = [
        'name',
        'key',
        'description',
        'price',
        'is_free',
        'estimated_days_min',
        'estimated_days_max',
        'active',
        'sort_order',
        'metadata'
    ];

    protected $casts = [
        'price' => 'float',
        'is_free' => 'boolean',
        'active' => 'boolean',
        'metadata' => 'array'
    ];

    // Scope para obtener solo métodos activos
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    // Método para obtener el rango estimado de entrega formateado
    public function getEstimatedRangeAttribute()
    {
        if (!$this->estimated_days_min && !$this->estimated_days_max) {
            return null;
        }

        if ($this->estimated_days_min === $this->estimated_days_max) {
            return "{$this->estimated_days_min} días";
        }

        return "{$this->estimated_days_min}-{$this->estimated_days_max} días";
    }
}
