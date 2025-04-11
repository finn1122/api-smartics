<?php

namespace App\Http\Controllers\Api\V1\Cart;

use App\Http\Controllers\Api\V1\ShopProduct\ShopProductController;
use App\Http\Controllers\Controller;
use App\Http\Resources\ShopProductResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CartItemController extends Controller
{
    /**
     * Agrega un producto al carrito
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'productId' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'sessionId' => 'required_if:token,null|string',  // Verifica si el sessionId es requerido cuando no hay token
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Obtener información básica
            $cart = $this->getOrCreateActiveCart($request->sessionId);  // Pasamos el sessionId si está presente
            $product = Product::findOrFail($request->productId);

            // 2. Obtener mejor precio
            $bestPriceResponse = app()->make(ShopProductController::class)
                ->getBestSupplierForProduct($request->productId);

            if (!$bestPriceResponse || $bestPriceResponse->getStatusCode() != 200) {
                throw new \Exception('No se pudo obtener el precio del producto', 500);
            }

            $bestPrice = json_decode($bestPriceResponse->getContent(), true);

            // 3. Validar stock
            if (isset($bestPrice['quantity']) && $bestPrice['quantity'] < $request->quantity) {
                return response()->json([
                    'message' => 'Stock insuficiente',
                    'availableQuantity' => $bestPrice['quantity']
                ], 422);
            }

            // 4. Actualizar/crear ítem en el carrito
            $item = $cart->items()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'supplier_id' => $bestPrice['supplierId']
                ],
                [
                    'price' => $bestPrice['newSalePrice'] ?? $bestPrice['salePrice'],
                    'original_price' => $bestPrice['salePrice'],
                    'quantity' => DB::raw("quantity + {$request->quantity}")
                ]
            );

            // 5. Respuesta optimizada
            return response()->json([
                'itemId' => $item->id,
                'productId' => $item->product_id,
                'supplierId' => $item->supplier_id,
                'newQuantity' => $item->quantity,
                'unitPrice' => $item->price,
                'cartTotal' => $cart->refresh()->total
            ], 201);
        });
    }
    protected function checkProductAvailability(array $bestPriceData, int $quantity): bool
    {
        // Si no hay información de stock, asumimos que está disponible
        if (!isset($bestPriceData['quantity'])) {
            return true;
        }

        return $bestPriceData['quantity'] >= $quantity;
    }
    /**
     * Obtiene o crea el carrito activo del usuario
     *
     * @return Cart
     */
    private function getOrCreateActiveCart($sessionId = null)
    {
        // Si existe un token, asociamos el carrito al usuario autenticado
        if (auth()->check()) {
            return auth()->user()->cart ?: auth()->user()->cart()->create();
        }

        // Si no hay usuario autenticado, buscamos el carrito por sessionId
        if ($sessionId) {
            return Cart::firstOrCreate(['session_id' => $sessionId]);
        }

        // Si no hay sessionId y no hay usuario autenticado, se crea un carrito temporal
        return Cart::create(['session_id' => $sessionId ?? (string) \Str::uuid()]);
    }
    /**
     * Actualiza la cantidad de un producto en el carrito
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  CartItem $cartItem
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, CartItem $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Verificar que el ítem pertenezca al carrito del usuario
        $this->authorize('update', $cartItem);

        // Obtener el producto con su mejor precio
        $product = Product::with('bestPrice')
            ->findOrFail($cartItem->product_id);

        // Verificar disponibilidad
        if (!$this->checkProductAvailability($product, $request->quantity)) {
            return response()->json([
                'message' => 'No hay suficiente stock disponible',
                'available_quantity' => $product->bestPrice->quantity ?? 0
            ], 422);
        }

        $cartItem->update([
            'quantity' => $request->quantity,
        ]);

        // Actualizar timestamp del carrito
        $cartItem->cart->touch();

        return response()->json([
            'message' => 'Cantidad actualizada',
            'cart_item' => $cartItem,
            'cart_total_price' => $cartItem->cart->total,
        ]);
    }

    /**
     * Elimina un producto del carrito
     *
     * @param  CartItem $cartItem
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(CartItem $cartItem)
    {
        // Verificar que el ítem pertenezca al carrito del usuario
        $this->authorize('delete', $cartItem);

        $cart = $cartItem->cart;
        $cartItem->delete();

        // Actualizar timestamp del carrito
        $cart->touch();

        return response()->json([
            'message' => 'Producto eliminado del carrito',
            'cart_total_items' => $cart->items()->count(),
            'cart_total_price' => $cart->total,
        ]);
    }
}
