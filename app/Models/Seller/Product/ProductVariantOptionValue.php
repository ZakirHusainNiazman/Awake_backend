<?php


namespace App\Models\Seller\Product;

use Illuminate\Database\Eloquent\Model;
use App\Models\Seller\Product\ProductOption;
use App\Models\Seller\Product\ProductVariant;
use App\Models\Seller\Product\ProductOptionValue;

class ProductVariantOptionValue extends Model
{
    protected $table = "product_variant_option_values";
    protected $fillable = ['product_variant_id', 'product_option_value_id'];  // Corrected field names

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');  // Foreign key is 'product_variant_id'
    }

    /**
     * Get the product option value associated with the variant.
     */
    public function productOptionValue()
    {
        return $this->belongsTo(ProductOptionValue::class);  // Foreign key is 'product_option_value_id'
    }

//     public function option()
// {
//     return $this->belongsTo(ProductOption::class, 'product_option_id'); // Fix here
// }

}
