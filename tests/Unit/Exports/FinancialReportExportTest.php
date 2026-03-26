<?php

namespace Tests\Unit\Exports;

use App\Exports\FinancialReportExport;
use App\Services\FinancialReport\FinancialStatementService;
use Mockery;
use Tests\TestCase;

class FinancialReportExportTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_export_uses_financial_statement_service_payload_for_all_sheets(): void
    {
        $profitLoss = [
            'revenue' => [
                'tour_package_sales' => 100,
                'other_revenue' => 0,
                'total_revenue' => 100,
            ],
            'cost_of_sales' => [
                'boat_services' => 10,
                'homestay_services' => 20,
                'culinary_services' => 5,
                'kiosk_services' => 5,
                'total_cost_of_sales' => 40,
            ],
            'gross_profit' => ['amount' => 60, 'margin_percentage' => 60],
            'operating_expenses' => ['by_nature' => [], 'total_operating_expenses' => 10],
            'operating_profit' => ['amount' => 50, 'margin_percentage' => 50],
            'other_items' => [
                'refund_fee_income' => 2,
                'other_income' => 2,
                'other_expenses' => 0,
                'net_other_items' => 2,
            ],
            'profit_before_tax' => ['amount' => 52],
            'tax_expense' => ['total_tax' => 0],
            'profit_for_period' => ['amount' => 52, 'margin_percentage' => 52],
            'revenue_breakdown' => [
                [
                    'order_id' => 'ORD-001',
                    'customer' => 'Test Customer',
                    'date' => '2026-01-02 10:00:00',
                    'status' => 'paid',
                    'revenue' => 100,
                    'cost_of_sales' => 40,
                    'gross_profit' => 60,
                    'currency_info' => [
                        'display_currency' => 'MYR',
                        'display_amount' => 100,
                    ],
                ],
            ],
        ];

        $cashFlow = [
            'operating_activities' => [
                'cash_receipts' => ['from_customers' => 100, 'by_payment_method' => []],
                'cash_payments' => [
                    'to_suppliers' => 40,
                    'refunds_to_customers' => 0,
                    'operating_expenses' => 10,
                ],
                'net_cash_from_operating' => 50,
            ],
            'investing_activities' => [
                'cash_outflows' => ['purchase_of_assets' => 0],
                'cash_inflows' => ['sale_of_assets' => 0],
                'net_cash_from_investing' => 0,
            ],
            'financing_activities' => [
                'cash_inflows' => ['loans_received' => 0],
                'cash_outflows' => ['loan_repayments' => 0],
                'net_cash_from_financing' => 0,
            ],
            'cash_summary' => ['net_increase_in_cash' => 50],
            'cash_reconciliation' => ['opening_balance' => 0, 'closing_balance' => 50],
        ];

        $service = Mockery::mock(FinancialStatementService::class);
        $service->shouldReceive('getProfitLoss')->once()->andReturn($profitLoss);
        $service->shouldReceive('getCashFlow')->once()->andReturn($cashFlow);

        $export = new FinancialReportExport('2026-01-01', '2026-01-31', $service);
        $sheets = $export->sheets();

        $this->assertCount(3, $sheets);
        $this->assertSame('Profit & Loss Statement', $sheets[0]->title());
        $this->assertSame('Cash Flow Statement', $sheets[1]->title());
        $this->assertSame('Revenue Breakdown', $sheets[2]->title());
        $this->assertGreaterThan(0, $sheets[0]->collection()->count());
    }
}
