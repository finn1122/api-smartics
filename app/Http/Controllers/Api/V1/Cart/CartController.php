<?php

namespace App\Http\Controllers\Api\V1\Cart;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function getActiveCart(Request $request): JsonResponse
    {
        Log::info('CartController@getActiveCart');

        // Obtenemos el sessionId de la solicitud, si existe. Si no, usamos el de la sesión actual.
        $sessionId = $request->sessionId ?? session()->getId();

        Log::debug('sessionId: ' . $sessionId);

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

    /**
     * Clear all items from the guest cart
     * DELETE /api/v1/guest-cart/clear?sessionId=xxx
     */
    /**
     * Vacía completamente el carrito (para usuarios autenticados e invitados)
     * DELETE /api/v1/cart/clear
     */
    public function clearCart(Request $request): JsonResponse
    {
        Log::info('CartController@clearCart', [
            'user_id' => Auth::check() ? Auth::id() : null,
            'session_id' => $request->header('X-Session-ID')
        ]);

        return DB::transaction(function () use ($request) {
            // Obtener el carrito adecuado según autenticación
            $cart = $this->resolveCart($request);

            if (!$cart) {
                return response()->json([
                    'message' => 'Carrito no encontrado',
                    'hint' => Auth::check() ? 'Intente iniciar sesión nuevamente' : 'Proporcione un sessionId válido'
                ], 404);
            }

            // Eliminar todos los items del carrito
            $itemsDeleted = $cart->items()->delete();
            $cart->touch();

            Log::debug('Carrito vaciado', [
                'cart_id' => $cart->id,
                'items_deleted' => $itemsDeleted
            ]);

            return response()->json([
                'message' => 'Carrito vaciado correctamente',
                'cart_total' => 0,
                'items_deleted' => $itemsDeleted
            ]);
        });
    }

    /**
     * Método auxiliar para obtener el carrito correcto
     */
    protected function resolveCart(Request $request): ?Cart
    {
        // 1. Prioridad a usuario autenticado
        if (Auth::check()) {
            return Auth::user()->cart()->firstOrCreate(
                ['is_active' => true],
                ['uuid' => Str::uuid(), 'expires_at' => now()->addDays(30)]
            );
        }

        // 2. Usuario invitado (por session_id)
        $sessionId = $request->header('X-Session-ID') ?? $request->input('sessionId');

        if ($sessionId) {
            return Cart::firstOrCreate(
                ['session_id' => $sessionId, 'user_id' => null],
                ['uuid' => Str::uuid(), 'is_active' => true, 'expires_at' => now()->addDays(1)]
            );
        }

        return null;
    }
}
