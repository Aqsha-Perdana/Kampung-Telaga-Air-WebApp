<?php

namespace App\Services\AI;

use App\Models\AnalyticsDailyFinanceSnapshot;
use App\Services\FinancialReport\FinancialStatementService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinanceInsightService
{
    public function __construct(private readonly FinancialStatementService $financialStatementService)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function overview(?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ?: Carbon::now()->subDays(29)->toDateString();
        $endDate = $endDate ?: Carbon::now()->toDateString();

        return $this->snapshotOverview($startDate, $endDate) ?? $this->liveOverview($startDate, $endDate);
    }

    /**
     * @return array<string, mixed>
     */
    public function liveOverview(string $startDate, string $endDate): array
    {
        $profitLoss = $this->financialStatementService->getProfitLoss($startDate, $endDate);
        $cashFlow = $this->financialStatementService->getCashFlow($startDate, $endDate);

        $expenseBreakdown = DB::table('beban_operasionals')
            ->select('kategori', DB::raw('SUM(jumlah) as total'))
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->get()
            ->pluck('total', 'kategori')
            ->map(fn ($amount) => (float) $amount)
            ->toArray();

        arsort($expenseBreakdown);
        $topExpense = empty($expenseBreakdown)
            ? null
            : [
                'name' => (string) array_key_first($expenseBreakdown),
                'amount' => (float) current($expenseBreakdown),
            ];

        return [
            'period_start' => $startDate,
            'period_end' => $endDate,
            'revenue' => (float) ($profitLoss['revenue']['total_revenue'] ?? 0),
            'cost_of_sales' => (float) ($profitLoss['cost_of_sales']['total_cost_of_sales'] ?? 0),
            'gross_profit' => (float) ($profitLoss['gross_profit']['amount'] ?? 0),
            'operating_expenses' => (float) ($profitLoss['operating_expenses']['total_operating_expenses'] ?? 0),
            'net_profit' => (float) ($profitLoss['profit_for_period']['amount'] ?? 0),
            'gross_margin_percent' => round((float) ($profitLoss['gross_profit']['margin_percentage'] ?? 0), 1),
            'net_margin_percent' => round((float) ($profitLoss['profit_for_period']['margin_percentage'] ?? 0), 1),
            'refund_fee_income' => (float) ($profitLoss['other_items']['refund_fee_income'] ?? 0),
            'net_cash_movement' => (float) ($cashFlow['cash_summary']['net_increase_in_cash'] ?? 0),
            'top_expense_category' => $topExpense,
            'expense_breakdown' => $expenseBreakdown,
            'source' => 'live_query',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function snapshotOverview(string $startDate, string $endDate): ?array
    {
        if (!Schema::hasTable('analytics_daily_finance_snapshots')) {
            return null;
        }

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();
        $expectedDays = $start->diffInDays($end) + 1;

        $rows = AnalyticsDailyFinanceSnapshot::query()
            ->whereBetween('snapshot_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('snapshot_date')
            ->get();

        if ($rows->count() !== $expectedDays) {
            return null;
        }

        $expenseBreakdown = [];
        foreach ($rows as $row) {
            foreach ((array) ($row->expense_breakdown_json ?? []) as $category => $amount) {
                $expenseBreakdown[$category] = ($expenseBreakdown[$category] ?? 0) + (float) $amount;
            }
        }

        arsort($expenseBreakdown);
        $topExpense = empty($expenseBreakdown)
            ? null
            : [
                'name' => (string) array_key_first($expenseBreakdown),
                'amount' => (float) current($expenseBreakdown),
            ];

        $revenue = (float) $rows->sum('revenue');
        $grossProfit = (float) $rows->sum('gross_profit');
        $netProfit = (float) $rows->sum('net_profit');

        return [
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'revenue' => $revenue,
            'cost_of_sales' => (float) $rows->sum('cost_of_sales'),
            'gross_profit' => $grossProfit,
            'operating_expenses' => (float) $rows->sum('operating_expenses'),
            'net_profit' => $netProfit,
            'gross_margin_percent' => $revenue > 0 ? round(($grossProfit / $revenue) * 100, 1) : 0.0,
            'net_margin_percent' => $revenue > 0 ? round(($netProfit / $revenue) * 100, 1) : 0.0,
            'refund_fee_income' => (float) $rows->sum('refund_fee_income'),
            'net_cash_movement' => (float) $rows->sum('net_cash_movement'),
            'top_expense_category' => $topExpense,
            'expense_breakdown' => $expenseBreakdown,
            'source' => 'daily_snapshots',
        ];
    }
}
