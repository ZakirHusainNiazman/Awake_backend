<?php

namespace App\Http\Requests\Seller\Product;

use App\Rules\UniqueVariantSku;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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

        Log::info("product data",$this->all());
        return [
            // core product fields
            'category_id'    => ['required', 'exists:categories,id'],
            'sku'            => ['required', 'string', 'max:255',Rule::unique('products', 'sku')->ignore($this->route('id'))],
            'title'          => ['required', 'string', 'max:255'],
            'details'        => ['required', 'string'],
            'stock'          => ['required', 'integer', 'min:0'],
            'description'    => ['required', 'string'],
            'base_price'     => ['required', 'numeric', 'min:0'],
            'has_variants'   => ['required', 'boolean'],

            // discount fields
            'has_discount'    => ['required', 'boolean'],
            'discount_amount' => [
                'nullable', 'numeric', 'between:0.01,100',
                Rule::requiredIf(fn() => $this->boolean('has_discount'))
            ],
            'discount_start'  => [
                'nullable', 'date',
                Rule::requiredIf(fn() => $this->boolean('has_discount'))
            ],
            'discount_end'    => [
                'nullable', 'date', 'after_or_equal:discount_start',
                Rule::requiredIf(fn() => $this->boolean('has_discount'))
            ],

            'images.*'         => ['image', 'mimes:svg,png,jpg,jpeg,webp,gif'],

            // variants when has_variants=true
            'variants' => ['array', Rule::requiredIf(fn() => $this->boolean('has_variants'))],
            'variants.*.sku' => [
                Rule::requiredIf(fn () => $this->boolean('has_variants')),
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    // Extract index from attribute like "variants.0.sku"
                    if (preg_match('/^variants\.(\d+)\.sku$/', $attribute, $matches)) {
                        $index = $matches[1];
                        $variantId = $this->input("variants.$index.id");
                        $rule = new UniqueVariantSku($variantId);

                        if (! $rule->passes($attribute, $value)) {
                            $fail($rule->message());
                        }
                    }
                }
            ],
            'variants.*.price' => [
            Rule::requiredIf(fn () => $this->boolean('has_variants')),
            'numeric',
            'min:0'
            ],
            'variants.*.stock' => [
                Rule::requiredIf(fn () => $this->boolean('has_variants')),
                'integer',
                'min:0'
            ],
            'variants.*.image' => [
                Rule::requiredIf(fn () => $this->boolean('has_variants')),
                'nullable', // allow existing images
                'image',
                'mimes:svg,png,jpg,jpeg,webp,gif',
            ],
            'variants.*.attributes' => [
                Rule::requiredIf(fn () => $this->boolean('has_variants')),
                'array',
                function ($attribute, $value, $fail) {
                    $options = $this->input('options', []);
                    $optionMap = collect($options)->mapWithKeys(function ($option) {
                        $name = $option['name'];
                        $values = match ($option['type']) {
                            'images' => collect($option['imageValues'] ?? [])->pluck('label')->all(),
                            default  => $option['values'] ?? [],
                        };
                        return [$name => $values];
                    })->toArray();

                    $validOptionNames = array_keys($optionMap);

                    foreach ($value as $key => $val) {
                        if (!in_array($key, $validOptionNames)) {
                            $fail("Variant attribute key '{$key}' is not defined in options.");
                        } elseif (!in_array(strtolower($val), array_map('strtolower', $optionMap[$key] ?? []))) {
                            $fail("Variant attribute value '{$val}' is invalid for '{$key}'.");
                        }
                    }
                }
            ]
        ];
    }

    public function messages(): array
    {
        return [
            // core
            'category_id.required'          => 'Please select a valid category.',
            'category_id.exists'            => 'Selected category does not exist.',
            'sku.required'                  => 'SKU is required.',
            'sku.unique'                    => 'This SKU has already been taken.',
            'title.required'                => 'Product title is required.',
            'details.required'              => 'Product details are required.',
            'stock.required'                => 'Stock quantity is required.',
            'stock.integer'                 => 'Stock must be an integer.',
            'stock.min'                     => 'Stock cannot be negative.',
            'description.required'          => 'Description is required.',
            'base_price.required'           => 'Base price is required.',
            'base_price.numeric'            => 'Base price must be a number.',
            'base_price.min'                => 'Base price must be at least 0.',
            'has_variants.required'         => 'Please specify if product has variants.',


            // discount
            'has_discount.required'         => 'Please specify if product has a discount.',
            'discount_amount.required'      => 'Please provide the discount amount.',
            'discount_amount.between'       => 'Discount amount must be between 0.01 and 100.',
            'discount_amount.numeric'       => 'Discount amount must be numeric.',
            'discount_start.required'       => 'Discount start date is required.',
            'discount_start.date'           => 'Discount start must be a valid date.',
            'discount_end.required'         => 'Discount end date is required.',
            'discount_end.after_or_equal'   => 'The discount end date must be after or equal to the start date.',

            // attributes
            'attributes.required'           => 'Attributes are required when variants are disabled.',
            'attributes.array'              => 'Attributes must be an array.',
            'attributes.*.required'         => 'Please fill the :attribute attribute.',


            // variants
            'variants.array'                => 'Variants must be an array.',
            'variants.*.price.required_with'=> 'Each variant must have a price.',
            'variants.*.stock.required_with'=> 'Each variant must have a stock quantity.',
            'variants.*.image.required_with'=> 'Each variant must have exactly one image.',
            'variants.*.image.image'        => 'Variant image must be a valid image file.',
            'variants.*.image.mimes'        => 'Variant image must be one of: svg, png, jpg, jpeg, webp, gif.',
            'variants.*.attributes.array'   => 'Each variant must have an attributes array.',

            // product images
            'images.required'               => 'At least four product images are required.',
            'images.array'                  => 'Images must be an array.',
            'images.min'                    => 'At least four images are required for the product.',
            'images.*.image'                => 'Each file must be an image.',
            'images.*.mimes'                => 'Allowed image types: svg, png, jpg, jpeg, webp, gif.',
        ];
    }
}
