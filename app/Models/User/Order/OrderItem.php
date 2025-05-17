<?php

namespace App\Models\User\Order;

use App\Models\User\User;
use App\Models\User\Order\Order;
use App\Models\Seller\SellerOrder;
use App\Models\Seller\Product\Product;
use Illuminate\Database\Eloquent\Model;
use App\Models\Seller\Product\ProductVariant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'title',
        'sku',
        'quantity',
        'price',
        'total_price',
        'attributes',
        'image',
        'seller_order_id',
        'commission',
    ];

    protected $casts = [
        'attributes' => 'array',
    ];


        function getDiscountedPrice($item)
        {
            if ($item->has_variant) {
                $variant = $item->variant;
                $basePrice = $variant->price;
                $discount = method_exists($variant, 'getActiveDiscountAmount')
                    ? $variant->getActiveDiscountAmount()
                    : null;

                return max(0, $basePrice - ($discount ?? 0));
            } else {
                $product = $item->product;
                $basePrice = $product->base_price;
                $discount = $product->getActiveDiscountAmount();

                return max(0, $basePrice - ($discount ?? 0));
            }
        }

    /**
     * Get the order that owns the OrderItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product that owns the OrderItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product that owns the OrderItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function getSeller()
    {
        return $this->product?->seller;
    }

    /**
     * Get the sellerOrder that owns the OrderItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sellerOrder(): BelongsTo
    {
        return $this->belongsTo(SellerOrder::class);
    }

    /**
     * Get the review associated with the OrderItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function review(): HasOne
    {
        return $this->hasOne(User::class);
    }


}
