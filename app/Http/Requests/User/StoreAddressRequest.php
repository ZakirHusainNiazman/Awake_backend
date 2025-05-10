<?php

namespace App\Http\Requests\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
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
            'fullfillment_country_id' => 'required|exists:fullfillment_countries,id',
            'fullfillment_state_id'   => 'required|exists:fullfillment_states,id',
            'fullfillment_city_id'    => 'required|exists:fullfillment_cities,id',
            'address_line1'          => 'required|string|max:255',
            'address_line2'          => 'nullable|string|max:255',
            'postal_code'            => 'required|string|max:20',
            'phone'                  => 'required|string|max:20',
            'is_default'             => 'boolean',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $user = Auth::user();

            if ($user->addresses()->count() >= 3) {
                $validator->errors()->add('addresses', 'You can only have up to 3 addresses.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'fullfillment_country_id.required' => 'Please select a country.',
            'fullfillment_country_id.exists'   => 'The selected country is invalid.',
            'fullfillment_state_id.required'   => 'Please select a state.',
            'fullfillment_state_id.exists'     => 'The selected state is invalid.',
            'fullfillment_city_id.required'    => 'Please select a city.',
            'fullfillment_city_id.exists'      => 'The selected city is invalid.',
            'address_line1.required'          => 'Address Line 1 is required.',
            'address_line1.max'               => 'Address Line 1 may not be greater than 255 characters.',
            'address_line2.max'               => 'Address Line 2 may not be greater than 255 characters.',
            'postal_code.required'                 => 'Postal code is required.',
            'postal_code.max'                 => 'Postal code may not be greater than 20 characters.',
            'phone.required'                       => 'Phone number is required.',
            'phone.max'                       => 'Phone number may not be greater than 20 characters.',
            'is_default.boolean'              => 'The default address flag must be true or false.',
        ];
    }
}


