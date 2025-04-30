<?php

namespace App\Http\Controllers\Api\V1\Cart;

use App\Http\Controllers\Api\V1\ShopProduct\ShopProductController;
use App\Http\Controllers\Controller;
use App\Http\Resources\ShopProductResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
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
    public function storeGuestCart(Request $request)
    {
        Log::info('CartItemController@store', ['request' => $request->all()]);

        $request->validate([
            'productId' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'sessionId' => 'required_if:token,null|string',
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Obtener información básica
            $cart = $this->getOrCreateActiveCart($request->sessionId);
            $product = Product::findOrFail($request->productId);

            // 2. Obtener mejor precio
            $bestPriceResponse = app()->make(ShopProductController::class)
                ->getBestPriceData($request->productId);

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

            // 4. Buscar si ya existe el ítem
            $existingItem = $cart->items()
                ->where('product_id', $product->id)
                ->where('supplier_id', $bestPrice['supplierId'])
                ->first();

            // 5. Actualizar o crear según corresponda
            if ($existingItem) {
                // Actualizar cantidad existente
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $request->quantity,
                    'price' => $bestPrice['newSalePrice'] ?? $bestPrice['salePrice'],
                    'original_price' => $bestPrice['salePrice']
                ]);
                $item = $existingItem;
            } else {
                // Crear nuevo ítem
                $item = $cart->items()->create([
                    'product_id' => $product->id,
                    'supplier_id' => $bestPrice['supplierId'],
                    'price' => $bestPrice['newSalePrice'] ?? $bestPrice['salePrice'],
                    'original_price' => $bestPrice['salePrice'],
                    'quantity' => $request->quantity
                ]);
            }

            // 6. Respuesta optimizada
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateGuestCart(Request $request, int $item_id)
    {
        Log::info('CartItemController@updateGuestCart', ['request' => $request->all()]);
        Log::debug('Item ID desde URL: ' . $item_id);

        // Validar la solicitud
        $request->validate([
            'quantity'   => 'required|integer|min:1',
            'sessionId'  => 'required|string', // Este es el campo session_id en la base de datos
        ]);

        // Buscar el carrito por session_id
        $cart = Cart::where('session_id', $request->sessionId)->first();

        if (!$cart) {
            return response()->json(['message' => 'Carrito no encontrado.'], 404);
        }

        // Buscar el item en el carrito usando el ID de la URL
        $cartItem = $cart->items()->where('id', $item_id)->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Item no encontrado en el carrito.'], 404);
        }

        // Obtener el producto
        $product = Product::findOrFail($cartItem->product_id);

        // Obtener bestPrice llamando al controlador
        $bestPriceResponse = app()->make(ShopProductController::class)->getBestPriceData($product->id);

        if (!$bestPriceResponse || $bestPriceResponse->getStatusCode() !== 200) {
            return response()->json([
                'message' => 'No se pudo obtener la información de stock del producto.',
            ], 500);
        }

        $bestPriceData = json_decode($bestPriceResponse->getContent(), true);
        $availableQuantity = $bestPriceData['quantity'] ?? 0;

        // Verificar disponibilidad
        if ($availableQuantity < $request->quantity) {
            return response()->json([
                'message' => 'No hay suficiente stock disponible',
                'available_quantity' => $availableQuantity
            ], 422);
        }

        // Actualizar la cantidad del item
        $cartItem->update([
            'quantity' => $request->quantity,
        ]);

        // Actualizar el timestamp del carrito
        $cart->touch();

        return response()->json([
            'message' => 'Cantidad actualizada',
            'cart_item' => $cartItem,
            'cart_total_price' => $cart->total,
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


    /**
     * Remove an item from the guest cart
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $item_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeItemGuestCart(Request $request, int $item_id): JsonResponse
    {
        Log::info('CartItemController@removeItemGuestCart', [
            'item_id' => $item_id,
            'sessionId' => $request->sessionId
        ]);

        // Validate the request
        $request->validate([
            'sessionId' => 'required|string', // This is the session_id field in the database
        ]);

        // Find the cart by session_id
        $cart = Cart::where('session_id', $request->sessionId)->first();

        if (!$cart) {
            return response()->json(['message' => 'Carrito no encontrado.'], 404);
        }

        // Find and delete the item in the cart using the URL ID
        $deleted = $cart->items()->where('id', $item_id)->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Item no encontrado en el carrito.'], 404);
        }

        // Update the cart timestamp
        $cart->touch();

        return response()->json([
            'message' => 'Item eliminado del carrito',
            'cart_total_price' => $cart->refresh()->total,
        ]);
    }
}
