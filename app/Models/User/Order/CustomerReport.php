<?php

namespace App\Models\User\Order;

use App\Models\User\User;
use App\Models\Seller\Seller;
use App\Models\User\Order\Order;
use App\Models\User\Order\OrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerReport extends Model
{
    protected $fillable = [
        'user_id',
        'order_item_id',
        'issue_type',
        'description',
        'status',
        'resolved_at',
    ];


    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    // Relationships


    /**
     * Get the user that owns the CustomerReport
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    /**
     * Get the orderItem that owns the CustomerReport
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }


    //this will give the order to wich to order item belongs
    public function order()
    {
        return $this->orderItem->order();
    }

    //this will give the seller to wich to order item belongs
    public function seller()
    {
        // Access seller via orderItem relationship
        return $this->orderItem->seller();
    }
}
