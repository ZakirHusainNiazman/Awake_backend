<?php

namespace App\Http\Controllers\User\Cart;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Seller\Product\Product;
use App\Http\Resources\User\Cart\CartItemResource;

class GuestCartController extends Controller
{
    public function details(Request $request)
    {
        // Get items from the request
        $items = $request->input('items');

        // Create a collection from the cart items
        $cartItems = collect($items);

        Log::info("cart items in the request =>", $items);

        // Get unique product and variant IDs from the cart items
        $productIds = $cartItems->pluck('productId')->unique();
        $variantIds = $cartItems->pluck('variantId')->filter()->unique();

        // Fetch products with their variants, images, and discounts
        $products = Product::with([
            'images',
            'variants.optionValues',
            'variants.discount',
            'discount'
        ])->whereIn('id', $productIds)->get();

        // Map cart items to temporary cart-like objects
        $final = $cartItems->map(function ($item) use ($products) {
            // Find the product based on the productId
            $product = $products->firstWhere('id', $item['productId']);
            if (!$product) return null;

            // Check if the cart item has a variantId, and get the variant if it exists
            $variant = $item['variantId']
                ? $product->variants->firstWhere('id', $item['variantId'])
                : null;

            // Return a temporary object that mimics a CartItem model
            return (object) [
                'id' => null, // No ID for guest cart item
                'product_id' => $product->id,
                'variant_id' => $variant?->id, // Only set variant_id if variant exists
                'quantity' => $item['quantity'] ?? 1,
                'product' => $product,
                'variant' => $variant, // Pass variant directly for resource
            ];
        })->filter(); // Remove any null values (if a product wasn't found)

        Log::info("cart items in guest controller =>", $final->toArray());

        // Return a response with the mapped cart items
        return response()->json([
            'data' => CartItemResource::collection($final)
        ]);
    }
}
