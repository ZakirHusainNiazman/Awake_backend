<?php

namespace App\Http\Resources\User\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderReviewResource extends JsonResource
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
            'order_item_id' => $this->order_item_id,
            'user' => $this->user_id,
            'rating' => $this->rating,
            'review' => $this->review,
            'approved' => $this->approved,
            'date' => $this->created_at,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->first_name . ' ' . $this->user->last_name, // Concatenate first name and last name
                'profile_image' => url($this->user->profile_image),
            ],
        ];
    }
}
