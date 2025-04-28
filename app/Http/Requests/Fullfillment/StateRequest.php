<?php

namespace App\Http\Requests\Fullfillment;

use Illuminate\Foundation\Http\FormRequest;

class StateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'state_name'             => ['required', 'min:2', 'max:40'],
            'fullfillment_country_id' => ['required', 'exists:fullfillment_countries,id,deleted_at,NULL'],
        ];
    }

    public function messages(): array
    {
        return [
            'state_name.required'             => 'The city name is required.',
            'state_name.min'                  => 'The city name must be at least 2 characters.',
            'state_name.max'                  => 'The city name may not be greater than 40 characters.',
            'fullfillment_country_id.required' => 'Please select a state.',
            'fullfillment_country_id.exists'   => 'The selected state does not exist.',
        ];
    }
}
