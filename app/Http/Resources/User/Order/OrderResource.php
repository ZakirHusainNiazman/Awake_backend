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
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'total' => $this->total_amount,
            'subtotal' => $this->subtotal,
            'shipping_cost' => $this->shipping_cost,
            'items' => OrderItemResource::collection($this->items),
            'shipping_address' => $this->whenLoaded('shippingAddress', function () {
                return $this->shippingAddress ? [
                    'phone_number'=>$this->shippingAddress->phone,
                    'address_line1' => $this->shippingAddress->address_line1,
                    'address_line2' => $this->shippingAddress->address_line2,
                    'city' => $this->shippingAddress->city->city_name,
                    'state' => $this->shippingAddress->state->state_name,
                    'country' => $this->shippingAddress->country->country_name,
                ] : null;
            }),
        ];

    }
}
