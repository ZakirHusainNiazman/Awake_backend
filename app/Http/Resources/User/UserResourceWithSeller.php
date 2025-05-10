<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use App\Http\Resources\Seller\SellerResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\Cart\CartItemResource;
use App\Http\Resources\User\Wishlist\WishlistItemResource;

class UserResourceWithSeller extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $cart = $this->whenLoaded('cart');

        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'user_type' => $this->user_type,
            'email_verified_at' => $this->email_verified_at,
            'profile_image'=> $this->profile_image ? url($this->profile_image) : null,
            'cart' => [
                'count' => $cart?->items->count() ?? 0,
                'items' => CartItemResource::collection($cart?->items ?? []),
                'total' => $cart?->items->sum(fn($item) => $item->quantity * ($item->variant?->price ?? $item->product->price)) ?? 0,
            ],
            'wishlist'=> WishlistItemResource::collection($this->wishlist->items),
           // Seller relationship (if exists)
            'seller' => $this->seller ? new SellerResource($this->seller) : null,
        ];
    }
}
