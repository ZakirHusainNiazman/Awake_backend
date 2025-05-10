<?php

namespace App\Http\Resources\Seller\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Category\CategoryResource;
use App\Http\Resources\Seller\Product\VariantResource;

class UpdateProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'       => $this->id,
            'title'    => $this->title,
            'description'=>$this->description,
            'details'=>$this->details,
            'stock'=>$this->stock,
            'price'=>$this->base_price,
            'sku'=>$this->sku,
            'status'=>$this->status,
            'condition' => $this->condition,
            'discount' => $this->has_discount && $this->discount
                    ? [
                        'hasDiscount' => $this->has_discount,
                        'amount' => $this->discount->discount_amount,
                        "discountStart"=>$this->discount->discount_start,
                        "discountEnd"=>$this->discount->discount_end,
                    ]
                    : [
                        'hasDiscount' => false,
                        'amount' => null,
                    ],

            'category'=> new CategoryResource($this->whenLoaded('category')),
            'options' => $this->options->map(function ($o) {
                $option = [
                    'name' => $o->name,
                    'type' => $o->type,
                ];

                if ($o->type === 'images') {
                    // Include ALL values, even those without images
                    $option['imageValues'] = $o->values->map(function ($v) {
                        return [
                            'label' => $v->value,
                            'file' => $v->image_path ? url($v->image_path) : null,
                        ];
                    });
                } else {
                    // Handle other types
                    $option['values'] = $o->values->pluck('value');
                }

                return $option;
            }),
            'hasVariants'=>$this->has_variants,
            'variants' => VariantResource::collection($this->variants),
            'images' => $this->images->map(function ($image) {
                return [
                    'id' => $image->id,  // Send the image ID for existing images
                    'image' => url($image->image_url),  // Send the image URL (or the actual file for new images)
                    'is_new' => false,  // Mark as false for existing images
                ];
            }),
        ];
    }
}
