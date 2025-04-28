<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'name'=> ['required','string','max:30'],
            'email'=> ['required','email','unique:users'],
            'password' => [
            'required',
            'min:8', // Minimum 8 characters required
            // Must contain at least one letter, one number, one special character,
            // and no character can be repeated more than twice consecutively.
            'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\'":\\|,.<>\/?])(?!.*(.)\1\1).+$/'
        ],
            'role_id'=> ['required','exists:roles,id'],
            'image'=>['nullable','image','mimes:jpeg,png,jpg,gif','max:2048'],
        ];
    }
    /*
    * Explanation:
    * - 'required' ensures that the password field is present.
    * - 'string' validates that the input is a valid string.
    * - 'min:8' enforces a minimum length of 8 characters.
    * - 'regex:...' ensures the password includes:
    *      • at least one alphabet character,
    *      • at least one numeric digit,
    *      • at least one special character,
    *      • and disallows any character from appearing three times consecutively.
    */

    public function messages(): array
    {
        return [
            'name.required' => 'User Name is required.',
            'name.string' => 'User Name should be a string.',
            'name.max' => 'User Name should not exceed 30 characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email should be a valid email address.',
            'email.unique' => 'Email already exists.',
            'password.required' => 'The password is required.',
            'password.min'      => 'The password must be at least 8 characters long.',
            'password.regex'    => 'The password must contain at least one letter, one number, one special character, and no character should repeat more than twice consecutively.',
            'password.confirmed' => 'Password confirmation does not match.',
            'role_id.required' => 'User Role is required.',
            'role_id.exists' => 'The role does not exist.',
            'image.image'=>'image must be an image.',
            'image.mimes'=>'Only jpeg, png, jpg, gif images are allowed.',
            'image.max'=>'Image size exceeds the limit (2MB).'
        ];
    }

}
