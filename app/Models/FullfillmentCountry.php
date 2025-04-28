<?php

namespace App\Models;

use App\Models\FullfillmentState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FullfillmentCountry extends Model
{
    protected $fillable = ['country_name', 'country_flag'];
    use SoftDeletes;

    /**
     * Get all of the states for the FullfillmentCountries
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function states(): HasMany
    {
        return $this->hasMany(FullfillmentState::class)->whereNull('deleted_at');
    }


    protected static function booted()
    {
        static::deleting(function ($country) {
            if (! $country->isForceDeleting()) {
                $country->states()->each(function ($state) {
                    $state->delete(); // Triggers state deleting event
                });
            }
        });
    }
}
