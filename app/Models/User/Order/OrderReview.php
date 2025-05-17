<?php

namespace App\Models\User\Order;

use App\Models\User\User;
use App\Models\User\Order\OrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReview extends Model
{
    protected $fillable = [
        'order_item_id',  // The ID of the order item being reviewed
        'user_id',        // The ID of the user submitting the review
        'rating',         // Rating given by the user (1 to 5)
        'review',         // Text review submitted by the user
        'approved',       // Whether the review has been approved (default false)
    ];


    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'approved' => 'boolean', // Ensure 'approved' is cast to a boolean
    ];


    /**
     * Get the orderItem that owns the OrderReview
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the user that owns the OrderReview
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
