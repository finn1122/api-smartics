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
    public function getActiveCart(Request $request): JsonResponse
    {
        Log::info('CartController@getActiveCart');

        // Obtenemos el sessionId de la solicitud, si existe. Si no, usamos el de la sesión actual.
        $sessionId = $request->sessionId ?? session()->getId();

        // Obtenemos el carrito según si el usuario está autenticado o no.
        $cart = Auth::check()
            ? $this->getUserCart()
            : $this->getGuestCart($sessionId);  // Pasamos el sessionId si es necesario.

        // Usamos el recurso CartResource para transformar la respuesta
        return response()->json([
            'success' => true,
            'data' => new CartResource($cart->load(['items.product.gallery', 'items.supplier'])),
        ]);
    }

    protected function getUserCart()
    {
        return Cart::firstOrCreate(
            ['user_id' => Auth::id(), 'is_active' => true],
            ['uuid' => Str::uuid(), 'expires_at' => now()->addDays(30)]
        );
    }

    protected function getGuestCart($sessionId)
    {
        return Cart::firstOrCreate(
            ['session_id' => $sessionId, 'is_active' => true],
            ['uuid' => Str::uuid(), 'expires_at' => now()->addDays(1)]
        );
    }

}
