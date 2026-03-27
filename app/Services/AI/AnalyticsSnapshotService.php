<?php

namespace App\Services\AI;

use App\Models\AnalyticsDailyFinanceSnapshot;
use App\Models\AnalyticsDailyPackageSnapshot;
use App\Models\AnalyticsDailyResourceSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnalyticsSnapshotService
{
    public function __construct(
        private readonly FinanceInsightService $financeInsightService,
        private readonly ResourceInsightService $resourceInsightService
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function refreshForDate(string $date): array
    {
        $date = Carbon::parse($date)->toDateString();

        return [
            'finance' => $this->refreshFinanceSnapshot($date),
            'resources' => $this->refreshResourceSnapshots($date),
            'packages' => $this->refreshPackageSnapshots($date),
        ];
    }

    public function hasSnapshotTables(): bool
    {
        return Schema::hasTable('analytics_daily_finance_snapshots')
            && Schema::hasTable('analytics_daily_resource_snapshots')
            && Schema::hasTable('analytics_daily_package_snapshots');
    }

    /**
     * @return array<string, mixed>|null
     */
    public function aggregateFinanceRange(string $startDate, string $endDate): ?array
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
        $topExpenseCategory = null;
        if (!empty($expenseBreakdown)) {
            $topExpenseCategory = [
                'name' => (string) array_key_first($expenseBreakdown),
                'amount' => (float) current($expenseBreakdown),
            ];
        }

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
            'top_expense_category' => $topExpenseCategory,
            'source' => 'daily_snapshots',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function resourceOverviewForDate(string $date): ?array
    {
        if (!Schema::hasTable('analytics_daily_resource_snapshots')) {
            return null;
        }

        $rows = AnalyticsDailyResourceSnapshot::query()
            ->where('snapshot_date', Carbon::parse($date)->toDateString())
            ->get()
            ->keyBy('resource_type');

        if ($rows->count() < 4) {
            return null;
        }

        $build = function (string $type) use ($rows) {
            $row = $rows->get($type);

            return [
                'total' => (int) ($row->total_resources ?? 0),
                'booked' => (int) ($row->booked_resources ?? 0),
                'available' => max((int) ($row->total_resources ?? 0) - (int) ($row->booked_resources ?? 0), 0),
                'active_capacity' => (int) ($row->active_capacity ?? 0),
                'utilization_percent' => (float) ($row->utilization_percent ?? 0),
            ];
        };

        return [
            'date' => Carbon::parse($date)->toDateString(),
            'date_label' => Carbon::parse($date)->format('d M Y'),
            'boats' => $build('boat'),
            'homestays' => $build('homestay'),
            'culinaries' => $build('culinary'),
            'kiosks' => $build('kiosk'),
            'source' => 'daily_snapshots',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function packageOverviewForPeriod(int $days = 30): ?array
    {
        if (!Schema::hasTable('analytics_daily_package_snapshots')) {
            return null;
        }

        $startDate = Carbon::today()->subDays($days - 1)->toDateString();
        $rows = AnalyticsDailyPackageSnapshot::query()
            ->where('snapshot_date', '>=', $startDate)
            ->get();

        if ($rows->isEmpty()) {
            return null;
        }

        $grouped = $rows->groupBy('package_id')->map(function ($items) {
            return [
                'id' => (string) $items->first()->package_id,
                'name' => (string) $items->first()->package_name,
                'orders' => (int) $items->sum('total_orders'),
                'participants' => (int) $items->sum('total_participants'),
                'revenue' => (float) $items->sum('total_revenue'),
                'profit' => (float) $items->sum('total_profit'),
            ];
        })->sortByDesc('profit')->values();

        $top = $grouped->first();

        return [
            'period_start' => $startDate,
            'period_end' => Carbon::today()->toDateString(),
            'top_package' => $top,
            'total_packages' => $grouped->count(),
            'source' => 'daily_snapshots',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function refreshFinanceSnapshot(string $date): array
    {
        $overview = $this->financeInsightService->liveOverview($date, $date);

        AnalyticsDailyFinanceSnapshot::query()->updateOrCreate(
            ['snapshot_date' => $date],
            [
                'revenue' => (float) $overview['revenue'],
                'cost_of_sales' => (float) $overview['cost_of_sales'],
                'gross_profit' => (float) $overview['gross_profit'],
                'operating_expenses' => (float) $overview['operating_expenses'],
                'net_profit' => (float) $overview['net_profit'],
                'gross_margin_percent' => (float) $overview['gross_margin_percent'],
                'net_margin_percent' => (float) $overview['net_margin_percent'],
                'refund_fee_income' => (float) $overview['refund_fee_income'],
                'net_cash_movement' => (float) $overview['net_cash_movement'],
                'top_expense_category' => $overview['top_expense_category']['name'] ?? null,
                'expense_breakdown_json' => $overview['expense_breakdown'] ?? [],
                'snapshot_json' => $overview,
            ]
        );

        return $overview;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function refreshResourceSnapshots(string $date): array
    {
        $overview = $this->resourceInsightService->liveOverview($date);
        $rows = [];

        foreach ([
            'boat' => $overview['boats'] ?? [],
            'homestay' => $overview['homestays'] ?? [],
            'culinary' => $overview['culinaries'] ?? [],
            'kiosk' => $overview['kiosks'] ?? [],
        ] as $type => $payload) {
            AnalyticsDailyResourceSnapshot::query()->updateOrCreate(
                [
                    'snapshot_date' => $date,
                    'resource_type' => $type,
                ],
                [
                    'total_resources' => (int) ($payload['total'] ?? 0),
                    'booked_resources' => (int) ($payload['booked'] ?? 0),
                    'active_capacity' => (int) ($payload['active_capacity'] ?? 0),
                    'utilization_percent' => (float) ($payload['utilization_percent'] ?? 0),
                    'snapshot_json' => $payload,
                ]
            );

            $rows[] = array_merge(['resource_type' => $type], $payload);
        }

        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function refreshPackageSnapshots(string $date): array
    {
        $rows = DB::table('paket_wisatas')
            ->leftJoin('order_items', function ($join) use ($date) {
                $join->on('paket_wisatas.id_paket', '=', 'order_items.id_paket')
                    ->whereDate('order_items.created_at', '=', $date);
            })
            ->leftJoin('orders', function ($join) {
                $join->on('order_items.id_order', '=', 'orders.id_order')
                    ->whereIn('orders.status', ['paid', 'confirmed', 'completed']);
            })
            ->select(
                'paket_wisatas.id_paket',
                'paket_wisatas.nama_paket',
                'paket_wisatas.status',
                'paket_wisatas.harga_total',
                'paket_wisatas.harga_final',
                DB::raw('COUNT(DISTINCT orders.id_order) as total_orders'),
                DB::raw('COALESCE(SUM(CASE WHEN orders.id_order IS NOT NULL THEN order_items.jumlah_peserta ELSE 0 END), 0) as total_participants'),
                DB::raw('COALESCE(SUM(CASE WHEN orders.id_order IS NOT NULL THEN order_items.subtotal ELSE 0 END), 0) as total_revenue'),
                DB::raw('COALESCE(SUM(CASE WHEN orders.id_order IS NOT NULL THEN COALESCE(order_items.company_profit_total, order_items.subtotal - COALESCE(order_items.vendor_cost_total, 0)) ELSE 0 END), 0) as total_profit')
            )
            ->groupBy(
                'paket_wisatas.id_paket',
                'paket_wisatas.nama_paket',
                'paket_wisatas.status',
                'paket_wisatas.harga_total',
                'paket_wisatas.harga_final'
            )
            ->get();

        $snapshots = [];

        foreach ($rows as $row) {
            $marginPercent = (float) $row->harga_total > 0
                ? round((((float) $row->harga_final - (float) $row->harga_total) / (float) $row->harga_total) * 100, 1)
                : 0.0;

            $payload = [
                'snapshot_date' => $date,
                'package_id' => (string) $row->id_paket,
                'package_name' => (string) $row->nama_paket,
                'package_status' => (string) $row->status,
                'total_orders' => (int) $row->total_orders,
                'total_participants' => (int) $row->total_participants,
                'total_revenue' => (float) $row->total_revenue,
                'total_profit' => (float) $row->total_profit,
                'margin_percent' => $marginPercent,
            ];

            AnalyticsDailyPackageSnapshot::query()->updateOrCreate(
                [
                    'snapshot_date' => $date,
                    'package_id' => (string) $row->id_paket,
                ],
                array_merge($payload, [
                    'snapshot_json' => $payload,
                ])
            );

            $snapshots[] = $payload;
        }

        return $snapshots;
    }
}
