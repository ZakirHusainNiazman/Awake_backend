<?php

namespace App\Models\Seller\Product;

use App\Models\Seller\Product\Product;
use Illuminate\Database\Eloquent\Model;
use App\Models\Seller\Product\ProductOptionValue;

class ProductOption extends Model
{
    protected $table = 'product_options';

    protected $fillable= ['id','product_id','name','type'];






     public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the values for the option.
     */
    public function values()
    {
        return $this->hasMany(ProductOptionValue::class);
    }
}



