<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Cart extends Model
{
    protected $fillable = ['uuid','user_id', 'session_id', 'shared_by', 'cloned_from', 'name', 'is_active', 'is_shared', 'expires_at'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_shared' => 'boolean',
        'expires_at' => 'datetime',
        'options' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function sharedBy()
    {
        return $this->belongsTo(User::class, 'shared_by');
    }

    public function clonedFrom()
    {
        return $this->belongsTo(Cart::class, 'cloned_from');
    }

    public function sharedCart()
    {
        return $this->hasOne(SharedCart::class);
    }

    // Helper para calcular el total
    public function getTotalAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }
}
