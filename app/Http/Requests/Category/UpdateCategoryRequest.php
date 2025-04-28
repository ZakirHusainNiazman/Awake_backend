<?php

namespace App\Http\Requests\Category;

use DB;
use Log;
use Illuminate\Validation\Rule;
use App\Models\Category\Category;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
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
        $slug = $this->route('category');  // You get the bound category model automatically
        $categoryId = null;
        if($slug){
            $category = Category::withTrashed()->where('slug', $slug)->first();

            if($category){
                $categoryId = $category->id;
            }
        }

        Log::info('Category from route: ', ['category' => $category]);



        return [
            // ensure we know when it's a child
            'isChild'     => ['required', 'boolean'],
            'is_active'     => ['required', 'boolean'],

            'commission_rate' => 'required|numeric|between:0,100',
            'category_icon'   => [
                'nullable',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:2048',
            ],

            'parent_id' => [
                Rule::requiredIf(fn() => $this->boolean('isChild')),

                'exists:categories,id',
                function ($attribute, $value, $fail) use ($categoryId) {
                    if ($value && $categoryId && $value == $categoryId) {
                        $fail('A category cannot be its own parent.');
                    }
                },
            ],

            'slug' => 'prohibited',
        ];
    }

    public function messages(): array
    {
        return [
            //ischild
            'isChild.required' => 'isChild rate is required.',
            'isChild.boolean' => 'isChild rate must be boolean type.',

            //is_active
            'isChild.required' => 'is active is required.',
            'isChild.boolean' => 'is active must be boolean type.',

            // commission_rate
            'commission_rate.required' => 'Commission rate is required.',
            'commission_rate.numeric' => 'Commission rate must be a number.',
            'commission_rate.between' => 'Commission rate must be between 0 and 100.',

            // category_icon
            'category_icon.mimes'    => 'Allowed flag formats: jpeg, png, jpg, gif, svg.',
            'category_icon.max'      => 'The category icon may not be greater than 2 MB.',

            // parent_id
            'parent_id.exists' => 'The selected parent category does not exist.',
            'parent_id.require' => 'Please selecte parent category.',

            'slug.prohibited' => 'You cannot set the slug manually.',

            // name
            'name.unique' => 'The category name has already been taken.',
            'name.required' => 'Category name is required.',
            'name.min' => 'Category name must be at least 2 characters.',
            'name.max' => 'Category name must not exceed 100 characters.',
            'name.string' => 'Category name must be a string.',
            'locale.required' => 'Locale is required.',
            'locale.in' => 'Locale must be "en".',
        ];
    }
}
