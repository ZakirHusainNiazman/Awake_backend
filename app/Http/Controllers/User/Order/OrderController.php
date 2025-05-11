<?php

namespace App\Http\Controllers\User\Order;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\User\Order\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\User\Order\OrderItem;
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
    public function store(Request $request)
    {
        $user = Auth::user();

        // Step 1: Get cart items from DB
        $cartItems = $user->cart->items()->with('product', 'variant')->get();

        Log::info("cartItems = >",$cartItems->all());

        // Step 2: Calculate total from cart items
        $total = $cartItems->reduce(function ($sum, $item) {
            $price = $item->variant ? $item->variant->price : $item->product->base_price;
            return $sum + ($price * $item->quantity);
        }, 0.0);


        do {
            // Generate order number
            $orderNumber = 'ORD-' . Carbon::now()->format('Ymd') . '-' . Str::upper(Str::random(6));

            // Check if the order number already exists in the database
        } while (Order::where('order_number', $orderNumber)->exists());

        // Step 3: Wrap order creation in transaction
        DB::beginTransaction();

        try {
            // Step 4: Create the order
            $order = $user->orders()->create([
                'total_amount' => $total,
                'payment_status'=>"paid",
                'shipping_address_id'=>$request->address_id,
                'order_number' => $orderNumber,
            ]);

            // Step 5: Create order items from cart
            $orderItems = $cartItems->map(function ($item) use ($order) {
                $price = $item->variant ? $item->variant->price : $item->product->base_price;
                $sku = $item->variant ? $item->variant->sku : $item->product->sku;
                $image = $item->variant ? $item->variant->image : $item->product->images[0]->image_url;
                $product = $item->product;
                return new OrderItem([
                    'product_id' => $item->product_id,
                    'order_id'=>$order->id,
                    'title'=>$product->title,
                    'sku'=>$sku,
                    'product_variant_id' => $item->variant_id,
                    'price' => $price,
                    'total_price'=>$item->quantity * $price,
                    'attributes'=>  $item->variant ? $item->variant->attributes : null,
                    'quantity' => $item->quantity,
                    'image'=> $image,
                ]);
            });

            $order->items()->saveMany($orderItems);

            // Step 6: Clear the cart
            $user->cart->items()->delete();

            DB::commit();

            return OrderResource::make($order);
        } catch (\Exception $e) {
            DB::rollBack();
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
