<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Requests\Checkout\ProcessCheckoutRequest;
use App\Http\Requests\Checkout\RequestRefundRequest;
use App\Services\CheckoutService;
use App\Services\StripeOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkoutService,
        private readonly StripeOrderService $stripeOrderService
    ) {
        $this->middleware('auth')->except(['webhook']);
    }

    public function index()
    {
        $cartItems = $this->checkoutService->getCartItemsForUser((int) Auth::id());

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        $total = $cartItems->sum('subtotal');
        $user = Auth::user();

        return view('landing.checkout', compact('cartItems', 'total', 'user'));
    }

    public function checkStatus($orderId)
    {
        $order = $this->checkoutService->getOrderStatusForUser($orderId, (int) Auth::id());

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        if ($order->status === 'pending') {
            $this->stripeOrderService->syncPendingOrder($order);
            $order->refresh();
        }

        return response()->json([
            'status' => $order->status,
            'redeem_code' => $order->redeem_code,
            'paid_at' => $order->paid_at?->toISOString(),
        ]);
    }

    public function process(ProcessCheckoutRequest $request)
    {
        try {
            $result = $this->checkoutService->createOrderFromCart(
                $request->validated(),
                (int) Auth::id(),
                [
                    'source_ip' => (string) $request->ip(),
                ]
            );

            $response = [
                'success' => true,
                'order_id' => $result['order_id'],
            ];

            if (!empty($result['client_secret'])) {
                $response['client_secret'] = $result['client_secret'];
            }

            return response()->json($response);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Throwable $e) {
            Log::error('Checkout error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function webhook(Request $request)
    {
        try {
            $this->stripeOrderService->handleWebhook(
                $request->getContent(),
                $request->header('Stripe-Signature')
            );
        } catch (\UnexpectedValueException|\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Webhook signature error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\Throwable $e) {
            Log::error('Webhook processing error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], 500);
        }

        return response()->json(['status' => 'success'], 200);
    }

    public function success(Request $request)
    {
        $orderId = $request->query('order_id');
        $order = $this->checkoutService->getOrderWithItemsForUser($orderId, (int) Auth::id());

        $this->stripeOrderService->syncPendingOrder($order);
        $order->refresh();

        return view('landing.payment-success', compact('order'));
    }

    public function failed(Request $request)
    {
        $orderId = $request->query('order_id');
        $order = $this->checkoutService->getOrderForUser($orderId, (int) Auth::id());

        return view('landing.payment-failed', compact('order'));
    }

    public function history(Request $request)
    {
        return view('landing.order-history', $this->checkoutService->getHistoryForUser(
            (int) Auth::id(),
            $request->query('status')
        ));
    }

    public function show($id_order)
    {
        $order = $this->checkoutService->getOrderWithItemsForUser($id_order, (int) Auth::id());

        $this->stripeOrderService->syncPendingOrder($order);
        $order->refresh();

        return view('landing.order-detail', compact('order'));
    }

    public function cancel($id_order)
    {
        if (!$this->checkoutService->cancelPendingOrder($id_order, (int) Auth::id())) {
            return redirect()->back()->with('error', 'This order cannot be cancelled.');
        }

        return redirect()->route('orders.history')
            ->with('success', 'Your order has been cancelled successfully.');
    }

    public function requestRefund(RequestRefundRequest $request, $id_order)
    {
        if (!$this->checkoutService->requestRefund(
            $id_order,
            (int) Auth::id(),
            (string) $request->input('reason'),
            [
                'source_ip' => (string) $request->ip(),
            ]
        )) {
            return redirect()->back()->with('error', 'Refund requests are available only for paid orders.');
        }

        return redirect()->back()->with('success', 'Refund request submitted successfully. Awaiting admin approval.');
    }

    public function invoice($id_order)
    {
        $order = $this->checkoutService->getOrderWithItemsForUser($id_order, (int) Auth::id());

        if (in_array($order->status, ['pending', 'failed', 'cancelled'], true)) {
            return redirect()->back()->with('error', 'Invoice is available after payment is completed.');
        }

        $pdf = Pdf::loadView('pdf.invoice', compact('order'));

        return $pdf->download('Invoice-' . $order->id_order . '.pdf');
    }
}
