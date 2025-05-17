<?php

namespace App\Http\Controllers\User\Order;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User\Order\OrderItem;
use App\Models\User\Order\OrderReview;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\User\Order\OrderReviewRequest;
use App\Http\Resources\User\Order\OrderReviewResource;

class OrderReviewController extends Controller
{
    public function store(OrderReviewRequest $request){
        $validated = $request->validated();

        $user = auth()->user();
        $orderItem = OrderItem::with('order')->findOrFail($request->order_item_id);

        // Ownership check
        if ($orderItem->order->user_id !== $user->id) {
            return response()->json([
                'message' => 'You must have bought the product to review it.',
            ], Response::HTTP_FORBIDDEN); // 403
        }

        // Conflict check: already reviewed
        $alreadyReviewed = OrderReview::where('order_item_id', $orderItem->id)
            ->where('user_id', $user->id)
            ->exists();


        if ($alreadyReviewed) {
            return response()->json([
                'message' => 'You have already reviewed this order item.',
            ], Response::HTTP_CONFLICT); // 409
        }

        $review = OrderReview::create([
            'order_item_id' => $validated['order_item_id'],
            'user_id' => auth()->id(),
            'rating' => $validated['rating'],
            'review' => $validated['review'],
            'approved' => false, // default false, pending approval
        ]);

        return response()->json([
            "message"=>"Reviewed saved successfuly",
        ]);
    }


    public function show(string $id){
        //
    }
}
