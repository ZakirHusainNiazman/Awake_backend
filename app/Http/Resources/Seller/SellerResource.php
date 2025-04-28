<?php

namespace App\Http\Resources\Seller;

use Illuminate\Http\Request;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\AddressResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
        // Eager-loaded user data (via UserResource)
        "id"=>$this->id,
        'account_status' => $this->account_status,
        'account_type' => $this->account_type,
        'dob' => $this->dob,
        'whatsapp_no' => $this->whatsapp_no,
        'store_name' => $this->store_name,
        'business_description' => $this->business_description,
        'identity_type' => $this->identity_type,
        'proof_of_identity' => $this->proof_of_identity ? url($this->proof_of_identity) : null,
        //return only the default address
        'address' => new AddressResource(
            $this->user?->addresses?->firstWhere('is_default', true)
        ),
        // Conditionally include brands only for business accounts
        'brand' => $this->when(
            $this->account_type === 'business',
            BrandResource::make($this->whenLoaded('brand'))
        ),
        'created_at' => $this->created_at,
    ];
    }
}
