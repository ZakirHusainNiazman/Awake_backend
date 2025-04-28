<?php

namespace App\Http\Requests\Fullfillment;

use Illuminate\Foundation\Http\FormRequest;

class CityRequest extends FormRequest
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
            "city_name"=>['required',"min:2","max:40"],
            'fullfillment_state_id' => ["required","exists:fullfillment_states,id"]
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'city_name.required' => 'The city name is required.',
            'city_name.min' => 'The city name must be at least 2 characters.',
            'city_name.max' => 'The city name may not be greater than 40 characters.',
            'fullfillment_state_id.required' => 'Please Select a State.',
            'fullfillment_state_id.exist' => 'The selected State does not exist.',
        ];
    }
}
