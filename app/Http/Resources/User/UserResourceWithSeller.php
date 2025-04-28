<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use App\Http\Resources\Seller\SellerResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResourceWithSeller extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'user_type' => $this->user_type,
            'email_verified_at' => $this->email_verified_at,
            'image'=> $this->image ? asset('storage/' . $this->image) : null,
           // Seller relationship (if exists)
            'seller' => $this->seller ? new SellerResource($this->seller) : null,
        ];
    }
}
