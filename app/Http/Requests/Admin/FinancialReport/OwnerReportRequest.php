<?php

namespace App\Http\Requests\Admin\FinancialReport;

class OwnerReportRequest extends ReportPeriodRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'type' => 'required|in:boat,homestay,culinary,kiosk',
            'id' => 'required',
        ]);
    }

    public function validationData(): array
    {
        return array_merge($this->all(), [
            'type' => $this->route('type'),
            'id' => $this->route('id'),
        ]);
    }
}
