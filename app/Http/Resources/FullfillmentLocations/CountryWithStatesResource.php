<?php

namespace App\Http\Resources\FullfillmentLocations;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\FullfillmentLocations\StateWithCitiesResource;

class CountryWithStatesResource extends JsonResource
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
        'name' => $this->country_name,
        'flag' => url($this->country_flag),
        'states' => $this->relationLoaded('states') ? StateWithCitiesResource::collection($this->states) : null,

    ];
    }
}
