<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

class HandleCart
{
    public function handle($request, Closure $next)
    {
        // Procesar la petición primero
        $response = $next($request);

        // Solo procesar si es una respuesta exitosa (2xx)
        if (!$response->isSuccessful()) {
            return $response;
        }

        // Manejar login exitoso
        if ($request->is('api/v1/login') && Auth::check()) {
            $this->mergeGuestCart();
        }

        return $response;
    }

    protected function mergeGuestCart()
    {
        $sessionId = session()->getId();
        $guestCart = Cart::with('items')
            ->where('session_id', $sessionId)
            ->where('is_active', true)
            ->first();

        if ($guestCart) {
            $userCart = Cart::firstOrCreate(
                ['user_id' => Auth::id(), 'is_active' => true],
                [
                    'uuid' => \Illuminate\Support\Str::uuid(),
                    'expires_at' => now()->addDays(30),
                    'session_id' => $sessionId // Mantener referencia
                ]
            );

            $this->transferCartItems($guestCart, $userCart);
            $guestCart->update(['is_active' => false]);
        }
    }

    protected function transferCartItems(Cart $source, Cart $target)
    {
        foreach ($source->items as $item) {
            $existingItem = $target->items()
                ->where('product_id', $item->product_id)
                ->where('supplier_id', $item->supplier_id)
                ->first();

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $item->quantity
                ]);
            } else {
                $target->items()->create($item->toArray());
            }
        }
    }
}
