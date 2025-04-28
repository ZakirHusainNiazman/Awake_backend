<?php

namespace App\Models;

use App\Models\FullfillmentState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FullfillmentCity extends Model
{
    protected $fillable = ['city_name', 'fullfillment_state_id'];

    use SoftDeletes;

    /**
     * Get the user that owns the FullfillmentCity
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function state()
    {
        return $this->belongsTo(FullfillmentState::class, 'fullfillment_state_id')->whereNull('deleted_at');
    }
}
