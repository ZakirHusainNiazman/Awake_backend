<?php

namespace App\Models\Seller\Product;

use Illuminate\Database\Eloquent\Model;
use App\Models\Seller\Product\ProductOption;

class ProductOptionValue extends Model
{
    protected $table= 'product_option_values';

    protected $fillable = ['product_option_id','image_path','value'];




    public function option()
    {
        return $this->belongsTo(ProductOption::class, 'product_option_id');
    }

    public function variants()
    {
      return $this->belongsToMany(
        ProductVariant::class,
        'product_variant_option_values',
        'product_option_value_id',
        'product_variant_id'
      )->withTimestamps();
    }


}
