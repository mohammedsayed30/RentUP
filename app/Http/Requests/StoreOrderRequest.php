<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
   
    public function authorize(): bool
    {
        // Authorization logic done by middleware before reaching here
        return true; 
    }

    public function rules(): array
    {
        return [
            'amount_decimal' => [
                'required',
                'numeric',
                'min:0.01',
            ],
        ];
    }
    
    /**
     * Custom error messages for better user feedback.
     */
    public function messages(): array
    {
        return [
            'amount_decimal.required' => 'The order amount is required to place an order.',
            'amount_decimal.numeric' => 'The amount must be a valid number.',
            'amount_decimal.min' => 'The order amount must be at least 0.01.',
        ];
    }
    
}