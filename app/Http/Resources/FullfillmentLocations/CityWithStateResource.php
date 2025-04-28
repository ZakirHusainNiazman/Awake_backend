<?php

namespace App\Http\Resources\FullfillmentLocations;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityWithStateResource extends JsonResource
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
            'name' => $this->city_name,
            'state_id' => $this->fullfillment_state_id,
            'state' => new StateResource($this->whenLoaded('state')),
        ];;
    }
}
