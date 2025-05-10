<?php

namespace App\Models\User;

use App\Models\Seller\Product\Product;
use Illuminate\Database\Eloquent\Model;
use App\Models\Seller\Product\ProductVariant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WishlistItem extends Model
{
    protected $fillable = [
        'wishlist_id',
        'product_id',
        'variant_id'
    ];

    /**
     * Get the product that owns the WishlistItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the variant that owns the WishlistItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

}
