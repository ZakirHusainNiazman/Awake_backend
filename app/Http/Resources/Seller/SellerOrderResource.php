<?php

namespace App\Http\Resources\Seller;

use Illuminate\Http\Request;
use App\Http\Resources\User\Order\OrderResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\Order\OrderItemResource;

class SellerOrderResource extends JsonResource
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
            'total' => $this->total,
            'subtotal' => $this->subtotal,
            'shipping_cost' => $this->shipping_cost,
            'items' => OrderItemResource::collection($this->items), // Collection of order items
           'customer' => $this->whenLoaded('order', function () {
                return $this->order->user ? [
                    'id' => $this->order->user->id,
                    'name' => $this->order->user->first_name.$this->order->user->first_name,
                    'email' => $this->order->user->email,
                ] : null;
            }),
            'shipping_address' => $this->whenLoaded('order', function () {
                return $this->order->shippingAddress ? [
                    'phone_number'=>$this->order->shippingAddress->phone,
                    'address_line1' => $this->order->shippingAddress->address_line1,
                    'address_line2' => $this->order->shippingAddress->address_line2,
                    'city' => $this->order->shippingAddress->city->city_name,
                    'state' => $this->order->shippingAddress->state->state_name,
                    'country' => $this->order->shippingAddress->country->country_name,
                ] : null;
            }),
        ];
    }
}
