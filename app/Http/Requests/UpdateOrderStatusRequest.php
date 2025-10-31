<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
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
            // Status must be one of the predefined values
            'status' => [
                'required',
                'string',
                'in:placed,processing,shipped,delivered,cancelled'
            ],
        ];
    }
    // Custom error messages for better user feedback.
    public function messages(): array
    {
        return [
            'status.required' => 'The order status is required to update the order.',
            'status.string' => 'The status must be a valid string.',
            'status.in' => 'The status must be one of the following: placed,processing,shipped,delivered,cancelled',
        ];
    }
}
