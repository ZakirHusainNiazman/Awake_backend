<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use App\Http\Resources\User\Order\OrderResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\Order\OrderItemResource;

class CustomerReportResource extends JsonResource
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
            'issue_type' => $this->issue_type,
            'description' => $this->description,
            'status' => $this->status,
            'resolved_at' => $this->resolved_at ? $this->resolved_at->toDateTimeString() : null,

            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->first_name." ".$this->user->last_name,
                'email' => $this->user->email,
            ],

            'order_item' => new OrderItemResource($this->whenLoaded('orderItem')),

            'order' => $this->relationLoaded('orderItem') && $this->orderItem->relationLoaded('order')
                ? new OrderResource($this->orderItem->order)
                : null,


            'seller' => [
                'id' => $this->orderItem->getSeller()->id, // seller id (seller table id)
                'name' => $this->orderItem->getSeller()->user->first_name." ".$this->orderItem->getSeller()->user->last_name,
                'last_name' => $this->orderItem->getSeller()->user->last_name ?? null,
                'email' => $this->orderItem->getSeller()->user->email ?? null,
                'whatsapp_no' => $this->orderItem->getSeller()->whatsapp_no ?? null,
            ],
        ];
    }
}
