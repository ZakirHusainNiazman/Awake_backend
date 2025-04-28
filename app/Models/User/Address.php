<?php

namespace App\Models\User;

use App\Models\FullfillmentCity;
use App\Models\FullfillmentState;
use App\Models\FullfillmentCountry;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'fullfillment_country_id',
        'fullfillment_state_id',
        'fullfillment_city_id',
        'address_line1',
        'address_line2',
        'postal_code',
        'phone',
        'is_default',
    ];


    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function country()
    {
        return $this->belongsTo(FullfillmentCountry::class, 'fullfillment_country_id');
    }

    public function state()
    {
        return $this->belongsTo(FullfillmentState::class, 'fullfillment_state_id');
    }

    public function city()
    {
        return $this->belongsTo(FullfillmentCity::class, 'fullfillment_city_id');
    }
}
