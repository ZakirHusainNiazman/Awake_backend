<?php

namespace App\Models\Seller;

use App\Models\Seller\Seller;
use App\Models\User\Order\Order;
use App\Models\User\Order\OrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerOrder extends Model
{
    protected $fillable = [
        'order_id',
        'seller_id',
        'order_number',      // seller-facing unique order number
        'subtotal',
        'tax',
        'shipping_cost',
        'total',
        'status',
    ];


    /**
     * Get the order that owns the SellerOrder
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the seller that owns the SellerOrder
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * Get all of the orderItems for the SellerOrder
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
