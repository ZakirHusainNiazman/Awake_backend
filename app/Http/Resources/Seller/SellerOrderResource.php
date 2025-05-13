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
        ];
    }
}
