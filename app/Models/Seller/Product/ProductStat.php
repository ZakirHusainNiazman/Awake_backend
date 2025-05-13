<?php

namespace App\Models\Seller\Product;

use Illuminate\Database\Eloquent\Model;
use App\Models\Seller\Product\ProductStat;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStat extends Model
{
    protected $fillable = [
        'product_id',
        'event',
    ];


    /**
     * Get the product that owns the ProductStat
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductStat::class);
    }
}
