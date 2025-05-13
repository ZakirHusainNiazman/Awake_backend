<?php

namespace App\Models\Seller\Product;

use Illuminate\Database\Eloquent\Model;

class ProductDiscount extends Model
{
    protected $fillable = [
        'discount_amount',
      'discount_start','discount_end',
    ];

     protected $casts = [
      'discount_start'  => 'datetime',
      'discount_end'    => 'datetime',
    ];


   public function getActiveAmount()
    {
        $now = now();

        if (!$this->discount_amount) return null;
        if ($this->discount_start && $now->lt($this->discount_start)) return null;
        if ($this->discount_end && $now->gt($this->discount_end)) return null;

        return $this->discount_amount;
    }

   public function isValid()
    {
        $now = now();

        if (!$this->discount_amount) return false;
        if ($this->discount_start && $now->lt($this->discount_start)) return false;
        if ($this->discount_end && $now->gt($this->discount_end)) return false;

        return true;
    }
}
