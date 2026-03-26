<?php

namespace Tests\Unit\Http\Requests\Admin\FinancialReport;

use App\Http\Requests\Admin\FinancialReport\OwnerReportRequest;
use App\Http\Requests\Admin\FinancialReport\ReportPeriodRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class FinancialReportRequestTest extends TestCase
{
    public function test_report_period_request_rejects_end_date_before_start_date(): void
    {
        $request = new ReportPeriodRequest();

        $validator = Validator::make([
            'start_date' => '2026-01-10',
            'end_date' => '2026-01-01',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('end_date', $validator->errors()->toArray());
    }

    public function test_owner_report_request_requires_valid_owner_type(): void
    {
        $request = new OwnerReportRequest();

        $validator = Validator::make([
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'type' => 'invalid-type',
            'id' => 'ANY',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());
    }

    public function test_report_period_helpers_return_normalized_carbon_instances(): void
    {
        $request = ReportPeriodRequest::create('/', 'GET', [
            'start_date' => '2026-01-05',
            'end_date' => '2026-01-10',
        ]);

        $this->assertSame('2026-01-05 00:00:00', $request->startDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-01-10 23:59:59', $request->endDate()->format('Y-m-d H:i:s'));
    }
}
