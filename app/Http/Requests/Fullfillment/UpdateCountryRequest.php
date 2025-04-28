<?php

namespace App\Http\Requests\Fullfillment;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCountryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->input('id');
        return [
            'country_name' => [
                'required',
                'string',
                'max:255',
                /// unique on country_name, but ignore the row with this id
                Rule::unique('fullfillment_countries', 'country_name')->ignore($id),
            ],
            'country_flag' => [
                'nullable', // Make flag optional during updates
                'mimes:jpeg,png,jpg,gif,svg',
                'max:2048', // max file size in kilobytes (2MB)
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'country_name.required' => 'Please enter a country name.',
            'country_name.string'   => 'The country name must be a valid string.',
            'country_name.max'      => 'The country name must be at most 255 characters.',
            'country_name.unique'   => 'The selected country name already exists.',

            'country_flag.mimes'    => 'Allowed flag formats: jpeg, png, jpg, gif, svg.',
            'country_flag.max'      => 'The flag may not be greater than 2 MB.',
        ];
    }
}
