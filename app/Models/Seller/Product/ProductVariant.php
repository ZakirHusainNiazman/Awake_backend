<?php

namespace App\Models\Seller\Product;

use Illuminate\Support\Str;
use App\Models\Seller\Product\Product;
use Illuminate\Database\Eloquent\Model;
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
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'attributes' => 'array',    // Converts JSON to array automatically
        'price' => 'decimal:2',     // Always formats price with 2 decimal places
        'stock' => 'integer',       // Ensures stock is treated as an integer
    ];




    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the option values for the variant.
     */
    public function optionValues()
{
    return $this->hasMany(ProductVariantOptionValue::class,'id');
}


     protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid(); // Generate UUID if not provided
            }
        });
    }

}
