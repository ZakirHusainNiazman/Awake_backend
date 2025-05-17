<?php

namespace App\Models\Seller\Product;

use App\Models\User\Cart;
use App\Models\Seller\Seller;
use App\Models\User\Wishlist;
use App\Models\Category\Category;
use App\Models\User\Order\OrderItem;
use App\Models\User\Order\OrderReview;
use Illuminate\Database\Eloquent\Model;
use App\Models\Seller\Product\ProductStat;
use App\Models\Seller\Product\ProductImage;
use App\Models\Seller\Product\ProductOption;
use App\Models\Seller\Product\ProductVariant;
use App\Models\Seller\Product\ProductDiscount;
use App\Models\Seller\Product\ProductOptionValue;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Seller\Product\ProductVariantOptionValue;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Product extends Model
{
    protected $fillable = [
         'id',
        'category_id',
        'sku',
        'title',
        'details',
        'stock',
        'description',
        'base_price',
        'has_variants',
        'attributes',
        'has_discount',
    ];

    // ─ disable auto-incrementing (we’ll supply our own UUIDs)
    public $incrementing = false;

    // ─ keys are stored as strings, not ints
    protected $keyType = 'string';

    // ─ if you like, you can also permanently cast your id
    //    (not required, but can help with serialization)
    protected $casts = [
      'attributes'      => 'array',
      'has_discount' => 'boolean', // Ensures it gets cast to a boolean
    ];


    // helpers

    //helper for getting the newly added products
    public function scopeNewArrivals(Builder $query, int $limit = 12): Builder
    {
        return $query->orderByDesc('created_at')->limit($limit);
    }


    /**
     * Get all of the variants for the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }


      public function options()
    {
        return $this->hasMany(ProductOption::class);
    }

    /**
     * Get all of the images for the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Get the category that owns the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class,"category_id");
    }

    /**
     * Get the discount associated with the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function discount(): HasOne
    {
        return $this->hasOne(ProductDiscount::class);
    }


    /**
     * Get all of the carts for the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * Get all of the wishlists for the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }


    /**
     * Get all of the orderItems for the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get all of the stat for the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stat(): HasMany
    {
        return $this->hasMany(ProductStat::class);
    }

    /**
     * Get the seller that owns the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * Get all of the reviews for the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(
            OrderReview::class,
            OrderItem::class,
            'product_id',       // Foreign key on order_items table
            'order_item_id',    // Foreign key on order_reviews table
            'id',               // Local key on products table
            'id'                // Local key on order_items table
        )->where('approved', true); // Only approved reviews
    }
}
