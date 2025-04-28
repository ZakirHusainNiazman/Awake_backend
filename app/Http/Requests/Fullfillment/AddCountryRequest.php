<?php

namespace App\Http\Requests\Fullfillment;

use Illuminate\Foundation\Http\FormRequest;

class AddCountryRequest extends FormRequest
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
            'country_name' => [
                'required',
                'string',
                'max:255',
                'unique:fullfillment_countries,country_name',
            ],
            'country_flag' => [
                'required',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:2048', // max file size in kilobytes (2 MB)
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

            'country_flag.required' => 'Please upload a country flag image.',
            'country_flag.mimes'    => 'Allowed flag formats: jpeg, png, jpg, gif, svg.',
            'country_flag.max'      => 'The flag may not be greater than 2 MB.',
        ];
    }

}
