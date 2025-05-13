<?php

namespace App\Http\Controllers\User\Order;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\User\Order\Order;
use App\Models\Seller\SellerOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\User\Order\OrderItem;
use App\Services\ProductStatService;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\User\Order\OrderResource;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
    {
        $user = Auth::user();

        $orders = $user->orders()->with('items')->get();

        return OrderResource::collection($orders);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request,ProductStatService $statService)
    {
        $user = Auth::user();

        if (!$user->cart || $user->cart->items()->count() === 0) {
            return response()->json(['message' => 'Cart is empty.'], 400);
        }

        // Get cart items grouped by seller
        $cartItems = $user->cart->items()->with('product', 'variant')->get()->groupBy('product.seller_id');

        // Log::info("cartItems = >",$cartItems->all());

        // Step 2: Calculate total from cart items
        $total = $cartItems->reduce(function ($sum, $items) {
            $subtotal = $items->reduce(function ($sum, $item) {
                $price = $item->has_variant ? $item->variant->price : $item->product->base_price;
                return $sum + ($price * $item->quantity);
            }, 0.0);
            return $sum + $subtotal;
        }, 0.0);

        do {
            // Generate order number
            $orderNumber = 'ORD-' . Carbon::now()->format('Ymd') . '-' . Str::upper(Str::random(9));

            // Check if the order number already exists in the database
        } while (Order::where('order_number', $orderNumber)->exists());

        // order creation transaction
        DB::beginTransaction();

        try {
            // Creates order
            $order = $user->orders()->create([
                'total_amount' => $total,
                'payment_status'=>"paid",
                'shipping_address_id'=>$request->address_id,
                'order_number' => $orderNumber,
            ]);

            Log::info("cartItems = >",['items',$cartItems]);
            // Create SellerOrders and OrderItems
            $orderItems = [];
           foreach ($cartItems as $sellerId => $items) {
            // Calculate seller's subtotal
            $sellerSubtotal = $items->reduce(function ($sum, $item) {
                $price = $item->has_variant ? $item->variant->price : $item->product->base_price;
                return $sum + ($price * $item->quantity);
            }, 0.0);

            // Create SellerOrder for each seller
            $sellerOrder = SellerOrder::create([
                'order_id' => $order->id,
                'seller_id' => $sellerId,
                'order_number' => 'SO-' . Carbon::now()->format('Ymd') . '-' . $sellerId . '-' . Str::upper(Str::random(4)),
                'subtotal' => $sellerSubtotal,
                'shipping_cost' => 50, // Example shipping cost per seller
                'total' => $sellerSubtotal + ($sellerSubtotal * 0.05) + 50,
                'status' => 'pending',
            ]);

            // Create order items for this seller
            foreach ($items as $item) {
                $price = $item->has_variant ? $item->variant->price : $item->product->base_price;
                $sku = $item->has_variant ? $item->variant->sku : $item->product->sku;
                $image = $item->has_variant ? $item->variant->image : $item->product->images[0]->image_url;
                $product = $item->product;

                // Log the purchase event
                $statService->logEvent($product->id, 'purchase');

                $orderItems[] = new OrderItem([
                    'product_id' => $item->product_id,
                    'order_id' => $order->id,
                    'seller_order_id' => $sellerOrder->id, // Link to seller order
                    'title' => $product->title,
                    'sku' => $sku,
                    'product_variant_id' => $item->variant_id,
                    'price' => $price,
                    'total_price' => $item->quantity * $price,
                    'attributes' => $item->variant ? $item->variant->attributes : null,
                    'quantity' => $item->quantity,
                    'image' => $image,
                ]);
            }
        }

        // Save all order items
        $order->items()->saveMany($orderItems);

        // Step 6: Clear the cart
        $user->cart->items()->delete();

        DB::commit();

        return OrderResource::make($order);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::info("error ",['message'=>$e->getMessage()]);
            return response()->json(['message' => 'Order creation failed', 'error' => $e->getMessage()], 500);
        }


    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
