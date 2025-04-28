<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttributeRequest extends FormRequest
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
   public function rules()
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name'        => ['required', 'string', 'max:255','unique:attributes,name'],
            'type'        => ['required', 'in:text,number,boolean,select,image'],
            'options'     => ['nullable', 'json','required_if:type,select'],
            'options.*'   => ['required_with:options', 'string'],
            'is_required' => ['required','boolean'],
            'is_global' => ['required','boolean'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $isRequired = $this->boolean('is_required');
            $isGlobal = $this->boolean('is_global');

            if ($isRequired && $isGlobal) {
                $validator->errors()->add('is_global', 'Only one of is_required or is_global can be true.');
                $validator->errors()->add('is_required', 'Only one of is_required or is_global can be true.');
            }
        });
    }

    public function messages()
    {
        return [
            'category_id.required'  => 'The category is required.',
            'category_id.exists'    => 'The selected category does not exist.',

            'name.required'         => 'The name field is required.',
            'name.string'           => 'The name must be a string.',
            'name.max'              => 'The name may not be greater than 255 characters.',

            'type.required'         => 'The type field is required.',
            'type.in'               => 'The selected type is invalid. Valid types are text, number, boolean, select, or image.',

            'options.array'         => 'The options must be an array.',
            'options.*.required_with' => 'Each option is required when options are provided.',
            'options.*.string'      => 'Each option must be a string.',

            'is_required.boolean'   => 'The is_required field must be true or false.',
            'is_required.required'   => 'The is_required field must be filled.',

            'is_global.required'   => 'The is_global field must be filled.',
            'is_global.boolean'   => 'The is_global field must be true or false.',
        ];
    }

}
