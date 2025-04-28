<?php

namespace App\Http\Requests\Category;

use Illuminate\Validation\Rule;
use App\Models\Category\Category;
use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Get the category ID from the route (for updates)
        $categoryId = $this->route('slug')
        ? Category::where('slug', $this->route('slug'))->value('id')
        : null;

        return [
            // ensure we know when it's a child
            'name'=>['required','string','max:50'],
            'isChild'     => ['required', 'boolean'],
            'commission_rate' => 'required|numeric|between:0,100',
            'category_icon' =>[
                'required',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:2048', // max file size in kilobytes (2 MB),
            ],

            'parent_id' => [
                'nullable',
                'exists:categories,id',
                function ($attribute, $value, $fail) use ($categoryId) {
                    if ($value && $categoryId && $value == $categoryId) {
                        $fail('A category cannot be its own parent.');
                    }
                },
            ],

            'slug' => 'prohibited', // Prevent clients from manually setting slug

        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'name.required' => 'Category name must be string.',
            'name.required' => 'Category name must be at most 50 chars.',

            // isChild
            'isChild.required' => 'Please specify whether this is a child category.',
            'isChild.boolean' => 'The child category indicator must be true or false.',

            // commission_rate
            'commission_rate.required' => 'Commission rate is required.',
            'commission_rate.numeric' => 'Commission rate must be a number.',
            'commission_rate.between' => 'Commission rate must be between 0 and 100.',

            // category_icon
            'category_icon.required' => 'Category icon is required.',
            'category_icon.mimes'    => 'Allowed flag formats: jpeg, png, jpg, gif, svg.',
            'category_icon.max'      => 'The category icon may not be greater than 2 MB.',

            // parent_id
            'parent_id.exists' => 'The selected parent category does not exist.',

            'slug.prohibited' => 'You cannot set the slug manually.',

            // name
            'name.unique' => 'The category name has already been taken.',
            'name.required' => 'Category name is required.',
            'name.min' => 'Category name must be at least 2 characters.',
            'name.max' => 'Category name must not exceed 100 characters.',
            'name.string' => 'Category name must be a string.',
        ];
    }
}


