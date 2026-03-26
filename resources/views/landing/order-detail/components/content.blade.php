@php
    $statusMeta = [
        'paid' => ['label' => 'Completed', 'class' => 'success', 'icon' => 'check-circle'],
        'pending' => ['label' => 'Pending', 'class' => 'warning text-dark', 'icon' => 'hourglass-split'],
        'failed' => ['label' => 'Failed', 'class' => 'danger', 'icon' => 'x-circle'],
        'cancelled' => ['label' => 'Cancelled', 'class' => 'secondary', 'icon' => 'slash-circle'],
        'refund_requested' => ['label' => 'Refund Requested', 'class' => 'warning text-dark', 'icon' => 'arrow-return-left'],
        'refunded' => ['label' => 'Refunded', 'class' => 'dark', 'icon' => 'arrow-counterclockwise'],
    ];

    $paymentMethodMeta = [
        'stripe' => ['label' => 'Credit/Debit Card (Stripe)', 'icon' => 'credit-card'],
    ];

    $status = $statusMeta[$order->status] ?? [
        'label' => ucfirst(str_replace('_', ' ', $order->status)),
        'class' => 'secondary',
        'icon' => 'info-circle',
    ];

    $paymentMethod = $paymentMethodMeta[$order->payment_method] ?? [
        'label' => strtoupper((string) $order->payment_method),
        'icon' => 'wallet2',
    ];

    $amount = (float) ($order->base_amount ?? $order->total_amount ?? 0);
    $invoiceEligibleStatuses = ['paid', 'refund_requested', 'refunded'];

    $hasRefundRequest = in_array($order->status, ['refund_requested', 'refunded'], true)
        || in_array($order->refund_status ?? null, ['requested', 'processing', 'rejected', 'succeeded', 'failed'], true)
        || !empty($order->refund_reason);

    $isPaidState = in_array($order->status, ['paid', 'refund_requested', 'refunded'], true)
        || in_array($order->refund_status ?? null, ['requested', 'processing', 'rejected', 'succeeded', 'failed'], true)
        || !empty($order->paid_at);

    $currentTimelineKey = match (true) {
        $order->status === 'cancelled' => 'cancelled',
        $order->status === 'refunded' => 'refunded',
        $order->status === 'refund_requested' || ($order->refund_status ?? null) === 'processing' => 'refund_requested',
        $order->status === 'paid' => 'paid',
        default => 'pending',
    };

    $refundRequestMeta = match (true) {
        ($order->refund_status ?? null) === 'rejected' => 'Your refund request was reviewed and rejected.',
        $order->status === 'refund_requested' => 'Your refund request is awaiting review.',
        $order->status === 'refunded' || ($order->refund_status ?? null) === 'succeeded' => 'Your refund request was approved and completed.',
        $hasRefundRequest => 'A refund request has been submitted for this order.',
        default => 'No refund request has been submitted.',
    };

    $timelineEvents = [
        [
            'key' => 'created',
            'label' => 'Created',
            'meta' => 'Booking record created.',
            'date' => \Carbon\Carbon::parse($order->created_at),
            'completed' => true,
            'active' => false,
            'icon' => 'circle-plus',
        ],
        [
            'key' => 'pending',
            'label' => 'Pending',
            'meta' => in_array($order->status, ['pending', 'failed'], true)
                ? 'Awaiting payment completion.'
                : 'Order passed the pending stage.',
            'date' => \Carbon\Carbon::parse($order->created_at),
            'completed' => true,
            'active' => $currentTimelineKey === 'pending',
            'icon' => 'hourglass-split',
        ],
        [
            'key' => 'paid',
            'label' => 'Paid',
            'meta' => $isPaidState
                ? 'Payment confirmed successfully.'
                : 'Payment has not been completed yet.',
            'date' => !empty($order->paid_at) ? \Carbon\Carbon::parse($order->paid_at) : null,
            'completed' => $isPaidState,
            'active' => $currentTimelineKey === 'paid',
            'icon' => 'credit-card',
        ],
        [
            'key' => 'refund_requested',
            'label' => 'Refund Requested',
            'meta' => $refundRequestMeta,
            'date' => null,
            'completed' => $hasRefundRequest,
            'active' => $currentTimelineKey === 'refund_requested',
            'icon' => 'arrow-return-left',
        ],
        [
            'key' => 'refunded',
            'label' => 'Refunded',
            'meta' => $order->status === 'refunded' || ($order->refund_status ?? null) === 'succeeded'
                ? 'Funds have been returned to you.'
                : 'Refund has not been completed.',
            'date' => !empty($order->refunded_at) ? \Carbon\Carbon::parse($order->refunded_at) : null,
            'completed' => $order->status === 'refunded' || ($order->refund_status ?? null) === 'succeeded',
            'active' => $currentTimelineKey === 'refunded',
            'icon' => 'arrow-counterclockwise',
        ],
        [
            'key' => 'cancelled',
            'label' => 'Cancelled',
            'meta' => $order->status === 'cancelled'
                ? 'Order was cancelled before completion.'
                : 'Order remains active.',
            'date' => $order->status === 'cancelled' && !empty($order->updated_at) ? \Carbon\Carbon::parse($order->updated_at) : null,
            'completed' => $order->status === 'cancelled',
            'active' => $currentTimelineKey === 'cancelled',
            'icon' => 'slash-circle',
        ],
    ];
@endphp

<style>
.order-status-timeline {
    position: relative;
}
.order-status-timeline::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 12px;
    bottom: 12px;
    width: 2px;
    background: #dbe3f0;
}
.order-status-timeline .timeline-item {
    position: relative;
    display: flex;
    gap: 1rem;
    padding-bottom: 1.5rem;
}
.order-status-timeline .timeline-item:last-child {
    padding-bottom: 0;
}
.order-status-timeline .timeline-marker {
    position: relative;
    z-index: 1;
    width: 42px;
    height: 42px;
    border-radius: 999px;
    background: #eef2ff;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 2px solid #dbe3f0;
}
.order-status-timeline .timeline-item.is-completed .timeline-marker {
    background: #dcfce7;
    border-color: #86efac;
    color: #15803d;
}
.order-status-timeline .timeline-item.is-active .timeline-marker {
    background: #dbeafe;
    border-color: #93c5fd;
    color: #1d4ed8;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
}
.order-status-timeline .timeline-content {
    flex: 1;
    min-width: 0;
    padding-top: 0.2rem;
}
.order-status-timeline .timeline-content h6 {
    margin-bottom: 0.25rem;
}
.order-status-timeline .timeline-content p {
    margin-bottom: 0;
    color: #64748b;
    font-size: 0.95rem;
}
@media (max-width: 767.98px) {
    .order-status-timeline::before {
        left: 18px;
    }
    .order-status-timeline .timeline-item {
        gap: 0.75rem;
    }
    .order-status-timeline .timeline-marker {
        width: 38px;
        height: 38px;
    }
}
</style>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="mb-4">
            <a href="{{ route('orders.history') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Order History
            </a>
        </div>

        <div class="row">
            <div class="col-md-8 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-receipt"></i> Order Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-md-4">
                                <strong>Order ID:</strong>
                            </div>
                            <div class="col-md-8">
                                <span class="font-monospace">{{ $order->id_order }}</span>
                                <button class="btn btn-sm btn-outline-secondary ms-2"
                                        onclick="copyOrderId()"
                                        title="Copy Order ID">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>

                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-md-4">
                                <strong>Status:</strong>
                            </div>
                            <div class="col-md-8">
                                <span class="badge bg-{{ $status['class'] }}">
                                    <i class="bi bi-{{ $status['icon'] }}"></i> {{ $status['label'] }}
                                </span>
                            </div>
                        </div>

                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-md-4">
                                <strong>Order Date:</strong>
                            </div>
                            <div class="col-md-8">
                                <i class="bi bi-calendar3"></i>
                                {{ $order->created_at->format('d M Y, H:i') }}
                            </div>
                        </div>

                        @if($order->paid_at)
                            <div class="row mb-3 pb-3 border-bottom">
                                <div class="col-md-4">
                                    <strong>Payment Date:</strong>
                                </div>
                                <div class="col-md-8">
                                    <i class="bi bi-check-circle text-success"></i>
                                    {{ $order->paid_at->format('d M Y, H:i') }}
                                </div>
                            </div>
                        @endif

                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-md-4">
                                <strong>Payment Method:</strong>
                            </div>
                            <div class="col-md-8">
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-{{ $paymentMethod['icon'] }}"></i> {{ $paymentMethod['label'] }}
                                </span>
                            </div>
                        </div>

                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-md-4">
                                <strong>Amount Charged:</strong>
                            </div>
                            <div class="col-md-8">
                                <h5 class="text-success mb-1">
                                    {{ format_ringgit($amount) }}
                                    <span class="badge bg-success">MYR</span>
                                </h5>

                                @if($order->display_currency && $order->display_currency !== 'MYR' && $order->display_amount)
                                    <small class="text-muted d-block">
                                        Display Amount:
                                        {{ $order->display_currency }}
                                        {{ in_array($order->display_currency, ['IDR', 'JPY'], true)
                                            ? number_format($order->display_amount, 0)
                                            : number_format($order->display_amount, 2) }}
                                        <br>
                                        <span style="font-size: 0.75rem;">Reference only - charged in MYR.</span>
                                    </small>
                                @endif
                            </div>
                        </div>

                        @if($order->redeem_code)
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="alert alert-success">
                                        <h6 class="mb-3">
                                            <i class="bi bi-ticket-perforated"></i> Redemption Code
                                        </h6>
                                        <div class="bg-white p-3 rounded text-center">
                                            <h2 class="font-monospace text-success mb-2">{{ $order->redeem_code }}</h2>
                                            <button class="btn btn-sm btn-outline-success" onclick="copyRedeemCode()">
                                                <i class="bi bi-clipboard"></i> Copy Code
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="bi bi-info-circle"></i>
                                            Present this code to service providers.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-diagram-3"></i> Order Timeline
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="order-status-timeline">
                            @foreach($timelineEvents as $event)
                                <div class="timeline-item {{ $event['completed'] ? 'is-completed' : '' }} {{ $event['active'] ? 'is-active' : '' }}">
                                    <div class="timeline-marker">
                                        <i class="bi bi-{{ $event['icon'] }}"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-2">
                                            <div>
                                                <h6>{{ $event['label'] }}</h6>
                                                <p>{{ $event['meta'] }}</p>
                                            </div>
                                            <div class="text-md-end">
                                                <span class="badge {{ $event['active'] ? 'bg-primary' : ($event['completed'] ? 'bg-success' : 'bg-light text-dark border') }}">
                                                    {{ $event['active'] ? 'Current' : ($event['completed'] ? 'Completed' : 'Not Reached') }}
                                                </span>
                                                @if($event['completed'])
                                                    <small class="text-muted d-block mt-1">
                                                        @if($event['date'])
                                                            {{ $event['date']->format('d M Y, H:i') }}
                                                        @elseif($event['key'] === 'refund_requested')
                                                            Time not recorded
                                                        @else
                                                            Status reached
                                                        @endif
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul"></i> Package Details
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($order->items as $item)
                            <div class="border rounded p-3 mb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">{{ $item->nama_paket }}</h6>
                                    <span class="badge bg-primary">{{ $item->durasi_hari }} Days</span>
                                </div>

                                <div class="row text-muted small mb-2">
                                    <div class="col-md-6">
                                        <i class="bi bi-people"></i>
                                        {{ $item->jumlah_peserta }} participants
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <i class="bi bi-calendar-event"></i>
                                        {{ \Carbon\Carbon::parse($item->tanggal_keberangkatan)->format('d M Y') }}
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-6">
                                        <small class="text-muted">Package Price:</small><br>
                                        <strong>{{ format_ringgit((float) $item->harga_satuan) }}</strong>
                                    </div>
                                    <div class="col-6 text-end">
                                        <small class="text-muted">Subtotal:</small><br>
                                        <strong class="text-primary">{{ format_ringgit((float) $item->subtotal) }}</strong>
                                    </div>
                                </div>

                                @if($item->catatan)
                                    <div class="mt-2 pt-2 border-top">
                                        <small class="text-muted">
                                            <i class="bi bi-chat-left-text"></i>
                                            <strong>Notes:</strong> {{ $item->catatan }}
                                        </small>
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        <div class="border-top pt-3 mt-3">
                            <div class="row">
                                <div class="col-6">
                                    <h5 class="mb-0">Total:</h5>
                                </div>
                                <div class="col-6 text-end">
                                    <h4 class="text-success mb-0">{{ format_ringgit($amount) }}</h4>
                                    @if($order->display_currency && $order->display_currency !== 'MYR' && $order->display_amount)
                                        <small class="text-muted">
                                            Approx {{ $order->display_currency }}
                                            {{ number_format($order->display_amount, $order->display_currency === 'IDR' ? 0 : 2) }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-person"></i> Customer Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong class="d-block text-muted small">Full Name</strong>
                            <span>{{ $order->customer_name }}</span>
                        </div>

                        <div class="mb-3">
                            <strong class="d-block text-muted small">Email</strong>
                            <span>{{ $order->customer_email }}</span>
                        </div>

                        <div class="mb-3">
                            <strong class="d-block text-muted small">Phone</strong>
                            <span>{{ $order->customer_phone }}</span>
                        </div>

                        @if($order->customer_address)
                            <div class="mb-0">
                                <strong class="d-block text-muted small">Address</strong>
                                <span>{{ $order->customer_address }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                @if($order->display_currency && $order->display_currency !== 'MYR')
                    <div class="card border-0 shadow-sm mb-4 bg-light">
                        <div class="card-body">
                            <h6 class="mb-3">
                                <i class="bi bi-info-circle"></i> Currency Information
                            </h6>

                            <div class="mb-2">
                                <small class="text-muted d-block">Display Currency</small>
                                <strong>{{ $order->display_currency }}</strong>
                            </div>

                            <div class="mb-2">
                                <small class="text-muted d-block">Exchange Rate</small>
                                <strong>1 MYR = {{ number_format((float) $order->display_exchange_rate, 4) }} {{ $order->display_currency }}</strong>
                            </div>

                            <hr>

                            <small class="text-muted">
                                <i class="bi bi-shield-check"></i>
                                All payments are processed in MYR.
                            </small>
                        </div>
                    </div>
                @endif

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-gear"></i> Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if(in_array($order->status, $invoiceEligibleStatuses, true))
                                <a href="{{ route('orders.invoice', $order->id_order) }}"
                                   class="btn btn-primary"
                                   target="_blank">
                                    <i class="bi bi-download"></i> Download Invoice
                                </a>
                            @endif

                            @if($order->status === 'paid')
                                <button type="button"
                                        class="btn btn-outline-warning w-100"
                                        data-bs-toggle="modal"
                                        data-bs-target="#refundModal">
                                    <i class="bi bi-arrow-return-left"></i> Request Refund
                                </button>

                                <div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form action="{{ route('orders.refund.request', $order->id_order) }}" method="POST">
                                            @csrf
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Request Refund for Order #{{ $order->id_order }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3 text-start">
                                                        <label class="form-label">Reason for Refund</label>
                                                        <textarea class="form-control" name="reason" rows="3" required placeholder="Please explain why you want to request a refund..."></textarea>
                                                    </div>

                                                    <div class="alert alert-warning small text-start mb-3">
                                                        <div class="d-flex justify-content-between">
                                                            <span>Total Payment</span>
                                                            <strong>{{ format_ringgit($amount) }}</strong>
                                                        </div>
                                                        <div class="d-flex justify-content-between text-danger">
                                                            <span>Refund Fee (10%)</span>
                                                            <strong>- {{ format_ringgit($amount * 0.10) }}</strong>
                                                        </div>
                                                        <hr class="my-2">
                                                        <div class="d-flex justify-content-between">
                                                            <span>Estimated Refund</span>
                                                            <strong class="text-success">{{ format_ringgit($amount * 0.90) }}</strong>
                                                        </div>
                                                    </div>

                                                    <div class="form-check text-start">
                                                        <input class="form-check-input" type="checkbox" name="confirm_refund_fee" value="1" id="confirmRefundFeeDetail" required>
                                                        <label class="form-check-label small" for="confirmRefundFeeDetail">
                                                            I understand and agree that a 10% refund fee will be deducted from the ticket price.
                                                        </label>
                                                    </div>

                                                    <div class="alert alert-info small text-start mt-3 mb-0">
                                                        <i class="bi bi-info-circle"></i>
                                                        Refund requests require admin approval and are usually processed within 3-5 business days.
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-warning">Submit Request</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif

                            @if($order->status === 'pending')
                                <button type="button" class="btn btn-warning" onclick="location.reload()">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh Status
                                </button>

                                <form action="{{ route('orders.cancel', $order->id_order) }}"
                                      method="POST"
                                      onsubmit="return confirm('Are you sure you want to cancel this order?')">
                                    @csrf
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="bi bi-x-circle"></i> Cancel Order
                                    </button>
                                </form>
                            @endif

                            @if($order->status === 'refund_requested')
                                <div class="alert alert-warning mt-3 mb-0">
                                    <h6 class="alert-heading"><i class="bi bi-hourglass-split"></i> Refund Requested</h6>
                                    <p class="mb-0">Your refund request is currently being reviewed by the admin.</p>
                                    @if($order->refund_reason)
                                        <hr>
                                        <small><strong>Reason:</strong> {{ $order->refund_reason }}</small>
                                    @endif
                                </div>
                            @endif

                            @if($order->status === 'refunded')
                                <div class="alert alert-secondary mt-3 mb-0">
                                    <h6 class="alert-heading"><i class="bi bi-check-circle"></i> Order Refunded</h6>
                                    <p class="mb-2">This order has been refunded.</p>
                                    <small class="d-block"><strong>Refunded Amount:</strong> {{ format_ringgit((float) ($order->refund_amount ?? 0)) }}</small>
                                    @if(!empty($order->refund_fee))
                                        <small class="d-block"><strong>Refund Fee:</strong> {{ format_ringgit((float) $order->refund_fee) }}</small>
                                    @endif
                                    @if($order->refund_reason)
                                        <small class="d-block"><strong>Reason:</strong> {{ $order->refund_reason }}</small>
                                    @endif
                                    @if(!empty($order->refunded_at))
                                        <small class="d-block"><strong>Processed At:</strong> {{ \Carbon\Carbon::parse($order->refunded_at)->format('d M Y, H:i') }}</small>
                                    @endif
                                </div>
                            @endif

                            @if($order->refund_rejected_reason && $order->status === 'paid')
                                <div class="alert alert-danger mt-3 mb-0">
                                    <h6 class="alert-heading"><i class="bi bi-x-circle"></i> Refund Request Rejected</h6>
                                    <small><strong>Reason:</strong> {{ $order->refund_rejected_reason }}</small>
                                </div>
                            @endif

                            @if($order->refund_failure_reason)
                                <div class="alert alert-danger mt-3 mb-0">
                                    <h6 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Refund Processing Error</h6>
                                    <small>{{ $order->refund_failure_reason }}</small>
                                </div>
                            @endif

                            @if($order->status === 'failed')
                                <a href="{{ route('checkout.index') }}" class="btn btn-primary">
                                    <i class="bi bi-arrow-clockwise"></i> Try Again
                                </a>
                            @endif

                            <a href="{{ route('orders.history') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-list-ul"></i> Order History
                            </a>

                            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-house"></i> Back to Home
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-4 bg-light">
                    <div class="card-body">
                        <h6 class="mb-2">
                            <i class="bi bi-question-circle"></i> Need Help?
                        </h6>
                        <p class="small text-muted mb-2">
                            If you have any questions about your order, please contact our customer service.
                        </p>
                        <small class="text-muted">
                            <i class="bi bi-envelope"></i> support@telagaair.com<br>
                            <i class="bi bi-whatsapp"></i> +60 12-345 6789
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>







