<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Models\User\CartItem;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\ProductStatService;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\User\Cart\CartResource;
use App\Http\Requests\User\Cart\AddToCartRequest;
use App\Http\Requests\User\Cart\UpdateCartRequest;
use App\Http\Resources\User\Cart\CartItemResource;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $cart = $user->cart()->with([
            'items.product.images', // Load product images
            'items.variant.optionValues' // Load variant attributes
            ])->first();


        return CartResource::make($cart);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AddToCartRequest $request,ProductStatService $statService)
    {
        $user = Auth::user();
        $cart = $user->cart;

        // Check if cart item with same product and variant exists
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->when($request->filled('variant_id'), function ($query) use ($request) {
                $query->where('variant_id', $request->variant_id);
            }, function ($query) {
                $query->whereNull('variant_id');
            })
            ->first();

        if ($cartItem) {
            // Update quantity if exists
            $cartItem->increment('quantity', $request->input('quantity', 1));
            $cartItem->refresh(); // Refresh to get the updated quantity
        } else {
            // Create new cart item
            $cartItem = CartItem::create([
                'cart_id'    => $cart->id,
                'product_id' => $request->product_id,
                'variant_id' => $request->variant_id, // nullable
                'quantity'   => $request->input('quantity', 1),
            ]);

            //this log teh event to product stat
             $statService->logEvent($product->id, 'cart');

        }

        return response()->json([
            'message' => 'Cart updated successfully.',
            'data' => [
                'id' => $cartItem->id,
                'product_id' => $cartItem->product_id,
                'variant_id' => $cartItem->variant_id,
                'quantity' => $cartItem->quantity,
            ]
        ], 201);
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();

        $cartItem = $user->cart->items()->find($id);


        if(!$cartItem){
            return response()->json([
                "status"=>"faild",
                "message"=>"Cart item not found",
            ]);
        }

        return CartItemResource::make($cartItem);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(string $id)
    {
        $user = Auth::user();

        // ensure cart exists
        if (! $user->cart) {
            return response()->json(['message' => 'Cart not found.'], 404);
        }

        // find the cart‑item by its own id
        $cartItem = $user->cart->items()->find($id);

        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found.'], 404);
        }

        // decrement by one, or delete if it would go to zero
        if ($cartItem->quantity > 1) {
            $cartItem->decrement('quantity', 1);
            return response()->json([
                'message'   => 'One item removed.',
                'quantity'  => $cartItem->quantity,
            ], 200);
        }

        $cartItem->delete();

        return response()->json([
            'message' => 'Cart item removed entirely.'
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();

        // ensure cart exists
        if (! $user->cart) {
            return response()->json(['message' => 'Cart not found.'], 404);
        }

        // find the cart‑item by its own id
        $cartItem = $user->cart->items()->find($id);

        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found.'], 404);
        }

        $cartItem->delete();

        return response()->json([
            'message' => 'Cart item removed entirely.'
        ], 200);
    }


    public function syncGuestCartItems(Request $request)
    {
        $items = $request->items;
        $cart = Auth::user()->cart;

        foreach ($items as $item) {
            $query = $cart->items()
                ->where('product_id', $item['productId']);

            // Handle nullable variant ID
            if (isset($item['variantId'])) {
                $query->where('variant_id', $item['variantId']);
            } else {
                $query->whereNull('variant_id');
            }

            $existingCartItem = $query->first();

            if ($existingCartItem) {
                $existingCartItem->increment('quantity', $item['quantity']);
            } else {
                $cart->items()->create([
                    'product_id' => $item['productId'],
                    'variant_id' => $item['variantId'] ?? null,
                    'quantity' => $item['quantity'],
                    'user_id' => $cart->id(), // assuming the item model needs this
                ]);
            }
        }

        return response()->json([
            "message" => "Cart synced successfully",
            "synced_items" => $request->items
        ]);
    }

}
