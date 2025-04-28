<?php

namespace App\Models\Seller\Product;

use Illuminate\Database\Eloquent\Model;
use App\Models\Seller\Product\ProductOption;

class ProductOptionValue extends Model
{
    protected $table= 'product_option_values';

    protected $fillable = ['product_option_id','value'];




    public function option()
    {
        return $this->belongsTo(ProductOption::class);
    }



}
