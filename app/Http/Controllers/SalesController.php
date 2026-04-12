<?php

namespace App\Http\Controllers;

use App\Exports\SalesRecordExport;
use App\Models\Order;
use App\Services\CustomerEmailService;
use App\Services\OrderItemSnapshotService;
use App\Services\RefundService;
use App\Services\Sales\SalesQueryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SalesController extends Controller
{
    public function __construct(
        private readonly SalesQueryService $salesQueryService,
        private readonly OrderItemSnapshotService $snapshotService,
        private readonly CustomerEmailService $customerEmailService
    ) {
    }

    public function index(Request $request)
    {
        $filters = $this->dashboardFiltersFromRequest($request);
        $viewPayload = $this->salesQueryService->getDashboardData($filters);
        $viewPayload['recentTransactions'] = $this->paginateCollection(
            $viewPayload['recentTransactions'],
            10,
            'sales_page',
            'recent-transactions'
        );

        return view('admin.sales.index', array_merge($viewPayload, [
            'startDate' => $filters['start_date'],
            'endDate' => $filters['end_date'],
            'status' => $filters['status'],
            'displayCurrency' => $filters['display_currency'],
            'paymentMethod' => $filters['payment_method'],
            'paymentChannel' => $filters['payment_channel'],
            'gatewayFeeSource' => $filters['gateway_fee_source'],
        ]));
    }

    public function export(Request $request)
    {
        $filters = $this->dashboardFiltersFromRequest($request);
        $filename = sprintf(
            'sales-record-%s-to-%s.xlsx',
            Carbon::parse($filters['start_date'])->format('Ymd'),
            Carbon::parse($filters['end_date'])->format('Ymd')
        );

        return Excel::download(new SalesRecordExport($filters), $filename);
    }

    public function show($orderId)
    {
        $detailPayload = $this->salesQueryService->getOrderDetailData((string) $orderId);

        if ($detailPayload === null) {
            abort(404, 'Order not found');
        }

        return view('admin.sales.detail', $detailPayload);
    }

    private function dashboardFiltersFromRequest(Request $request): array
    {
        return [
            'start_date' => $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d')),
            'end_date' => $request->input('end_date', Carbon::now()->format('Y-m-d')),
            'status' => $request->input('status', 'all'),
            'display_currency' => $request->input('display_currency', 'all'),
            'payment_method' => $request->input('payment_method', 'all'),
            'payment_channel' => $request->input('payment_channel', 'all'),
            'gateway_fee_source' => $request->input('gateway_fee_source', 'all'),
        ];
    }

    private function paginateCollection(Collection|array $items, int $perPage, string $pageName, ?string $fragment = null): LengthAwarePaginator
    {
        $collection = $items instanceof Collection ? $items->values() : collect($items)->values();
        $currentPage = LengthAwarePaginator::resolveCurrentPage($pageName);
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

    private function calculateTotals($itemsWithBreakdown, $baseAmount)
    {
        $vendorTotals = [
            'boat' => $itemsWithBreakdown->sum(function ($item) {
                return ($item->breakdown['boat_total'] ?? 0);
            }),
            'homestay' => $itemsWithBreakdown->sum(function ($item) {
                return ($item->breakdown['homestay_total'] ?? 0);
            }),
            'culinary' => $itemsWithBreakdown->sum(function ($item) {
                return ($item->breakdown['culinary_total'] ?? 0);
            }),
            'kiosk' => $itemsWithBreakdown->sum(function ($item) {
                return ($item->breakdown['kiosk_total'] ?? 0);
            }),
        ];

        $totalCosts = array_sum($vendorTotals);
        $vendorTotals['company'] = $baseAmount - $totalCosts;

        return $vendorTotals;
    }

    public function downloadManifest($orderId)
    {
        $order = DB::table('orders')->where('id_order', $orderId)->first();
        if (!$order) {
            abort(404);
        }

        $orderItems = DB::table('order_items')
            ->where('order_items.id_order', $orderId)
            ->select('order_items.*')
            ->get();

        $itemsWithBreakdown = $orderItems->map(function ($item) {
            $item->breakdown = $this->snapshotService->breakdownFromOrderItem($item);

            return $item;
        });

        $totals = $this->calculateTotals($itemsWithBreakdown, $order->base_amount);

        $pdf = \PDF::loadView('invoice.manifest', [
            'order' => $order,
            'items' => $itemsWithBreakdown,
            'totals' => $totals,
        ]);

        return $pdf->download('Manifest-' . $orderId . '.pdf');
    }

    public function approveRefund(Request $request, $id_order, RefundService $refundService)
    {
        $result = $refundService->approveRefund((string) $id_order);

        if (($result['ok'] ?? false) !== true) {
            return redirect()->back()->with('error', $result['message']);
        }

        if (($result['code'] ?? null) === 'already_refunded') {
            return redirect()->back()->with('success', 'This refund has already been processed.');
        }

        if (($result['code'] ?? null) === 'processing') {
            return redirect()->back()->with(
                'success',
                'Refund request approved and submitted to the gateway. RM '
                . number_format((float) ($result['refund_amount'] ?? 0), 2)
                . ' is now waiting for final refund confirmation (Refund fee: RM '
                . number_format((float) ($result['refund_fee'] ?? 0), 2)
                . ').'
            );
        }

        return redirect()->back()->with(
            'success',
            'Refund approved. RM '
            . number_format((float) ($result['refund_amount'] ?? 0), 2)
            . ' has been returned (Refund fee: RM '
            . number_format((float) ($result['refund_fee'] ?? 0), 2)
            . ').'
        );
    }

    public function rejectRefund(Request $request, $id_order)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $order = Order::findOrFail($id_order);

        if ($order->status !== 'refund_requested') {
            return redirect()->back()->with('error', 'This order is not in refund-requested status.');
        }

        if ($order->refund_status === 'processing') {
            return redirect()->back()->with('error', 'Refund is currently processing and cannot be rejected right now.');
        }

        $order->update([
            'status' => 'paid',
            'refund_status' => 'rejected',
            'refund_rejected_reason' => $request->reason,
            'refund_failure_reason' => null,
        ]);

        $order->loadMissing('items');
        $this->customerEmailService->sendRefundRejected($order);

        return redirect()->back()->with('success', 'Refund request rejected. Order status has been restored to Paid.');
    }
}
