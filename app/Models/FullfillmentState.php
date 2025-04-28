<?php

namespace App\Models;

use App\Models\FullfillmentCity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FullfillmentState extends Model
{
    protected $fillable = ['state_name', 'fullfillment_country_id'];

    use SoftDeletes;

    /**
     * Get all of the cities for the FullfillmentStates
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cities(): HasMany
    {
        return $this->hasMany(FullfillmentCity::class)->whereNull('deleted_at');
    }

    public function country()
    {
        return $this->belongsTo(FullfillmentCountry::class, 'country_id');
    }

    protected static function booted()
    {
        static::deleting(function ($state) {
            if (! $state->isForceDeleting()) {
                $state->cities()->each(function ($city) {
                    $city->delete();
                });
            }
        });
    }
}
