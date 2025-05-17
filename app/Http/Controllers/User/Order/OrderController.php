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
    public function store(Request $request, ProductStatService $statService)
    {
        $user = Auth::user();

        if (!$user->cart || $user->cart->items()->count() === 0) {
            return response()->json(['message' => 'Cart is empty.'], 400);
        }

        $cartItems = $user->cart->items()->with('product.category', 'variant')->get()->groupBy('product.seller_id');

        $allItems = $cartItems->flatten();
        $subtotal = $allItems->sum(fn($item) => $this->getDiscountedPrice($item) * $item->quantity);

        // Calculate total using discounted prices
        $total = $subtotal; // Add tax/shipping here if needed

        do {
            $orderNumber = 'ORD-' . Carbon::now()->format('Ymd') . '-' . Str::upper(Str::random(9));
        } while (Order::where('order_number', $orderNumber)->exists());

        DB::beginTransaction();

        try {
            $order = $user->orders()->create([
                'total_amount' => $total,
                'subtotal' => $subtotal,
                'payment_status' => "paid",
                'shipping_address_id' => $request->address_id,
                'order_number' => $orderNumber,
            ]);

            $orderItems = [];
            $orderCommissionTotal = 0;

            foreach ($cartItems as $sellerId => $items) {
                $sellerSubtotal = 0;
                $sellerCommissionTotal = 0;

                // Calculate seller subtotal first
                foreach ($items as $item) {
                    $price = $this->getDiscountedPrice($item);
                    $sellerSubtotal += $price * $item->quantity;
                }

                // Create SellerOrder before creating OrderItems
                $sellerOrder = SellerOrder::create([
                    'order_id' => $order->id,
                    'seller_id' => $sellerId,
                    'order_number' => 'SO-' . Carbon::now()->format('Ymd') . '-' . $sellerId . '-' . Str::upper(Str::random(4)),
                    'subtotal' => $sellerSubtotal,
                    'shipping_cost' => 50,
                    'total' => $sellerSubtotal + ($sellerSubtotal * 0.05) + 50,
                    'commission' => 0,  // Will update after loop
                    'status' => 'pending',
                ]);

                foreach ($items as $item) {
                    $price = $this->getDiscountedPrice($item);
                    $quantity = $item->quantity;
                    $itemTotalPrice = $price * $quantity;

                    $product = $item->product;
                    $category = $product->category;
                    $commissionRate = $category ? ($category->commission_rate / 100) : 0;
                    $itemCommission = $itemTotalPrice * $commissionRate;

                    Log::info("test  => ",["category => ",$category,"commision rate = > ",$commissionRate,"item comisiion => ",$itemCommission]);

                    $attributes = $item->variant?->attributes;
                    if (is_string($attributes)) {
                        $attributes = json_decode($attributes, true);
                    }

                    $orderItems[] = new OrderItem([
                        'product_id' => $item->product_id,
                        'order_id' => $order->id,
                        'seller_order_id' => $sellerOrder->id,
                        'title' => $product->title,
                        'sku' => $item->has_variant ? $item->variant->sku : $product->sku,
                        'product_variant_id' => $item->variant_id,
                        'price' => $price,
                        'total_price' => $itemTotalPrice,
                        'commission' => $itemCommission,
                        'attributes' => $attributes,
                        'quantity' => $quantity,
                        'image' => $item->has_variant ? $item->variant->image : $product->images[0]->image_url,
                    ]);

                    $sellerCommissionTotal += $itemCommission;

                    $statService->logEvent($product->id, 'purchase');
                }

                // Update seller order commission after looping items
                $sellerOrder->commission = $sellerCommissionTotal;
                $sellerOrder->save();

                $orderCommissionTotal += $sellerCommissionTotal;
            }

            $order->items()->saveMany($orderItems);

            // Save total commission on order
            $order->commission = $orderCommissionTotal;
            $order->save();

            $user->cart->items()->delete();

            DB::commit();

            return OrderResource::make($order);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Order creation failed", ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Order creation failed', 'error' => $e->getMessage()], 500);
        }
    }

    protected function getDiscountedPrice($item)
    {
        if ($item->has_variant) {
            $price = $item->variant->price;
            $discount = $item->variant->discount; // Assuming relation returns discount model or null
        } else {
            $price = $item->product->base_price;
            $discount = $item->product->discount;
        }

        if ($discount && $discount->is_active) {
            // For example, discount could be a percentage or fixed amount
            if ($discount->type === 'percentage') {
                return $price - ($price * ($discount->value / 100));
            } elseif ($discount->type === 'fixed') {
                return max(0, $price - $discount->value);
            }
        }

        return $price;
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $order = $user->orders()->with(['items','shippingAddress'])->where('id',$id)->first();

        if(!$order){
            return response()->json([
                'message'=>"Order not found.",
            ]);
        }

        return OrderResource::make($order);
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


    // this function will get all ordrs to admin
    public function getAllOrders(Request $request)
    {
        $query = Order::query();

        // Optional status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Optional price range filter
        if ($request->filled('min')) {
            $query->where('total_amount', '>=', $request->min);
        }

        if ($request->filled('max')) {
            $query->where('total_amount', '<=', $request->max);
        }

        // Optional: Eager load relationships
        $orders = $query->with(['items'])->latest()->get();

        return OrderResource::collection($orders);
    }

}
