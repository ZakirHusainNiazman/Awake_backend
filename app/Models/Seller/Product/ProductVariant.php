<?php

namespace App\Models\Seller\Product;

use Illuminate\Support\Str;
use App\Models\User\Order\OrderItem;
use App\Models\Seller\Product\Product;
use Illuminate\Database\Eloquent\Model;
use App\Models\Seller\Product\VariantDiscount;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Seller\Product\ProductVariantOptionValue;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'stock',
        'fulfillment_type',
        'image',
        'attributes',
        'has_discount',
        'discount_amount', 'discount_start', 'discount_end'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'attributes' => 'array',    // Converts JSON to array automatically
        'price' => 'decimal:2',     // Always formats price with 2 decimal places
        'stock' => 'integer',       // Ensures stock is treated as an integer
        'discount_start'  => 'datetime',
      'discount_end'    => 'datetime',
      'has_discount'=>'boolean'
    ];


    // heplers for discoount

    // Accessor: This will automatically convert the attributes field to an array when you retrieve it
    public function getAttributesAttribute($value)
    {
        // Convert the stored JSON string to an array (or object if needed)
        return json_decode($value, true); // true returns an array, false would return an object
    }


   public function getActiveDiscountAmount()
{
    if (!$this->has_discount || !$this->discount_amount) {
        return null;
    }

    $now = now()->timezone('UTC'); // 👈 Use UTC

    if ($this->discount_start && $now->lt($this->discount_start)) {
        return null;
    }

    if ($this->discount_end && $now->gt($this->discount_end)) {
        return null;
    }

    return $this->discount_amount;
}

    public function isDiscountValid()
    {
        if (!$this->has_discount || !$this->discount_amount) {
            return false;
        }

        $now = now()->timezone('UTC'); // 👈 Force UTC

        // Check if current UTC time is within discount period
        $isAfterStart = $this->discount_start ? $now >= $this->discount_start : true;
        $isBeforeEnd = $this->discount_end ? $now <= $this->discount_end : true;

        return $isAfterStart && $isBeforeEnd;
    }


    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the option values for the variant.
     */
   public function optionValues()
    {
      // pivot table: product_variant_option_values
      return $this->belongsToMany(
        ProductOptionValue::class,
        'product_variant_option_values',
        'product_variant_id',
        'product_option_value_id'
      )->withTimestamps();
    }



     protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid(); // Generate UUID if not provided
            }
        });
    }


    /**
     * Get the discount associated with the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function discount(): HasOne
    {
        return $this->hasOne(VariantDiscount::class,'product_variant_id','id');
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


}
