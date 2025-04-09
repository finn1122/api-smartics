<?php

namespace App\Http\Controllers\Api\V1\Cart;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function __construct()
    {
        // Aplicar middleware a métodos específicos si es necesario
        $this->middleware('auth:api')->except(['getSharedCart', 'cloneSharedCart']);
    }

    public function getActiveCart()
    {
        $cart = Auth::check()
            ? $this->getUserCart()
            : $this->getGuestCart();

        return response()->json([
            'success' => true,
            'data' => $cart->load(['items.product.bestPrice', 'items.supplier'])
        ]);
    }

    protected function getUserCart()
    {
        return Cart::firstOrCreate(
            ['user_id' => Auth::id(), 'is_active' => true],
            ['uuid' => \Illuminate\Support\Str::uuid(), 'expires_at' => now()->addDays(30)]
        );
    }

    protected function getGuestCart()
    {
        return Cart::firstOrCreate(
            ['session_id' => session()->getId(), 'is_active' => true],
            ['uuid' => \Illuminate\Support\Str::uuid(), 'expires_at' => now()->addDays(1)]
        );
    }
}
