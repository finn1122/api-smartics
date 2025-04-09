<?php

namespace App\Policies;

use App\Models\CartItem;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CartItemPolicy
{
    use HandlesAuthorization;

    /**
     * Determina si el usuario puede actualizar el ítem del carrito
     */
    public function update(User $user, CartItem $cartItem)
    {
        // Si el carrito tiene usuario, verificar que sea el mismo
        if ($cartItem->cart->user_id) {
            return $user->id === $cartItem->cart->user_id;
        }

        // Si es carrito de sesión, verificar misma sesión
        return $cartItem->cart->session_id === session()->getId();
    }

    /**
     * Determina si el usuario puede eliminar el ítem del carrito
     */
    public function delete(User $user, CartItem $cartItem)
    {
        return $this->update($user, $cartItem);
    }
}
