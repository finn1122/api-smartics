<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SharedCart extends Model
{
    protected $fillable = ['cart_id', 'token', 'expires_at', 'max_uses'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function isValid()
    {
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        return $this->expires_at->isFuture();
    }
}
