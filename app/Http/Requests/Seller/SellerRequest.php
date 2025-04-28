<?php

namespace App\Http\Requests\Seller;

use App\Models\User\User;
use App\Models\Seller\Seller;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SellerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
public function rules(): array
    {
        $userId = $this->user()->id;

        return [

            //personal info
             'first_name'=>['required','min:2','max:12'],
            'last_name'=>['required','min:2','max:12'],
            'email'=>['required','email',Rule::unique('users', 'email')->ignore($userId),"regex:/^[a-zA-Z0-9.!#$%&*+'\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/"],

            ///bussines info
            'account_status' => 'in:pending,approved,block',
            'dob' => 'required|date',
            'whatsapp_no' => 'required|string|max:255',
            'business_description' => 'nullable|string',
            'identity_type' => 'required|in:passport,driving_license,national_id_card',
            'proof_of_identity' =>[
                'required',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:2048', // max file size in kilobytes (2 MB),
            ],
            'brand_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sellers', 'brand_name'),
            ],
            'brand_logo' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:2048',
            ],

            // Address fields
            'fullfillment_country_id' => 'required|exists:fullfillment_countries,id',
            'fullfillment_state_id' => 'required|exists:fullfillment_states,id',
            'fullfillment_city_id' => 'required|exists:fullfillment_cities,id',
            'address_line1' => 'required|string',
            'address_line2' => 'nullable|string',
            'postal_code' => 'required|string',
            'phone' => 'required|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'first_name.required'=>"The first name field is required",
            'first_name.min'=>"The First Name field should be at least 2 character long",
            'first_name.max'=>"The First Name field should be at most 12 character long",
            'last_name.required'=>"The Last Name field is required",
            'last_name.min'=>"The Last Name field should be at least 2 character long",
            'last_name.max'=>"The Last Name field should be at most 12 character long",
            'email.required'=>"The Email field is required",
            'email.email'=>"Please enter an email address",
            'email.regex'=>"Please enter a vaild email address",


            // Brand Name
            'brand_name.required' => 'The brand name is required when the account type is business.',
            'brand_name.max' => 'The brand name may not be greater than 255 characters.',
            'brand_name.unique' => 'This brand name is already taken.',

            // Brand Logo
            'brand_logo.required' => 'The brand logo is required when the account type is business.',
            'brand_logo.image' => 'The brand logo must be a valid image file.',
            'brand_logo.mimes' => 'The brand logo must be a file of type: jpeg, png, jpg, gif, svg.',
            'brand_logo.max' => 'The brand logo may not be larger than 2MB.',


            'dob.required' => 'Date of birth is required.',
            'dob.date' => 'Date of birth must be a valid date.',
            'whatsapp_no.required' => 'WhatsApp number is required.',
            'whatsapp_no.max' => 'WhatsApp number may not be greater than 255 characters.',
            'identity_type.required' => 'Identity type is required.',
            'identity_type.in' => 'Identity type must be passport, driving license, or national ID card.',
            'proof_of_identity.required' => 'Proof of identity is required.',
            'proof_of_identity.mimes' => 'Proof of identity must be an image of jpeg,png,jpg,gif or svg type .',
            'proof_of_identity.max' => 'Proof of identity image must be at most 2MB .',


            // country
            'fullfillment_country_id.required' => 'Please select a country.',
            'fullfillment_country_id.exists'   => 'The selected country is invalid.',

            // state
            'fullfillment_state_id.required' => 'Please select a state/province.',
            'fullfillment_state_id.exists'   => 'The selected state/province is invalid.',

            // city
            'fullfillment_city_id.required' => 'Please select a city.',
            'fullfillment_city_id.exists'   => 'The selected city is invalid.',

            // address lines
            'address_line1.required' => 'Address line 1 is required.',
            'address_line1.string'   => 'Address line 1 must be text.',
            'address_line2.string'   => 'Address line 2 must be text.',

            // postal code
            'postal_code.required' => 'Postal code is required.',
            'postal_code.string'   => 'Postal code must be text.',

            // phone
            'phone.required' => 'Phone number is required.',
            'phone.string'   => 'Phone number must be text.',
        ];
    }


    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = $this->user();

        // Prevent duplicate seller account
            if ($user->user_type === 'seller') {
                // You can also specify a custom message and status code
                abort(400, 'There is already a seller acount register with email.'); // 400 Conflict
            }
        });
    }
}
