<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginUserRequest extends FormRequest
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
            
            'email' => ['required', 'string', 'email', 'max:255'],
            
            'password' => ['required', 'string', 'min:6'],
        ];
    }
    
    public function messages(): array
    {
        return [
            // Custom messages for the 'email' field
            'email.required' => 'The email address is required for login.',
            'email.email' => 'Please provide a valid email format ',
           //password field
            'password.required' => 'A password is required to access your account.',
            'password.min' => 'The password must be at least 6 characters long.',
        
        ];
    }

    

}
