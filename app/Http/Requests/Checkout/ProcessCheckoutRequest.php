<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class ProcessCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'nullable|string',
            'display_currency' => 'nullable|in:MYR,USD,IDR,SGD,EUR,GBP,AUD,JPY,CNY',
            'payment_method' => 'required|in:stripe',
        ];
    }
}
