<?php

namespace App\Http\Controllers\Api\V1\Cart;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function getActiveCart(): JsonResponse
    {
        Log::info('getActiveCart');

        $cart = Auth::check()
            ? $this->getUserCart()
            : $this->getGuestCart();

        // Usamos el recurso CartResource para transformar la respuesta
        return response()->json([
            'success' => true,
            'data' => new CartResource($cart->load(['items.product', 'items.supplier'])),
        ]);
    }

    protected function getUserCart()
    {
        return Cart::firstOrCreate(
            ['user_id' => Auth::id(), 'is_active' => true],
            ['uuid' => Str::uuid(), 'expires_at' => now()->addDays(30)]
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
