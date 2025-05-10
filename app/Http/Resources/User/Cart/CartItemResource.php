<?php

namespace App\Http\Resources\User\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Seller\Product\ProductOptionValueResource;
use Illuminate\Support\Collection;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
{
    $variant = $this->variant;

    $productDiscount = $this->product->discount;
    $variantDiscount = $variant && $variant->discount ? $variant->discount : null;

    return [
        'id' => $this->id,
        'productId' => $this->product_id,
        'variantId' => $this->variant_id,
        'quantity' => $this->quantity,

        'product' => [
            'title' => $this->product->title,
            'price' => $variant ? $variant->price : $this->product->base_price,
            'stock' => $this->product->stock,
            'image' => $this->product->images->isNotEmpty()
                ? url($this->product->images[0]->image_url)
                : null,
            'discount' => $productDiscount
                ? [
                    'hasDiscount' => $productDiscount->isValid(),
                    'amount' => $productDiscount->getActiveAmount(),
                    'discountStart' => $productDiscount->discount_start,
                    'discountEnd' => $productDiscount->discount_end,
                ]
                : [
                    'hasDiscount' => false,
                    'amount' => null,
                ],
        ],

        'variant' => $variant ? [
            'id' => $variant->id,
            'price' => $variant->price,
            'stock' => $variant->stock,
            'attributes'   => $this->variant->attributes,
            'image' => url($variant->image),
            'discount' => $variantDiscount
                ? [
                    'hasDiscount' => $variantDiscount->isValid(),
                    'amount' => $variantDiscount->getActiveAmount(),
                    'discountStart' => $variantDiscount->discount_start,
                    'discountEnd' => $variantDiscount->discount_end,
                ]
                : [
                    'hasDiscount' => false,
                    'amount' => null,
                ],
        ] : null,
    ];
}

}
