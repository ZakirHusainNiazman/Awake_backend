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
            'addressLine1'        => $this->address_line1,
            'addressLine2'        => $this->address_line2,
            'postalCode'          => $this->postal_code,
            'phone'                => $this->phone,
            'isDefault'           => (bool) $this->is_default,
            'country' =>$this->country?->country_name,
            'state'=>$this->state?->state_name ,
            'city' =>$this->city?->city_name,
            // raw IDs in case the client needs them
            'countryId'     => $this->fullfillment_country_id,
            'stateId'       => $this->fullfillment_state_id,
            'cityId'        => $this->fullfillment_city_id,
        ];
    }
}
