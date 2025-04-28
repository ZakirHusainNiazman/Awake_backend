<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         return [
            'id'                    => $this->id,
            'address_line1'        => $this->address_line1,
            'address_line2'        => $this->address_line2,
            'postal_code'          => $this->postal_code,
            'phone'                => $this->phone,
            'is_default'           => (bool) $this->is_default,
            'country' =>$this->country?->country_name,
            'state'=>$this->state?->state_name ,
            'city' =>$this->city?->city_name,
            // raw IDs in case the client needs them
            'country_id'     => $this->fullfillment_country_id,
            'state_id'       => $this->fullfillment_state_id,
            'city_id'        => $this->fullfillment_city_id,
        ];
    }
}
