<?php

namespace App\Http\Requests\Admin\FinancialReport;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class ReportPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];
    }

    public function startDate(): Carbon
    {
        return Carbon::parse($this->input('start_date', Carbon::now()->startOfMonth()->toDateString()))->startOfDay();
    }

    public function endDate(): Carbon
    {
        return Carbon::parse($this->input('end_date', Carbon::now()->endOfMonth()->toDateString()))->endOfDay();
    }
}
