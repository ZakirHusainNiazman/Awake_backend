<?php

namespace App\Http\Requests\Fullfillment;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $stateId = $this->route('id') ?? $this->input('id'); // flexible for both route and input

        return [
            'state_name' => [
                'required',
                'min:2',
                'max:40',
                Rule::unique('fullfillment_states', 'state_name')
                    ->ignore($stateId)
                    ->whereNull('deleted_at'),
            ],
            'fullfillment_country_id' => [
                'required',
                'exists:fullfillment_countries,id,deleted_at,NULL',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'state_name.required' => 'The state name is required.',
            'state_name.min' => 'The state name must be at least 2 characters.',
            'state_name.max' => 'The state name may not be greater than 40 characters.',
            'state_name.unique' => 'This state name is already in use.',

            'fullfillment_country_id.required' => 'Please select a country.',
            'fullfillment_country_id.exists' => 'The selected country does not exist.',
        ];
    }
}
