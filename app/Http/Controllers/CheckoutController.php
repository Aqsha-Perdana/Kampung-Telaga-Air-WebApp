<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Webhook;
use Illuminate\Support\Str;
use App\Services\ExchangeRateService;

class CheckoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['webhook']);
    }

    private function getCartItems()
    {
        return Cart::with('paket')->where('user_id', Auth::id())->get();
    }

    public function index()
    {
        $cartItems = $this->getCartItems();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang Anda kosong!');
        }

        $total = $cartItems->sum('subtotal'); // ALWAYS in MYR
        $user = Auth::user();

        return view('landing.checkout', compact('cartItems', 'total', 'user'));
    }

    /**
     * Process Payment - ALWAYS PAY IN MYR
     */
    /**
     * API endpoint untuk cek status order (untuk polling)
     */
    public function checkStatus($orderId)
    {
        $order = Order::where('id_order', $orderId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        return response()->json([
            'status' => $order->status,
            'redeem_code' => $order->redeem_code,
            'paid_at' => $order->paid_at?->toISOString()
        ]);
    }

    public function process(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'nullable|string',
            'display_currency' => 'nullable|in:MYR,USD,IDR,SGD,EUR,GBP,AUD,JPY,CNY',
            'payment_method' => 'required|in:stripe,bank_transfer,ewallet'
        ]);

        $cartItems = $this->getCartItems();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang kosong!'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // ✅ BASE AMOUNT - ALWAYS MYR
            $baseAmount = $cartItems->sum('subtotal');
            
            // ✅ Display amount (optional, hanya untuk UI)
            $displayCurrency = $validated['display_currency'] ?? 'MYR';
            $displayAmount = null;
            $displayRate = 1;

            if ($displayCurrency !== 'MYR') {
                $exchangeService = app(ExchangeRateService::class);
                $displayRate = $exchangeService->getRate($displayCurrency);
                $displayAmount = $exchangeService->convert($baseAmount, $displayCurrency);
            }

            // ✅ Create Order - PAYMENT ALWAYS MYR
            $order = Order::create([
                'user_id' => Auth::id(),
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'customer_address' => $validated['customer_address'],

                // PAYMENT AMOUNT (ALWAYS MYR)
                'base_amount' => $baseAmount,
                'total_amount' => $baseAmount, // = base_amount

                // DISPLAY ONLY (optional)
                'display_currency' => $displayCurrency,
                'display_amount' => $displayAmount,
                'display_exchange_rate' => $displayRate,

                'payment_method' => $validated['payment_method'],
                'status' => 'pending'
            ]);

            // Create Order Items
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'id_order' => $order->id_order,
                    'id_paket' => $item->id_paket,
                    'nama_paket' => $item->paket->nama_paket,
                    'durasi_hari' => $item->paket->durasi_hari,
                    'jumlah_peserta' => $item->jumlah_peserta,
                    'tanggal_keberangkatan' => $item->tanggal_keberangkatan,
                    'catatan' => $item->catatan,
                    'harga_satuan' => $item->harga_satuan,
                    'subtotal' => $item->subtotal
                ]);
            }

            // ✅ STRIPE PAYMENT INTENT - ALWAYS MYR
            if ($validated['payment_method'] === 'stripe') {
                Stripe::setApiKey(config('services.stripe.secret'));

                // PAYMENT ALWAYS IN MYR (smallest unit = cents)
                $amountInCents = (int) ($baseAmount * 100);

                $paymentIntent = PaymentIntent::create([
                    'amount' => $amountInCents,
                    'currency' => 'myr', // ✅ ALWAYS MYR
                    'metadata' => [
                        'order_id' => $order->id_order,
                        'user_id' => Auth::id(),
                        'customer_name' => $validated['customer_name'],
                        'base_amount' => $baseAmount,
                        'display_currency' => $displayCurrency,
                        'display_amount' => $displayAmount
                    ],
                    'receipt_email' => $validated['customer_email']
                ]);

                $order->update(['payment_intent_id' => $paymentIntent->id]);

                PaymentLog::create([
                    'id_order' => $order->id_order,
                    'payment_intent_id' => $paymentIntent->id,
                    'payment_method' => 'stripe',
                    'amount' => $baseAmount, // ✅ ALWAYS MYR
                    'currency' => 'MYR',     // ✅ ALWAYS MYR
                    'status' => 'pending',
                    'response_data' => json_encode($paymentIntent)
                ]);

                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'client_secret' => $paymentIntent->client_secret,
                    'order_id' => $order->id_order
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'order_id' => $order->id_order]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Checkout error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Stripe Webhook Handler
     */
    public function webhook(Request $request)
    {
        \Log::info('=== WEBHOOK RECEIVED ===');
        
        Stripe::setApiKey(config('services.stripe.secret'));
        $endpoint_secret = config('services.stripe.webhook.secret');

        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
            \Log::info('✅ Signature verified! Event: ' . $event->type);
        } catch (\Exception $e) {
            \Log::error('❌ Webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle events
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentSuccess($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentFailed($event->data->object);
                break;

            case 'payment_intent.canceled':
                $this->handlePaymentCanceled($event->data->object);
                break;
        }

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Handle successful payment
     */
    private function handlePaymentSuccess($paymentIntent)
    {
        \Log::info('🔹 Processing payment success');
        
        DB::beginTransaction();
        try {
            $orderId = $paymentIntent->metadata->order_id ?? null;
            
            if (!$orderId) {
                \Log::error('❌ No order_id in metadata');
                return;
            }
            
            $order = Order::where('id_order', $orderId)->first();

            if (!$order) {
                \Log::error('❌ Order not found: ' . $orderId);
                return;
            }

            // Idempotency check
            if ($order->status === 'paid' && $order->redeem_code) {
                \Log::info('✅ Already processed: ' . $orderId);
                DB::commit();
                return;
            }

            // Generate redeem code
            if (!$order->redeem_code) {
                $redeemCode = 'KTA-' . strtoupper(Str::random(4)) . '-' . rand(1000, 9999);
                \Log::info('🎫 Generated redeem code: ' . $redeemCode);
            } else {
                $redeemCode = $order->redeem_code;
            }

            // Update order
            $order->update([
                'status' => 'paid',
                'paid_at' => $order->paid_at ?? now(),
                'redeem_code' => $redeemCode,
                'payment_intent_id' => $paymentIntent->id
            ]);

            // Update payment log
            PaymentLog::where('payment_intent_id', $paymentIntent->id)
                ->update([
                    'status' => 'success',
                    'response_data' => json_encode($paymentIntent)
                ]);

            // Clear cart
            Cart::where('user_id', $order->user_id)->delete();

            \Log::info('✅ Payment processed successfully');
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('❌ Error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function handlePaymentFailed($paymentIntent)
    {
        try {
            $orderId = $paymentIntent->metadata->order_id;
            
            $order = Order::where('id_order', $orderId)->first();

            if ($order) {
                $order->update(['status' => 'failed']);
                
                PaymentLog::where('payment_intent_id', $paymentIntent->id)
                    ->update([
                        'status' => 'failed',
                        'response_data' => json_encode($paymentIntent)
                    ]);
            }
        } catch (\Exception $e) {
            \Log::error('Webhook failed: ' . $e->getMessage());
        }
    }

    private function handlePaymentCanceled($paymentIntent)
    {
        try {
            $orderId = $paymentIntent->metadata->order_id;
            
            $order = Order::where('id_order', $orderId)->first();

            if ($order) {
                $order->update(['status' => 'cancelled']);
                
                PaymentLog::where('payment_intent_id', $paymentIntent->id)
                    ->update([
                        'status' => 'cancelled',
                        'response_data' => json_encode($paymentIntent)
                    ]);
            }
        } catch (\Exception $e) {
            \Log::error('Webhook canceled: ' . $e->getMessage());
        }
    }

    /**
     * Success page
     */
    public function success(Request $request)
    {
        $orderId = $request->query('order_id');

        $order = Order::with('items.paket')
            ->where('id_order', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Check with Stripe if still pending
        if ($order->status === 'pending' && $order->payment_intent_id) {
            try {
                Stripe::setApiKey(config('services.stripe.secret'));
                $paymentIntent = PaymentIntent::retrieve($order->payment_intent_id);
                
                if ($paymentIntent->status === 'succeeded') {
                    $this->handlePaymentSuccess($paymentIntent);
                    $order->refresh();
                }
            } catch (\Exception $e) {
                \Log::error('Error checking payment: ' . $e->getMessage());
            }
        }

        return view('landing.payment-success', compact('order'));
    }

    public function failed(Request $request)
    {
        $orderId = $request->query('order_id');
        
        $order = Order::where('id_order', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('landing.payment-failed', compact('order'));
    }

    // Order history & details
    public function history(Request $request)
{
    $status = $request->query('status');
    
    $query = Order::with('items.paket')
        ->where('user_id', Auth::id())
        ->orderBy('created_at', 'desc');
    
    if ($status && in_array($status, ['pending', 'paid', 'failed', 'cancelled'])) {
        // ✅ Jika filter "failed", tampilkan juga "cancelled"
        if ($status === 'failed') {
            $query->whereIn('status', ['failed', 'cancelled']);
        } else {
            $query->where('status', $status);
        }
    }
    
    $orders = $query->paginate(10);
    
    $stats = [
        'total' => Order::where('user_id', Auth::id())->count(),
        'paid' => Order::where('user_id', Auth::id())->where('status', 'paid')->count(),
        'pending' => Order::where('user_id', Auth::id())->where('status', 'pending')->count(),
        'failed' => Order::where('user_id', Auth::id())->whereIn('status', ['failed', 'cancelled'])->count(),
    ];
    
    return view('landing.order-history', compact('orders', 'stats', 'status'));
}

    public function show($id_order)
    {
        $order = Order::with('items.paket')
            ->where('id_order', $id_order)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        return view('landing.order-detail', compact('order'));
    }

    public function cancel($id_order)
    {
        $order = Order::where('id_order', $id_order)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        if ($order->status !== 'pending') {
            return redirect()->back()->with('error', 'Pesanan tidak dapat dibatalkan');
        }
        
        $order->update(['status' => 'cancelled']);
        
        return redirect()->route('orders.history')
            ->with('success', 'Pesanan berhasil dibatalkan');
    }
}