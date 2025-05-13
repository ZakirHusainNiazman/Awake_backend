<?php

namespace App\Http\Requests\Seller\Product;

use App\Rules\UniqueVariantSku;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Models\Admin\Attribute as ProductAttribute;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        Log::info("product",$this->all());
        // Normalize 'undefined' values to null for attributes
        $attrs = $this->input('attributes', []);
        foreach ($attrs as $k => $v) {
            if ($v === 'undefined') {
                $attrs[$k] = null;
            }
        }

        // Normalize options to always have 'values' and 'imageValues'
        $options = $this->input('options', []);
        foreach ($options as &$option) {
            if (!array_key_exists('values', $option)) {
                $option['values'] = [];
            }
            if (!array_key_exists('imageValues', $option)) {
                $option['imageValues'] = [];
            }
        }

        $this->merge([
            'attributes' => $attrs,
            'options'    => $options,
        ]);
    }

    public function rules(): array
    {
        $hasVariants = $this->boolean('has_variants');
        $categoryId  = $this->category_id;

        Log::info("data=>", $this->all());

        $rules = [
            // core product fields
            'category_id'    => ['required', 'exists:categories,id'],
            'sku'            => ['required', 'string', 'max:255', 'unique:products,sku'],
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

            // options when variants exist
            'options'                       => ['array', Rule::requiredIf(fn() => $hasVariants)],
            'options.*.name'                => ['required_with:options', 'string'],
            'options.*.type'                => [
                'required_with:options',
                Rule::in(['select','text','images'])
            ],

            // for select/text options
            'options.*.values' => [
                function ($attribute, $value, $fail) {
                    // Extract the index from the attribute, like "options.0.values"
                        preg_match('/options\.(\d+)\.values/', $attribute, $matches);
                        $index = $matches[1] ?? null;

                        $options = $this->input('options', []);
                        $type = $options[$index]['type'] ?? null;

                        if ($type !== 'images' && (is_null($value) || $value === [])) {
                            $fail('The ' . $attribute . ' field is required when type is not "images".');
                        }
                },
            ],
            'options.*.values.*'            => ['string'],

            // for image-swatch options
            'options.*.imageValues' => [
                'array',
                function ($attribute, $value, $fail) {
                    preg_match('/options\.(\d+)\.imageValues/', $attribute, $matches);
                    $index = $matches[1] ?? null;
                    $type = $this->input("options.{$index}.type");

                    if ($type === 'images' && (empty($value) || count($value) < 1)) {
                        $fail("The {$attribute} field must have at least 1 item when type is 'images'.");
                    }
                },
            ],

            'options.*.imageValues.*.label' => ['required_with:options.*.imageValues', 'string'],
            'options.*.imageValues.*.file'  => ['required_with:options.*.imageValues', 'image', 'mimes:svg,png,jpg,jpeg,webp,gif'],

            // variants when has_variants=true
            'variants'              => ['array', Rule::requiredIf(fn() => $hasVariants)],
            'variants.*.sku' => [
                Rule::requiredIf(fn () => $this->boolean('has_variants')),
                'string',
                'max:255',
                new UniqueVariantSku(),
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
                'image',
                'mimes:svg,png,jpg,jpeg,webp,gif'
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
            ],

            // product images: require at least 4
            'images'           => ['array', 'min:4', 'required'],
            'images.*'         => ['image', 'mimes:svg,png,jpg,jpeg,webp,gif'],
        ];

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $variants = $this->input('variants', []);
            foreach ($variants as $index => $variant) {
                $hasDiscount = filter_var($variant['has_discount'] ?? false, FILTER_VALIDATE_BOOLEAN);

                if ($hasDiscount) {
                    if (empty($variant['discount_amount'])) {
                        $validator->errors()->add("variants.$index.discount_amount", 'Discount amount is required.');
                    }
                    if (empty($variant['discount_start'])) {
                        $validator->errors()->add("variants.$index.discount_start", 'Discount start date is required.');
                    }
                    if (empty($variant['discount_end'])) {
                        $validator->errors()->add("variants.$index.discount_end", 'Discount end date is required.');
                    } elseif (!empty($variant['discount_start']) && $variant['discount_end'] < $variant['discount_start']) {
                        $validator->errors()->add("variants.$index.discount_end", 'Discount end must be after or equal to start.');
                    }
                }
            }
        });
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

            // attributes
            'attributes.required'           => 'Attributes are required when variants are disabled.',
            'attributes.array'              => 'Attributes must be an array.',
            'attributes.*.required'         => 'Please fill the :attribute attribute.',

            // options
            'options.required'              => 'At least one option is required when variants are enabled.',
            'options.array'                 => 'Options must be an array.',
            'options.*.name.required_with'  => 'Each option must have a name.',
            'options.*.type.required_with'  => 'Each option must have a type.',
            'options.*.type.in'             => 'Option type is invalid.',
            'options.*.values.required_if'  => 'Each select/text option must have at least one value.',
            'options.*.values.array'        => 'Option values must be an array.',
            'options.*.values.*.string'     => 'Each option value must be a string.',
            'options.*.imageValues.required_if'       => 'Each image option must have at least one swatch.',
            'options.*.imageValues.array'             => 'Image swatches must be an array.',
            'options.*.imageValues.*.label.required_with' => 'Each swatch must have a label.',
            'options.*.imageValues.*.file.image'      => 'Each swatch must be a valid image file.',
            'options.*.imageValues.*.file.mimes'      => 'Swatch images must be one of: svg, png, jpg, jpeg, webp, gif.',

            // variants
            'variants.required'             => 'Variants array is required when has_variants is true.',
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

            // discount
            'has_discount.required'         => 'Please specify if product has a discount.',
            'discount_amount.required'      => 'Please provide the discount amount.',
            'discount_amount.between'       => 'Discount amount must be between 0.01 and 100.',
            'discount_amount.numeric'       => 'Discount amount must be numeric.',
            'discount_start.required'       => 'Discount start date is required.',
            'discount_start.date'           => 'Discount start must be a valid date.',
            'discount_end.required'         => 'Discount end date is required.',
            'discount_end.after_or_equal'   => 'The discount end date must be after or equal to the start date.',
        ];
    }
}
