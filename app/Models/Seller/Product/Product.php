<?php

namespace App\Models\Seller\Product;

use Illuminate\Database\Eloquent\Model;
use App\Models\Seller\Product\ProductImage;
use App\Models\Seller\Product\ProductOption;
use App\Models\Seller\Product\ProductVariant;
use App\Models\Seller\Product\ProductOptionValue;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Seller\Product\ProductVariantOptionValue;

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
        'discount_amount',
        'has_discount',
      'discount_start','discount_end',
    ];

    // ─ disable auto-incrementing (we’ll supply our own UUIDs)
    public $incrementing = false;

    // ─ keys are stored as strings, not ints
    protected $keyType = 'string';

    // ─ if you like, you can also permanently cast your id
    //    (not required, but can help with serialization)
    protected $casts = [
        'id' => 'string',
    ];


    // helpers
    public function getActiveDiscountAttribute()
    {
        if (!$this->discount_type || !$this->discount_amount) {
            return null;
        }

        $now = now();

        if ($this->discount_start && $now->lt($this->discount_start)) {
            return null;
        }

        if ($this->discount_end && $now->gt($this->discount_end)) {
            return null;
        }

        return [
            'type' => $this->discount_type,
            'amount' => $this->discount_amount,
        ];
    }

    /**
     * Get all of the variants for the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
   public function variants()
{
    return $this->hasMany(ProductVariant::class);
}

public function options()
{
    return $this->hasMany(ProductOption::class);
}

        public function values()
        {
            return $this->hasMany(ProductOptionValue::class);
    }

    public function optionValues()
    {
        return $this->hasMany(ProductVariantOptionValue::class);
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


}
