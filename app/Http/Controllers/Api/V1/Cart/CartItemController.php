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
        Log::info('CartItemController@store');
        $request->validate([
            'productId' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            // Obtener o crear el carrito activo del usuario
            $cart = $this->getOrCreateActiveCart();

            // Obtener el producto con su mejor precio
            $bestPriceResponse = app()->make(ShopProductController::class)
                ->getBestSupplierForProduct($request->productId);

            if ($bestPriceResponse || $bestPriceResponse->getStatusCode() == 200) {
                $bestPriceData = json_decode($bestPriceResponse->getContent(), true);

                Log::debug($bestPriceData);

                $product = Product::findOrFail($request->productId);


                $productResource = new ShopProductResource($product);
                $productResource->additional(['bestPrice' => $bestPriceData]);



                // Verificar disponibilidad
                if (!$this->checkProductAvailability($bestPriceData, $request->quantity)) {
                    return response()->json([
                        'message' => 'No hay suficiente stock disponible',
                        'available_quantity' => $product->bestPrice->quantity ?? 0
                    ], 422);
                }

                // Buscar si el producto ya está en el carrito
                $existingItem = $cart->items()
                    ->where('product_id', $product->id)
                    ->where('supplier_id', $bestPriceData['supplierId'])
                    ->first();

                if ($existingItem) {
                    // Actualizar cantidad si ya existe
                    $existingItem->update([
                        'quantity' => $existingItem->quantity + $request->quantity,
                    ]);

                    $item = $existingItem;
                } else {
                    // Crear nuevo ítem en el carrito
                    $item = CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => $product->id,
                        'supplier_id' => $bestPriceData['supplierId'],
                        'price' => $bestPriceData['newSalePrice'] ?? $bestPriceData['salePrice'],
                        'original_price' => $bestPriceData['salePrice'],
                        'quantity' => $request->quantity,
                    ]);
                }

                // Actualizar timestamp del carrito
                $cart->touch();

                return response()->json([
                    'message' => 'Producto agregado al carrito',
                    'cart_item' => $item,
                    'cart_total_items' => $cart->items()->count(),
                    'cart_total_price' => $cart->total,
                ], 201);
            }
            });

    }

    /**
     * Obtiene o crea el carrito activo del usuario
     *
     * @return Cart
     */
    protected function getOrCreateActiveCart()
    {
        $user = Auth::user();
        $sessionId = session()->getId();

        if ($user) {
            return Cart::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'is_active' => true,
                ],
                [
                    'uuid' => Str::uuid(),
                    'expires_at' => now()->addDays(30),
                ]
            );
        }

        return Cart::firstOrCreate(
            [
                'session_id' => $sessionId,
                'is_active' => true,
            ],
            [
                'uuid' => Str::uuid(),
                'expires_at' => now()->addDays(1), // Carritos de invitados expiran más rápido
            ]
        );
    }

    /**
     * Verifica la disponibilidad del producto
     *
     * @param Product $product
     * @param int $quantity
     * @return bool
     */
    protected function checkProductAvailability($product, int $quantity)
    {
        // Si no hay información de stock, asumimos que está disponible
        if (!isset($bestPriceData->quantity)) {
            return true;
        }

        return $product->bestPrice->quantity >= $quantity;
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
