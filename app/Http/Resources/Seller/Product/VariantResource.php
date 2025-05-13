<?php

namespace App\Http\Resources\Seller\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
        'id'           => $this->id,
        'sku'          => $this->sku,
        'price'        => $this->price,
        'stock'        => $this->stock,
        'image'        => url($this->image),
        'attributes'   => $this->attributes,        // now an array
        'optionValues' => $this->optionValues->map(fn($v) => [
            'optionName' => $v->option->name,
            'value'      => $v->value,
        ]),
        'discount' => $this->has_discount && $this->discount
                    ? [
                        'hasDiscount' => $this->discount->isValid(),
                        'amount' => $this->discount->getActiveAmount(),
                        "discountStart"=>$this->discount->discount_start,
                        "discountEnd"=>$this->discount->discount_end,
                    ]
                    : [
                        'hasDiscount' => false,
                        'amount' => null,
                    ],

        ];
    }
}
