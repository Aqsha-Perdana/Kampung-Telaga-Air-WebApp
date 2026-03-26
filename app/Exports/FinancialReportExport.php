<?php

namespace App\Exports;

use App\Services\FinancialReport\FinancialStatementService;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinancialReportExport implements WithMultipleSheets
{
    private Carbon $startDate;
    private Carbon $endDate;
    private array $profitLoss;
    private array $cashFlow;

    public function __construct($startDate, $endDate, ?FinancialStatementService $financialStatementService = null)
    {
        $this->startDate = Carbon::parse($startDate)->startOfDay();
        $this->endDate = Carbon::parse($endDate)->endOfDay();

        $service = $financialStatementService ?? app(FinancialStatementService::class);
        $this->profitLoss = $service->getProfitLoss($this->startDate, $this->endDate);
        $this->cashFlow = $service->getCashFlow($this->startDate, $this->endDate);
    }

    public function sheets(): array
    {
        return [
            new ProfitLossSheet($this->startDate, $this->endDate, $this->profitLoss),
            new CashFlowSheet($this->startDate, $this->endDate, $this->cashFlow),
            new RevenueBreakdownSheet($this->startDate, $this->endDate, $this->profitLoss['revenue_breakdown'] ?? []),
        ];
    }
}

class ProfitLossSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
{
    public function __construct(
        private readonly Carbon $startDate,
        private readonly Carbon $endDate,
        private readonly array $profitLoss
    ) {
    }

    public function title(): string
    {
        return 'Profit & Loss Statement';
    }

    public function headings(): array
    {
        return [
            ['KAMPUNG TELAGA AIR'],
            ['STATEMENT OF PROFIT OR LOSS AND OTHER COMPREHENSIVE INCOME'],
            ['For the period from ' . $this->startDate->format('d F Y') . ' to ' . $this->endDate->format('d F Y')],
            ['All amounts in Malaysian Ringgit (MYR)'],
            [],
            ['Account', 'Amount (MYR)'],
        ];
    }

    public function collection()
    {
        $revenue = (float) ($this->profitLoss['revenue']['total_revenue'] ?? 0);
        $grossProfit = (float) ($this->profitLoss['gross_profit']['amount'] ?? 0);
        $operatingProfit = (float) ($this->profitLoss['operating_profit']['amount'] ?? 0);
        $operatingExpenses = (float) ($this->profitLoss['operating_expenses']['total_operating_expenses'] ?? 0);
        $refundFeeIncome = (float) ($this->profitLoss['other_items']['refund_fee_income'] ?? 0);
        $otherIncome = (float) ($this->profitLoss['other_items']['other_income'] ?? 0);
        $otherExpenses = (float) ($this->profitLoss['other_items']['other_expenses'] ?? 0);
        $profitBeforeTax = (float) ($this->profitLoss['profit_before_tax']['amount'] ?? 0);
        $taxExpense = (float) ($this->profitLoss['tax_expense']['total_tax'] ?? 0);
        $netProfit = (float) ($this->profitLoss['profit_for_period']['amount'] ?? 0);

        return collect([
            ['REVENUE', ''],
            ['  Tour Package Sales', number_format((float) ($this->profitLoss['revenue']['tour_package_sales'] ?? 0), 2)],
            ['  Other Revenue', number_format((float) ($this->profitLoss['revenue']['other_revenue'] ?? 0), 2)],
            ['Total Revenue', number_format($revenue, 2)],
            ['', ''],
            ['COST OF SALES', ''],
            ['  Boat Services', '(' . number_format((float) ($this->profitLoss['cost_of_sales']['boat_services'] ?? 0), 2) . ')'],
            ['  Homestay Services', '(' . number_format((float) ($this->profitLoss['cost_of_sales']['homestay_services'] ?? 0), 2) . ')'],
            ['  Culinary Services', '(' . number_format((float) ($this->profitLoss['cost_of_sales']['culinary_services'] ?? 0), 2) . ')'],
            ['  Kiosk Services', '(' . number_format((float) ($this->profitLoss['cost_of_sales']['kiosk_services'] ?? 0), 2) . ')'],
            ['Total Cost of Sales', '(' . number_format((float) ($this->profitLoss['cost_of_sales']['total_cost_of_sales'] ?? 0), 2) . ')'],
            ['', ''],
            ['GROSS PROFIT', number_format($grossProfit, 2)],
            ['Gross Profit Margin %', number_format((float) ($this->profitLoss['gross_profit']['margin_percentage'] ?? 0), 2) . '%'],
            ['', ''],
            ['OPERATING EXPENSES', ''],
            ['  Total Operating Expenses', '(' . number_format($operatingExpenses, 2) . ')'],
            ['', ''],
            ['OPERATING PROFIT (EBIT)', number_format($operatingProfit, 2)],
            ['Operating Profit Margin %', number_format((float) ($this->profitLoss['operating_profit']['margin_percentage'] ?? 0), 2) . '%'],
            ['', ''],
            ['OTHER INCOME/(EXPENSES)', ''],
            ['  Refund Fee Income', number_format($refundFeeIncome, 2)],
            ['  Total Other Income', number_format($otherIncome, 2)],
            ['  Other Expenses', '(' . number_format($otherExpenses, 2) . ')'],
            ['Net Other Income/(Expenses)', number_format($otherIncome - $otherExpenses, 2)],
            ['', ''],
            ['PROFIT BEFORE TAX', number_format($profitBeforeTax, 2)],
            ['', ''],
            ['TAX EXPENSE', '(' . number_format($taxExpense, 2) . ')'],
            ['', ''],
            ['NET PROFIT FOR THE PERIOD', number_format($netProfit, 2)],
            ['Net Profit Margin %', number_format((float) ($this->profitLoss['profit_for_period']['margin_percentage'] ?? 0), 2) . '%'],
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            3 => ['font' => ['italic' => true]],
            6 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E7E6E6']]],
            7 => ['font' => ['bold' => true]],
            12 => ['font' => ['bold' => true]],
            19 => ['font' => ['bold' => true, 'size' => 11], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D4EDDA']]],
            22 => ['font' => ['bold' => true]],
            25 => ['font' => ['bold' => true, 'size' => 11], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'CCE5FF']]],
            28 => ['font' => ['bold' => true]],
            34 => ['font' => ['bold' => true]],
            36 => ['font' => ['bold' => true]],
            38 => ['font' => ['bold' => true, 'size' => 12], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'C3E6CB']]],
        ];
    }
}

class CashFlowSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
{
    public function __construct(
        private readonly Carbon $startDate,
        private readonly Carbon $endDate,
        private readonly array $cashFlow
    ) {
    }

    public function title(): string
    {
        return 'Cash Flow Statement';
    }

    public function headings(): array
    {
        return [
            ['KAMPUNG TELAGA AIR'],
            ['STATEMENT OF CASH FLOWS'],
            ['For the period from ' . $this->startDate->format('d F Y') . ' to ' . $this->endDate->format('d F Y')],
            ['All amounts in Malaysian Ringgit (MYR)'],
            [],
            ['Account', 'Amount (MYR)'],
        ];
    }

    public function collection()
    {
        $receipts = (float) ($this->cashFlow['operating_activities']['cash_receipts']['from_customers'] ?? 0);
        $paymentsSuppliers = (float) ($this->cashFlow['operating_activities']['cash_payments']['to_suppliers'] ?? 0);
        $paymentsRefund = (float) ($this->cashFlow['operating_activities']['cash_payments']['refunds_to_customers'] ?? 0);
        $paymentsExpenses = (float) ($this->cashFlow['operating_activities']['cash_payments']['operating_expenses'] ?? 0);

        return collect([
            ['CASH FLOWS FROM OPERATING ACTIVITIES', ''],
            ['  Cash Receipts from Customers', number_format($receipts, 2)],
            ['  Cash Payments to Suppliers', '(' . number_format($paymentsSuppliers, 2) . ')'],
            ['  Refunds Paid to Customers', '(' . number_format($paymentsRefund, 2) . ')'],
            ['  Cash Payments for Operating Expenses', '(' . number_format($paymentsExpenses, 2) . ')'],
            ['Net Cash from Operating Activities', number_format((float) ($this->cashFlow['operating_activities']['net_cash_from_operating'] ?? 0), 2)],
            ['', ''],
            ['CASH FLOWS FROM INVESTING ACTIVITIES', ''],
            ['  Purchase of Assets', number_format((float) ($this->cashFlow['investing_activities']['cash_outflows']['purchase_of_assets'] ?? 0), 2)],
            ['  Sale of Assets', number_format((float) ($this->cashFlow['investing_activities']['cash_inflows']['sale_of_assets'] ?? 0), 2)],
            ['Net Cash from Investing Activities', number_format((float) ($this->cashFlow['investing_activities']['net_cash_from_investing'] ?? 0), 2)],
            ['', ''],
            ['CASH FLOWS FROM FINANCING ACTIVITIES', ''],
            ['  Proceeds from Borrowings', number_format((float) ($this->cashFlow['financing_activities']['cash_inflows']['loans_received'] ?? 0), 2)],
            ['  Repayment of Borrowings', number_format((float) ($this->cashFlow['financing_activities']['cash_outflows']['loan_repayments'] ?? 0), 2)],
            ['Net Cash from Financing Activities', number_format((float) ($this->cashFlow['financing_activities']['net_cash_from_financing'] ?? 0), 2)],
            ['', ''],
            ['NET INCREASE/(DECREASE) IN CASH', number_format((float) ($this->cashFlow['cash_summary']['net_increase_in_cash'] ?? 0), 2)],
            ['Cash at Beginning of Period', number_format((float) ($this->cashFlow['cash_reconciliation']['opening_balance'] ?? 0), 2)],
            ['CASH AT END OF PERIOD', number_format((float) ($this->cashFlow['cash_reconciliation']['closing_balance'] ?? 0), 2)],
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            3 => ['font' => ['italic' => true]],
            6 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E7E6E6']]],
            7 => ['font' => ['bold' => true]],
            12 => ['font' => ['bold' => true, 'size' => 11], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D4EDDA']]],
            14 => ['font' => ['bold' => true]],
            17 => ['font' => ['bold' => true]],
            19 => ['font' => ['bold' => true]],
            22 => ['font' => ['bold' => true]],
            24 => ['font' => ['bold' => true, 'size' => 11]],
            26 => ['font' => ['bold' => true, 'size' => 12], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'C3E6CB']]],
        ];
    }
}

class RevenueBreakdownSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
{
    /**
     * @param array<int, array<string, mixed>> $revenueBreakdown
     */
    public function __construct(
        private readonly Carbon $startDate,
        private readonly Carbon $endDate,
        private readonly array $revenueBreakdown
    ) {
    }

    public function title(): string
    {
        return 'Revenue Breakdown';
    }

    public function headings(): array
    {
        return [
            ['KAMPUNG TELAGA AIR'],
            ['DETAILED REVENUE BREAKDOWN'],
            ['For the period from ' . $this->startDate->format('d F Y') . ' to ' . $this->endDate->format('d F Y')],
            [],
            ['Order ID', 'Customer', 'Date', 'Revenue (MYR)', 'Cost of Sales (MYR)', 'Gross Profit (MYR)', 'Display Currency', 'Display Amount'],
        ];
    }

    public function collection()
    {
        return collect($this->revenueBreakdown)
            ->filter(fn ($order) => in_array(($order['status'] ?? null), ['paid', 'confirmed', 'completed'], true))
            ->map(function ($order) {
                $date = isset($order['date']) ? Carbon::parse($order['date'])->format('d M Y') : null;
                $displayCurrency = $order['currency_info']['display_currency'] ?? 'MYR';
                $displayAmount = $order['currency_info']['display_amount'] ?? $order['revenue'] ?? 0;

                return [
                    $order['order_id'] ?? '-',
                    $order['customer'] ?? '-',
                    $date,
                    number_format((float) ($order['revenue'] ?? 0), 2),
                    number_format((float) ($order['cost_of_sales'] ?? 0), 2),
                    number_format((float) ($order['gross_profit'] ?? 0), 2),
                    $displayCurrency ?: 'MYR',
                    number_format((float) $displayAmount, 2),
                ];
            })
            ->values();
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            5 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E7E6E6']]],
        ];
    }
}
