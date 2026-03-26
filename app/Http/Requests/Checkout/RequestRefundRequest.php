<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class RequestRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string|max:1000',
            'confirm_refund_fee' => 'accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'confirm_refund_fee.accepted' => 'You must accept the 10% refund fee before submitting this request.',
        ];
    }
}
