<?php

namespace App\Http\Resources\FullfillmentLocations;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\FullfillmentLocations\CityResource;

class StateWithCitiesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
        'id' => $this->id,
        'name' => $this->state_name,
        'country_id' => $this->fullfillment_country_id,
        'cities' => $this->relationLoaded('cities')
            ? CityResource::collection($this->cities)
            : null
        ];
    }
}
