<?php

namespace App\Models\Seller;

use App\Models\Seller\Seller;
use Illuminate\Support\Str;
use App\Models\Seller\Product\Product;
use Illuminate\Database\Eloquent\Model;
use App\Models\Seller\Product\BrandStat;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    protected $fillable = [
        'name',
        'logo',
    ];



    protected static function booted()
    {
        static::creating(function ($brand) {
            $brand->slug = static::generateUniqueSlug($brand->name);
        });

        static::updating(function ($brand) {
            if ($brand->isDirty('name')) {
                $brand->slug = static::generateUniqueSlug($brand->name, $brand->id);
            }
        });
    }

    protected static function generateUniqueSlug($name, $ignoreId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $i = 1;

        while (static::where('slug', $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $originalSlug . '-' . $i++;
        }

        return $slug;
    }


    /**
     * Get all of the stats for the Brand
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stats(): HasMany
    {
        return $this->hasMany(BrandStat::class);
    }

    /**
     * Get all of the products for the Brand
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
