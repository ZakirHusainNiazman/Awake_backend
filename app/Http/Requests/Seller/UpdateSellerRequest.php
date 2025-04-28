<?php

namespace App\Http\Requests\Seller;

use Log;
use App\Models\Seller\Seller;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSellerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Set this to true to allow the request
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $sellerId = $this->route('id');

        $seller = Seller::findOrFail($sellerId);

        return [

            //personal info
             'first_name'=>['required','min:2','max:12'],
            'last_name'=>['required','min:2','max:12'],
            'email'=>['required','email',Rule::unique('users', 'email')->ignore($seller->user->id),"regex:/^[a-zA-Z0-9.!#$%&*+'\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/"],

            ///bussines info
            'account_status' => 'required,in:pending,approved,block,rejected',
            'dob' => 'required|date',
            'whatsapp_no' => 'required|string|max:255',
            'business_description' => 'nullable|string',
            'identity_type' => 'required|in:passport,driving_license,national_id_card',
            'proof_of_identity' =>[
                'nullable',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:2048', // max file size in kilobytes (2 MB),
            ],
            'brand_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sellers', 'brand_name')->ignore($seller->brand_name)
            ],
            'brand_logo' => [
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
            'is_default' => 'required|boolean',
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
            'brand_name.required' => 'The brand name is required.',
            'brand_name.max' => 'The brand name may not be greater than 255 characters.',
            'brand_name.unique' => 'This brand name is already taken.',

            // Brand Logo
            'brand_logo.required' => 'The brand logo is required.',
            'brand_logo.image' => 'The brand logo must be a valid image file.',
            'brand_logo.mimes' => 'The brand logo must be a file of type: jpeg, png, jpg, gif, svg.',
            'brand_logo.max' => 'The brand logo may not be larger than 2MB.',



            'account_status.required' => 'Account status is required.',
            'account_status.in' => 'Account status must be pending, approved, or block.',
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

            // default flag
            'is_default.required' => 'Please specify whether this is the default address.',
            'is_default.boolean'  => 'The default-address flag must be true or false.',
        ];
    }
}
