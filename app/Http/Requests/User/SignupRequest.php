<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class SignupRequest extends FormRequest
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
            'first_name'=>['required','min:2','max:12'],
            'last_name'=>['required','min:2','max:12'],
            'email'=>['required','email','unique:users',"regex:/^[a-zA-Z0-9.!#$%&*+'\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/"],
            'password'=>['required','min:8','max:12','confirmed'],
        ];
    }


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
            'password.required'=>"The Password field is required",
            'password.min'=>"The Password field should be at least 8 character long",
            'password.max'=>"The Password field should be at most 12 character long",
            'password.confirmed'=>"The Confirm Password field should match the password",
        ];
    }
}
