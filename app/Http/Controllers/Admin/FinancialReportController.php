<?php

namespace App\Http\Controllers\Admin;

use App\Exports\FinancialReportExport;
use App\Exports\OwnerReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FinancialReport\OwnerReportRequest;
use App\Http\Requests\Admin\FinancialReport\ReportPeriodRequest;
use App\Services\FinancialReport\FinancialStatementService;
use App\Services\FinancialReport\OwnerReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
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

        $cacheKey = 'admin.financial.index.v1.' . md5(json_encode([
            'start' => $startDate->toDateString(),
            'end' => $endDate->toDateString(),
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

        return view('admin.financial-reports.index', compact(
            'profitLoss',
            'cashFlow',
            'ownerSummary',
            'startDate',
            'endDate'
        ));
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
}
