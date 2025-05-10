<?php

namespace App\Http\Requests\User\Wishlist;

use App\Models\Seller\Product\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreWishlistItemRequest extends FormRequest
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
            'product_id' => 'required|uuid|exists:products,id',
            'variant_id' => 'nullable|uuid|exists:product_variants,id',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'The product ID is required.',
            'product_id.uuid' => 'The product ID must be a valid UUID.',
            'product_id.exists' => 'The selected product does not exist.',

            'variant_id.uuid' => 'The variant ID must be a valid UUID.',
            'variant_id.exists' => 'The selected variant does not exist in product variants.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $productId = $this->input('product_id');

            if ($productId && Product::where('id', $productId)->where('has_variants', true)->exists()) {
                if (!$this->filled('variant_id')) {
                    $validator->errors()->add('variant_id', 'The variant ID is required for this product.');
                }
            }
        });
}


}
