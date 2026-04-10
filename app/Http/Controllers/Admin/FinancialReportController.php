<?php

namespace App\Http\Controllers\Admin;

use App\Exports\FinancialReportExport;
use App\Exports\OwnerReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FinancialReport\OwnerReportRequest;
use App\Http\Requests\Admin\FinancialReport\ReportPeriodRequest;
use App\Http\Requests\Admin\FinancialReport\StoreCashOpeningBalanceRequest;
use App\Models\CashOpeningBalance;
use App\Services\FinancialReport\FinancialStatementService;
use App\Services\FinancialReport\OwnerReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;

class FinancialReportController extends Controller
{
    public function __construct(
        private readonly FinancialStatementService $financialStatementService,
        private readonly OwnerReportService $ownerReportService
    ) {
    }

    /**
     * Main Financial Reports Dashboard
     */
    public function index(ReportPeriodRequest $request)
    {
        [$startDate, $endDate] = $this->resolvePeriod($request);
        $hasOpeningBalanceTable = Schema::hasTable('cash_opening_balances');
        $openingBalanceVersion = $hasOpeningBalanceTable
            ? (string) (DB::table('cash_opening_balances')->max('updated_at') ?? 'none')
            : 'table_missing';

        $cacheKey = 'admin.financial.index.v5.' . md5(json_encode([
            'start' => $startDate->toDateString(),
            'end' => $endDate->toDateString(),
            'opening_balance_version' => $openingBalanceVersion,
        ]));

        $payload = Cache::remember($cacheKey, now()->addSeconds(45), function () use ($startDate, $endDate) {
            return [
                'profitLoss' => $this->financialStatementService->getProfitLoss($startDate, $endDate),
                'cashFlow' => $this->financialStatementService->getCashFlow($startDate, $endDate),
                'ownerSummary' => $this->ownerReportService->getOwnerSummary($startDate, $endDate),
            ];
        });

        $profitLoss = $payload['profitLoss'];
        $cashFlow = $payload['cashFlow'];
        $ownerSummary = $payload['ownerSummary'];
        $currentOpeningBalanceRecord = $hasOpeningBalanceTable
            ? CashOpeningBalance::query()->whereDate('balance_date', $startDate->toDateString())->first()
            : null;
        $recentOpeningBalances = $hasOpeningBalanceTable
            ? CashOpeningBalance::query()->orderByDesc('balance_date')->limit(5)->get()
            : collect();
        $impactPaginator = $this->paginateArray(
            collect($profitLoss['revenue_breakdown'] ?? [])
                ->sortByDesc(function ($order) {
                    return data_get($order, 'date');
                })
                ->values()
                ->all(),
            10,
            'impact_page',
            'profit-loss'
        );
        $profitLoss['revenue_breakdown'] = $impactPaginator->items();

        return view('admin.financial-reports.index', compact(
            'profitLoss',
            'cashFlow',
            'ownerSummary',
            'currentOpeningBalanceRecord',
            'recentOpeningBalances',
            'startDate',
            'endDate',
            'impactPaginator'
        ));
    }

    public function storeOpeningBalance(StoreCashOpeningBalanceRequest $request)
    {
        $validated = $request->validated();

        if (!Schema::hasTable('cash_opening_balances')) {
            return redirect()
                ->route('financial-reports.index', array_filter([
                    'start_date' => $validated['start_date'] ?? null,
                    'end_date' => $validated['end_date'] ?? null,
                ]))
                ->with('error', 'Opening cash table is not ready yet. Please run the latest migration first.');
        }

        CashOpeningBalance::query()->updateOrCreate(
            ['balance_date' => $validated['balance_date']],
            [
                'amount' => $validated['amount'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth('admin')->id(),
            ]
        );

        return redirect()
            ->route('financial-reports.index', array_filter([
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
            ]))
            ->with('success', 'Opening cash balance saved successfully.');
    }

    public function exportProfitLossPdf(ReportPeriodRequest $request)
    {
        [$startDate, $endDate] = $this->resolvePeriod($request);

        $data = [
            'profitLoss' => $this->financialStatementService->getProfitLoss($startDate, $endDate),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => Carbon::now(),
        ];

        $pdf = Pdf::loadView('admin.financial-reports.pdf-profit-loss', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'Profit_Loss_Statement_' . $startDate->format('Ymd') . '_to_' . $endDate->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    public function exportCashFlowPdf(ReportPeriodRequest $request)
    {
        [$startDate, $endDate] = $this->resolvePeriod($request);

        $data = [
            'cashFlow' => $this->financialStatementService->getCashFlow($startDate, $endDate),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => Carbon::now(),
        ];

        $pdf = Pdf::loadView('admin.financial-reports.pdf-cash-flow', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'Cash_Flow_Statement_' . $startDate->format('Ymd') . '_to_' . $endDate->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    public function exportExcel(ReportPeriodRequest $request)
    {
        [$startDate, $endDate] = $this->resolvePeriod($request);

        $filename = 'Financial_Report_' . $startDate->format('Ymd') . '_to_' . $endDate->format('Ymd') . '.xlsx';

        return Excel::download(new FinancialReportExport($startDate, $endDate), $filename);
    }

    /**
     * Backward-compatible endpoint alias for owner detail route.
     */
    public function ownerReport(OwnerReportRequest $request, $type, $id)
    {
        return $this->ownerDetail($request, $type, $id);
    }

    public function ownerDetail(OwnerReportRequest $request, $type, $id)
    {
        [$startDate, $endDate] = $this->resolvePeriod($request);

        $ownerType = (string) $request->route('type');
        $ownerId = (string) $request->route('id');
        $cacheKey = 'admin.financial.owner-detail.v1.' . md5(json_encode([
            'type' => $ownerType,
            'id' => $ownerId,
            'start' => $startDate->toDateString(),
            'end' => $endDate->toDateString(),
        ]));

        $report = Cache::remember($cacheKey, now()->addSeconds(45), function () use ($ownerType, $ownerId, $startDate, $endDate) {
            return $this->ownerReportService->getOwnerDetail($ownerType, $ownerId, $startDate, $endDate);
        });

        if (!$report) {
            return redirect()->route('financial-reports.index')
                ->with('error', 'Owner not found');
        }

        $type = $ownerType;

        return view('admin.financial-reports.owner-detail', compact('report', 'type', 'startDate', 'endDate'));
    }

    public function exportOwnerPDF(OwnerReportRequest $request, $type, $id)
    {
        [$startDate, $endDate] = $this->resolvePeriod($request);

        $ownerType = (string) $request->route('type');
        $ownerId = (string) $request->route('id');
        $report = $this->ownerReportService->getOwnerDetail($ownerType, $ownerId, $startDate, $endDate);

        if (!$report) {
            return redirect()->back()->with('error', 'Owner not found');
        }

        $pdf = Pdf::loadView('admin.financial-reports.pdf.owner-report', [
            'report' => $report,
            'type' => $ownerType,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $safeName = preg_replace('/[^A-Za-z0-9\-]/', '-', (string) $report['name']);
        $filename = 'Owner-Report-' . strtoupper($ownerType) . '-' . $safeName . '-' . $startDate->format('d-M-Y') . '.pdf';

        return $pdf->download($filename);
    }

    public function exportOwnerExcel(OwnerReportRequest $request, $type, $id)
    {
        [$startDate, $endDate] = $this->resolvePeriod($request);

        $ownerType = (string) $request->route('type');
        $ownerId = (string) $request->route('id');
        $filename = 'Owner-Report-' . strtoupper($ownerType) . '-' . $ownerId . '-' . $startDate->format('d-M-Y') . '.xlsx';

        return Excel::download(new OwnerReportExport($ownerType, $ownerId, $startDate, $endDate), $filename);
    }

    /**
     * Legacy aliases kept to avoid breaking existing links.
     */
    public function exportLabaRugiPDF(ReportPeriodRequest $request)
    {
        return $this->exportProfitLossPdf($request);
    }

    public function exportArusKasPDF(ReportPeriodRequest $request)
    {
        return $this->exportCashFlowPdf($request);
    }

    private function resolvePeriod(ReportPeriodRequest $request): array
    {
        return [$request->startDate(), $request->endDate()];
    }

    private function paginateArray(array $items, int $perPage, string $pageName, ?string $fragment = null): LengthAwarePaginator
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage($pageName);
        $collection = collect($items);
        $paginator = new LengthAwarePaginator(
            $collection->forPage($currentPage, $perPage)->values(),
            $collection->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
                'query' => request()->query(),
            ]
        );

        if ($fragment) {
            $paginator->fragment($fragment);
        }

        return $paginator;
    }
}
