<?php

namespace App\Models\Seller\Product;

use Illuminate\Database\Eloquent\Model;

class VariantDiscount extends Model
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

        if (!$this->is_valid || !$this->amount) return null;
        if ($this->start_at && $now->lt($this->start_at)) return null;
        if ($this->end_at && $now->gt($this->end_at)) return null;

        return $this->amount;
    }

    public function isValid()
    {
        $now = now();

        if (!$this->is_valid || !$this->amount) return false;
        if ($this->start_at && $now->lt($this->start_at)) return false;
        if ($this->end_at && $now->gt($this->end_at)) return false;

        return true;
    }
}
