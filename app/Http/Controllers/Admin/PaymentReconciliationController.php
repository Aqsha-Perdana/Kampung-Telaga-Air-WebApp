<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payments\PaymentReconciliationQueryService;
use App\Services\Payments\PaymentReconciliationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentReconciliationController extends Controller
{
    public function __construct(
        private readonly PaymentReconciliationQueryService $paymentReconciliationQueryService,
        private readonly PaymentReconciliationService $paymentReconciliationService
    ) {
    }

    public function index(Request $request): View
    {
        $filters = [
            'start_date' => $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d')),
            'end_date' => $request->input('end_date', Carbon::now()->format('Y-m-d')),
            'issue_type' => $request->input('issue_type', 'all'),
            'payment_method' => $request->input('payment_method', 'all'),
            'payment_channel' => $request->input('payment_channel', 'all'),
        ];

        return view('admin.payment-reconciliation.index', $this->paymentReconciliationQueryService->getDashboardData($filters));
    }

    public function refresh(string $orderId): RedirectResponse
    {
        $order = Order::where('id_order', $orderId)->firstOrFail();

        if (!in_array($order->payment_method, ['stripe', 'xendit'], true)) {
            return redirect()->back()->with('error', 'This order does not use a reconcilable gateway.');
        }

        $result = $this->paymentReconciliationService->refreshGatewayFeeIfAvailable($order);

        if (($result['updated'] ?? false) !== true) {
            return redirect()->back()->with('error', 'The gateway fee is not final yet. Please try again later.');
        }

        return redirect()->back()->with(
            'success',
            'Gateway fee refreshed to ' . format_ringgit((float) ($result['fee_amount'] ?? 0)) . ' (' . gateway_fee_source_label($result['fee_source'] ?? null) . ').'
        );
    }
}
