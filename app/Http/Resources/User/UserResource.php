<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use App\Http\Resources\Seller\SellerResource;
use App\Http\Resources\User\PermissionResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
        ];
    }
}
