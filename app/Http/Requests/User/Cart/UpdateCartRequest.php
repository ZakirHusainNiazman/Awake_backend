<?php

namespace App\Http\Requests\User\Cart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartRequest extends FormRequest
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
            'quantity' => 'required|integer|min:1',
        ];
    }

    /**
     * Additional custom validation after the default rules.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $product = Product::find($this->product_id);

            if (!$product) {
                return; // already covered by the rules, but just in case
            }

            // Variant logic
            if ($product->has_variants && !$this->filled('variant_id')) {
                $validator->errors()->add('variant_id', 'variant_id is required for this product.');
            }

            if (!$product->has_variants && $this->filled('variant_id')) {
                $validator->errors()->add('variant_id', 'This product does not support variants.');
            }

            // Stock check
            $quantity = $this->quantity;

            if ($product->has_variants) {
                $variant = ProductVariant::find($this->variant_id);

                if (!$variant || $variant->stock < $quantity) {
                    $validator->errors()->add('quantity', 'Requested quantity exceeds available stock for this variant.');
                }
            } else {
                if ($product->stock < $quantity) {
                    $validator->errors()->add('quantity', 'Requested quantity exceeds available stock for this product.');
                }
            }
        });
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'The product_id field is required.',
            'product_id.exists' => 'The selected product does not exist.',
            'variant_id.exists' => 'The selected variant does not exist.',
            'quantity.required' => 'Quantity is required.',
            'quantity.integer' => 'Quantity must be an integer.',
            'quantity.min' => 'Quantity must be at least 1.',
        ];
    }
}
