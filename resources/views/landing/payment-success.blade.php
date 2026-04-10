@extends('landing.layout')

@section('content')
@php
    $paymentMethodLabel = payment_method_label($order->payment_method);
@endphp
<style>
.payment-feedback-shell {
    background: linear-gradient(180deg, #fbfdff 0%, #ffffff 100%);
}

.payment-feedback-card {
    border-radius: 24px;
}

.payment-feedback-card--success {
    position: relative;
    overflow: hidden;
}

.payment-feedback-card--success::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at top center, rgba(34, 197, 94, 0.12), transparent 38%),
        linear-gradient(180deg, rgba(240, 253, 244, 0.85) 0%, rgba(255, 255, 255, 0) 45%);
    pointer-events: none;
}

.payment-feedback-icon {
    width: 6rem;
    height: 6rem;
    margin: 0 auto;
    border-radius: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #eff6ff;
}

.payment-feedback-icon--success {
    background: #ecfdf5;
    animation: successIconPop 0.7s cubic-bezier(.2, .9, .2, 1.2) 0.1s both, successIconGlow 2.8s ease-in-out 1s infinite;
}

.payment-feedback-icon--warning {
    background: #fff7ed;
}

.payment-feedback-icon--danger {
    background: #fef2f2;
}

.payment-feedback-section {
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    background: #fff;
}

.payment-feedback-summary-row {
    padding: 0.35rem 0;
}

.payment-feedback-summary-row .label {
    color: #64748b;
}

.payment-feedback-amount {
    color: #16a34a;
}

.payment-feedback-meta {
    border-top: 1px solid #e5e7eb;
    padding-top: 1rem;
}

.payment-feedback-celebrate {
    animation: fadeLiftIn 0.75s ease both;
}

.payment-feedback-celebrate-delay-1 {
    animation-delay: 0.15s;
}

.payment-feedback-celebrate-delay-2 {
    animation-delay: 0.28s;
}

.payment-feedback-celebrate-delay-3 {
    animation-delay: 0.4s;
}

.payment-feedback-celebrate-delay-4 {
    animation-delay: 0.52s;
}

.payment-success-confetti {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
}

.payment-success-confetti-piece {
    position: absolute;
    top: -10%;
    width: 10px;
    height: 18px;
    border-radius: 999px;
    opacity: 0.92;
    animation: confettiDrop linear forwards;
}

.payment-feedback-actions .btn {
    min-width: 180px;
}

@keyframes successIconPop {
    0% {
        transform: scale(0.65) rotate(-10deg);
        opacity: 0;
    }
    70% {
        transform: scale(1.08) rotate(3deg);
        opacity: 1;
    }
    100% {
        transform: scale(1) rotate(0);
        opacity: 1;
    }
}

@keyframes successIconGlow {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.18);
    }
    50% {
        box-shadow: 0 0 0 14px rgba(34, 197, 94, 0);
    }
}

@keyframes fadeLiftIn {
    0% {
        opacity: 0;
        transform: translateY(18px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes confettiDrop {
    0% {
        transform: translate3d(0, 0, 0) rotate(0deg);
        opacity: 0;
    }
    10% {
        opacity: 1;
    }
    100% {
        transform: translate3d(var(--drift, 0px), 120vh, 0) rotate(var(--spin, 540deg));
        opacity: 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .payment-feedback-icon--success,
    .payment-feedback-celebrate,
    .payment-success-confetti-piece {
        animation: none !important;
    }
}

@media (max-width: 767.98px) {
    .payment-feedback-actions .btn {
        min-width: 100%;
    }
}
</style>
<section class="py-5 payment-feedback-shell" style="margin-top: 80px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                @if($order->status === 'paid')
                    {{-- Payment Success --}}
                    <div class="card shadow-lg border-0 payment-feedback-card payment-feedback-card--success">
                        <div class="payment-success-confetti" id="payment-success-confetti" aria-hidden="true"></div>
                        <div class="card-body p-5 text-center">
                            <div class="mb-4">
                                <div class="payment-feedback-icon payment-feedback-icon--success">
                                    <i class="bi bi-check-circle-fill text-success" style="font-size: 3.5rem;"></i>
                                </div>
                            </div>
                            <h2 class="mb-3 payment-feedback-celebrate payment-feedback-celebrate-delay-1">Payment Successful!</h2>
                            <p class="text-muted mb-4 payment-feedback-celebrate payment-feedback-celebrate-delay-2">Thank you for your order.</p>

                            @if($order->redeem_code)
                                {{-- Redeem code generated --}}
                                <div class="alert alert-success text-start mt-3 payment-feedback-section payment-feedback-celebrate payment-feedback-celebrate-delay-3">
                                    <h5 class="mb-3">
                                        <i class="bi bi-ticket-perforated"></i> Your Redemption Code
                                    </h5>
                                    <div class="bg-white p-3 rounded text-center mb-3">
                                        <p class="fs-3 fw-bold mb-1 font-monospace text-success">
                                            {{ $order->redeem_code }}
                                        </p>
                                        <button class="btn btn-sm btn-outline-success" onclick="copyRedeemCode(event)">
                                            <i class="bi bi-clipboard"></i> Copy Code
                                        </button>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i> 
                                        Present this code to the service providers involved in your tour package.
                                    </small>
                                </div>
                            @else
                                {{-- Redeem code not generated yet --}}
                                <div class="alert alert-warning text-start mt-3 payment-feedback-section payment-feedback-celebrate payment-feedback-celebrate-delay-3">
                                    <h5 class="mb-2">
                                        <i class="bi bi-hourglass-split"></i> Processing Your Redemption Code
                                    </h5>
                                    <p class="mb-2">
                                        Your redemption code is being generated. This usually takes a few seconds.
                                    </p>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                             role="progressbar" style="width: 100%"></div>
                                    </div>
                                    <small class="text-muted d-block mt-2" id="redeem-sync-note">
                                        <i class="bi bi-clock"></i> Page will auto-refresh in a moment...
                                    </small>
                                </div>
                            @endif
                            
                            {{-- Order Information --}}
                            <div class="alert alert-light text-start mt-4 payment-feedback-section payment-feedback-celebrate payment-feedback-celebrate-delay-4">
                                <h5 class="mb-3">
                                    <i class="bi bi-receipt"></i> Order Details
                                </h5>
                                
                                <div class="row payment-feedback-summary-row">
                                    <div class="col-4 text-muted">Order ID:</div>
                                    <div class="col-8">
                                        <strong class="font-monospace">{{ $order->id_order }}</strong>
                                    </div>
                                </div>

                                <div class="row payment-feedback-summary-row">
                                    <div class="col-4 text-muted">Payment Date:</div>
                                    <div class="col-8">
                                        <strong>{{ $order->paid_at ? $order->paid_at->format('d M Y, H:i') : now()->format('d M Y, H:i') }}</strong>
                                    </div>
                                </div>

                                <div class="row payment-feedback-summary-row">
                                    <div class="col-4 text-muted">Payment Method:</div>
                                    <div class="col-8">
                                        <strong>{{ $paymentMethodLabel }}</strong>
                                    </div>
                                </div>

                                <hr>

                                {{-- Payment Amount --}}
                                <div class="row payment-feedback-summary-row">
                                    <div class="col-4 text-muted">Amount Charged:</div>
                                    <div class="col-8">
                                        <strong class="payment-feedback-amount fs-5">
                                            RM {{ number_format($order->base_amount, 2) }}
                                        </strong>
                                        <span class="badge bg-success ms-2">MYR</span>
                                    </div>
                                </div>

                                {{-- Display Amount (if different) --}}
                                @if($order->display_currency && $order->display_currency !== 'MYR')
                                    <div class="row payment-feedback-summary-row">
                                        <div class="col-4 text-muted">Display Amount:</div>
                                        <div class="col-8">
                                            <span class="text-muted">
                                                ≈ {{ $order->display_currency }} 
                                                {{ $order->display_currency === 'IDR' || $order->display_currency === 'JPY' 
                                                    ? number_format($order->display_amount, 0) 
                                                    : number_format($order->display_amount, 2) }}
                                            </span>
                                            <small class="d-block text-muted" style="font-size: 0.75rem;">
                                                (Reference only, actual charge in MYR)
                                            </small>
                                        </div>
                                    </div>
                                @endif

                                <hr>

                                <div class="row payment-feedback-summary-row">
                                    <div class="col-4 text-muted">Email:</div>
                                    <div class="col-8">
                                        <strong>{{ $order->customer_email }}</strong>
                                        <br>
                                    </div>
                                </div>
                            </div>

                            {{-- Order Items --}}
                            @if($order->items && $order->items->count() > 0)
                            <div class="alert alert-light text-start mt-3 payment-feedback-section payment-feedback-celebrate payment-feedback-celebrate-delay-4">
                                <h6 class="mb-3">
                                    <i class="bi bi-list-check"></i> Package Details
                                </h6>
                                @foreach($order->items as $item)
                                <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>{{ $item->nama_paket }}</strong>
                                            <div class="text-muted small">
                                                <i class="bi bi-calendar-event"></i> 
                                                {{ \Carbon\Carbon::parse($item->tanggal_keberangkatan)->format('d M Y') }}
                                            </div>
                                            <div class="text-muted small">
                                                <i class="bi bi-people"></i> {{ $item->jumlah_peserta }} participant(s)
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <strong>RM {{ number_format($item->subtotal, 2) }}</strong>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif

                            {{-- Action Buttons --}}
                            <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-center payment-feedback-actions payment-feedback-celebrate payment-feedback-celebrate-delay-4">
                                <a href="{{ route('orders.history') }}" class="btn btn-primary">
                                    <i class="bi bi-list-ul"></i> View Order History
                                </a>
                                <a href="{{ route('orders.show', $order->id_order) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-file-earmark-text"></i> Order Details
                                </a>
                            </div>

                            <div class="mt-3">
                                <a href="{{ route('home') }}" class="btn btn-link">
                                    <i class="bi bi-house"></i> Back to Home
                                </a>
                            </div>
                            
                        </div>
                    </div>

                @elseif($order->status === 'pending')
                    {{-- Payment Processing --}}
                    <div class="card shadow-lg border-0 payment-feedback-card">
                        <div class="card-body p-5 text-center">
                            <div class="mb-4">
                                <div class="payment-feedback-icon payment-feedback-icon--warning">
                                    <div class="spinner-border text-warning" style="width: 3rem; height: 3rem;"></div>
                                </div>
                            </div>
                            <h2 class="mb-3">Payment Processing</h2>
                            <p class="text-muted mb-4">Order ID: <strong>{{ $order->id_order }}</strong></p>

                            <div class="alert alert-warning payment-feedback-section">
                                <div class="mb-3">
                                    <i class="bi bi-hourglass-split fs-3"></i>
                                </div>
                                <p class="mb-2">Your payment is being verified by our system.</p>
                                <p class="mb-3" id="pending-sync-note">Please wait while we sync your payment status.</p>
                                
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" 
                                         role="progressbar" style="width: 100%"></div>
                                </div>
                            </div>

                            <div class="alert alert-light text-start payment-feedback-section">
                                <h6 class="mb-2">Payment Information:</h6>
                                <p class="mb-1">
                                    <strong>Amount:</strong> RM {{ number_format($order->base_amount, 2) }}
                                </p>
                                @if($order->display_currency && $order->display_currency !== 'MYR')
                                <p class="mb-1 text-muted small">
                                    Display: {{ $order->display_currency }} 
                                    {{ number_format($order->display_amount, $order->display_currency === 'IDR' ? 0 : 2) }}
                                </p>
                                @endif
                                <p class="mb-0">
                                    <strong>Method:</strong> {{ $paymentMethodLabel }}
                                </p>
                            </div>

                            <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-center payment-feedback-actions">
                                <button onclick="location.reload()" class="btn btn-primary">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh Page
                                </button>
                                <a href="{{ route('orders.history') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-list-ul"></i> Check Order History
                                </a>
                            </div>

                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> 
                                    If status doesn't update after 5 minutes, please contact our customer service.
                                </small>
                            </div>
                        </div>
                    </div>

                @else
                    {{-- Other Status (failed, cancelled) --}}
                    <div class="card shadow-lg border-0 payment-feedback-card">
                        <div class="card-body p-5 text-center">
                            <div class="mb-4">
                                <div class="payment-feedback-icon payment-feedback-icon--danger">
                                    <i class="bi bi-exclamation-circle text-danger" style="font-size: 3.5rem;"></i>
                                </div>
                            </div>
                            <h2 class="mb-3">Payment Status: {{ ucfirst($order->status) }}</h2>
                            <p class="text-muted mb-4">Order ID: <strong>{{ $order->id_order }}</strong></p>

                            <div class="alert alert-{{ $order->status === 'failed' ? 'danger' : 'secondary' }} payment-feedback-section">
                                <i class="bi bi-info-circle"></i>
                                @if($order->status === 'failed')
                                    <p class="mb-2">Your payment could not be processed.</p>
                                    <p class="mb-0">Please try again or contact customer service for assistance.</p>
                                @elseif($order->status === 'cancelled')
                                    <p class="mb-0">This order has been cancelled.</p>
                                @else
                                    <p class="mb-0">Please contact customer service for more information.</p>
                                @endif
                            </div>

                            <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-center payment-feedback-actions">
                                @if($order->status === 'failed')
                                <a href="{{ route('checkout.index') }}" class="btn btn-primary">
                                    <i class="bi bi-arrow-clockwise"></i> Try Again
                                </a>
                                @endif
                                <a href="{{ route('home') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-house"></i> Back to Home
                                </a>
                                <a href="{{ route('orders.history') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-list-ul"></i> Order History
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    const paymentStatusOrderId = '{{ $order->id_order }}';
    const paymentStatusUrl = `/api/order-status/${paymentStatusOrderId}`;
    const shouldPollPendingPayment = @json($order->status === 'pending');
    const shouldPollRedeemCode = @json($order->status === 'paid' && !$order->redeem_code);
    const shouldCelebrateSuccess = @json($order->status === 'paid');

    async function fetchOrderStatus() {
        const response = await fetch(paymentStatusUrl, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Unable to fetch order status.');
        }

        return response.json();
    }

    async function pollPaymentStatus() {
        const note = document.getElementById('pending-sync-note');
        let attempts = 0;
        const maxAttempts = 20;

        const intervalId = setInterval(async () => {
            attempts++;

            try {
                const data = await fetchOrderStatus();

                if (['paid', 'failed', 'cancelled'].includes(data.status)) {
                    clearInterval(intervalId);
                    window.location.reload();
                    return;
                }

                if (note) {
                    note.textContent = `Checking payment confirmation... (${attempts}/${maxAttempts})`;
                }
            } catch (error) {
                if (note) {
                    note.textContent = 'We are still checking your payment status. Please wait a moment.';
                }
            }

            if (attempts >= maxAttempts) {
                clearInterval(intervalId);
                if (note) {
                    note.textContent = 'Payment sync is taking longer than usual. Please refresh this page or check your order history.';
                }
            }
        }, 2000);
    }

    async function pollRedeemCode() {
        const note = document.getElementById('redeem-sync-note');
        let attempts = 0;
        const maxAttempts = 12;

        const intervalId = setInterval(async () => {
            attempts++;

            try {
                const data = await fetchOrderStatus();

                if (data.redeem_code) {
                    clearInterval(intervalId);
                    window.location.reload();
                    return;
                }

                if (note) {
                    note.textContent = `Generating your redemption code... (${attempts}/${maxAttempts})`;
                }
            } catch (error) {
                if (note) {
                    note.textContent = 'We are finalising your redemption code. Please keep this page open.';
                }
            }

            if (attempts >= maxAttempts) {
                clearInterval(intervalId);
                if (note) {
                    note.textContent = 'Redemption code is taking longer than usual. Please refresh this page shortly.';
                }
            }
        }, 2000);
    }

    function copyRedeemCode(event) {
        const code = '{{ $order->redeem_code }}';
        navigator.clipboard.writeText(code).then(() => {
            const btn = event?.target?.closest('button');
            if (!btn) {
                return;
            }
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check"></i> Copied!';
            btn.classList.remove('btn-outline-success');
            btn.classList.add('btn-success');

            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-success');
            }, 2000);
        }).catch(() => {
            alert('Failed to copy code. Please copy manually: ' + code);
        });
    }

    function launchSuccessCelebration() {
        if (!shouldCelebrateSuccess || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        const container = document.getElementById('payment-success-confetti');

        if (!container || container.dataset.loaded === 'true') {
            return;
        }

        container.dataset.loaded = 'true';

        const colors = ['#22c55e', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'];
        const pieces = 22;

        for (let i = 0; i < pieces; i++) {
            const piece = document.createElement('span');
            piece.className = 'payment-success-confetti-piece';
            piece.style.left = `${Math.random() * 100}%`;
            piece.style.background = colors[i % colors.length];
            piece.style.animationDuration = `${3 + Math.random() * 1.4}s`;
            piece.style.animationDelay = `${Math.random() * 0.6}s`;
            piece.style.setProperty('--drift', `${(Math.random() * 180) - 90}px`);
            piece.style.setProperty('--spin', `${360 + Math.random() * 540}deg`);
            container.appendChild(piece);

            setTimeout(() => piece.remove(), 5200);
        }
    }

    if (shouldPollPendingPayment) {
        pollPaymentStatus();
    }

    if (shouldPollRedeemCode) {
        pollRedeemCode();
    }

    if (shouldCelebrateSuccess) {
        launchSuccessCelebration();
    }
</script>
@endpush

@endsection
