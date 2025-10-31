<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
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
            
            'name' => ['required', 'string', 'max:255'],
            
            
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            
            
            'password' => ['required', 'string', 'min:6'],
        ];
    }

    public function messages(): array
    {
        return [
            // Custom messages for the 'name' field
            'name.required' => 'We need your name to set up your account.',
            'name.max' => 'Your name cannot be longer than 255 characters.',

            // Custom messages for the 'email' field
            'email.required' => 'The email address is required for registration.',
            'email.email' => 'Please provide a valid email format (e.g., user@example.com).',
            'email.unique' => 'This email address is already registered. Try logging in!',
            
            // Custom messages for the 'password' field
            'password.required' => 'A password is required to secure your account.',
            'password.min' => 'The password must be at least 6 characters long.',
        ];
    }
}
