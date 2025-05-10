<?php

namespace App\Http\Requests\User\Cart;

use App\Models\Seller\Product\Product;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Seller\Product\ProductVariant;

class AddToCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'nullable|integer|min:1',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $product = Product::find($this->product_id);

            // If the product has variants but no variant_id is provided
            if ($product && $product->has_variants && !$this->filled('variant_id')) {
                $validator->errors()->add('variant_id', 'variant_id is required for this product.');
            }

            // If the product has no variants but variant_id is provided
            if ($product && !$product->has_variants && $this->filled('variant_id')) {
                $validator->errors()->add('variant_id', 'This product has no variants. Do not include variant_id.');
            }

            // Validate stock quantity
            if ($this->filled('quantity')) {
                $quantity = $this->quantity;

                // Check stock for variant or product
                if ($product->hasVariants) {
                    // Validate against the stock of the specified variant
                    $variant = ProductVariant::find($this->variant_id);
                    if ($variant && $variant->stock < $quantity) {
                        $validator->errors()->add('quantity', 'Requested quantity exceeds the stock available for this variant.');
                    }
                } else {
                    // Validate against the stock of the product
                    if ($product->stock < $quantity) {
                        $validator->errors()->add('quantity', 'Requested quantity exceeds the stock available for this product.');
                    }
                }
            }
        });
    }


    public function messages()
    {
        return [
            'product_id.required' => 'The product_id field is required.',
            'product_id.exists' => 'The selected product does not exist.',
            'variant_id.exists' => 'The selected variant does not exist.',
            'quantity.integer' => 'The quantity must be a valid integer.',
            'quantity.min' => 'The quantity must be at least 1.',
        ];
    }
}
