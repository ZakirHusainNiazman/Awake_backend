<?php

namespace App\Http\Resources\User\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\Order\OrderItemResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_amount' => $this->total_amount,
            'payment_status' => $this->payment_status,
            'shipping_address_id' => $this->shipping_address_id,
            'status'=> $this->status,
            'order_number' => $this->order_number,
            'user_id' => $this->user_id,
            "date"=> $this->created_at,
            'items'=>OrderItemResource::collection($this->items),
        ];
    }
}
