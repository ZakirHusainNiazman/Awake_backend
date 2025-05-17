<?php

namespace App\Models\Seller;

use Illuminate\Database\Eloquent\Model;

class MonthlyTarget extends Model
{
    protected $fillable= [
        'seller_id',
        'month',
        'orders_target',
        'revenue_target',
    ];
}
