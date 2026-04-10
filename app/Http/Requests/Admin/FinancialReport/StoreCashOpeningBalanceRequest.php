<?php

namespace App\Http\Requests\Admin\FinancialReport;

use Illuminate\Foundation\Http\FormRequest;

class StoreCashOpeningBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'balance_date' => 'required|date',
            'amount' => 'required|numeric|min:0|max:999999999999.99',
            'notes' => 'nullable|string|max:1000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];
    }
}
