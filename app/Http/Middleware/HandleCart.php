<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HandleCart
{
    public function handle($request, Closure $next)
    {
        Log::info('HandleCart@handle');

        // 1. Verificar token Bearer sin forzar autenticación
        $user = null;
        if ($bearerToken = $request->bearerToken()) {
            try {
                // Verificar token sin autenticar (solo validar)
                $payload = Auth::guard('api')->getPayload($bearerToken);
                $user = Auth::guard('api')->getProvider()->retrieveById($payload->get('sub'));

                if ($user) {
                    Auth::setUser($user);
                }
            } catch (\Exception $e) {
                Log::warning('Token Bearer inválido o expirado', [
                    'token' => $bearerToken,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // 2. Obtener sessionId del header o request
        $sessionId = $request->header('X-Session-ID') ?? $request->input('sessionId');
        Log::debug("Session ID recibido: " . $sessionId);

        // 3. Manejo de carritos
        if (Auth::check()) {
            Log::debug('Usuario autenticado: ' . Auth::id());

            // Migrar carrito de invitado si existe sessionId
            if ($sessionId) {
                Log::debug('Migrando carrito de invitado a usuario');
                $this->mergeGuestCart(Auth::user(), $sessionId);
            }

            $request->merge(['user_authenticated' => true]);
        } else {
            Log::debug('Usuario no autenticado (invitado)');

            if (!$sessionId) {
                Log::warning('Falta sessionId para usuario invitado');
                return response()->json([
                    'message' => 'Se requiere autenticación o session ID'
                ], 401);
            }

            $request->merge(['guest_session_id' => $sessionId]);
        }

        return $next($request);
    }
    protected function mergeGuestCart($user, $sessionId)
    {
        Log::info('HandleCart@mergeGuestCart', [
            'user_id' => $user->id,
            'session_id' => $sessionId
        ]);

        // Buscar carrito de invitado con sus items
        $guestCart = Cart::with('items')
            ->where('session_id', $sessionId)
            ->whereNull('user_id')
            ->where('is_active', true)
            ->first();

        if (!$guestCart) {
            Log::debug('No se encontró carrito de invitado para migrar');
            return;
        }

        // Obtener o crear carrito de usuario
        $userCart = $user->cart()->firstOrCreate(
            ['is_active' => true],
            [
                'uuid' => Str::uuid(),
                'expires_at' => now()->addDays(30),
                'session_id' => null // Limpiamos el session_id del carrito de usuario
            ]
        );

        Log::debug('Iniciando transferencia de items', [
            'guest_cart_id' => $guestCart->id,
            'user_cart_id' => $userCart->id,
            'items_count' => $guestCart->items->count()
        ]);

        // Transferir items
        $this->transferCartItems($guestCart, $userCart);

        // Eliminar completamente el carrito de invitado y sus items
        DB::transaction(function () use ($guestCart) {
            $guestCart->items()->delete(); // Eliminar todos los items asociados
            $guestCart->delete(); // Eliminar el carrito

            Log::info('Carrito de invitado eliminado', [
                'cart_id' => $guestCart->id,
                'items_deleted' => $guestCart->items->count()
            ]);
        });

        Log::info('Migración de carrito completada', [
            'user_id' => $user->id,
            'items_transferidos' => $userCart->items->count()
        ]);
    }

    protected function transferCartItems(Cart $source, Cart $target)
    {
        Log::info('HandleCart@transferCartItems');

        foreach ($source->items as $item) {
            $existingItem = $target->items()
                ->where('product_id', $item->product_id)
                ->where('supplier_id', $item->supplier_id)
                ->first();

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $item->quantity,
                    'price' => $item->price // Mantener el precio más reciente
                ]);
            } else {
                $target->items()->create($item->toArray());
            }
        }
    }
}
