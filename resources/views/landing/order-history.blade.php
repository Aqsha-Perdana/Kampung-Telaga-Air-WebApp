@extends('landing.layout')

@section('content')
@php
    $statusMeta = [
        'paid' => ['label' => 'Completed', 'class' => 'success', 'icon' => 'check-circle'],
        'pending' => ['label' => 'Pending', 'class' => 'warning text-dark', 'icon' => 'clock'],
        'failed' => ['label' => 'Failed', 'class' => 'danger', 'icon' => 'x-circle'],
        'cancelled' => ['label' => 'Cancelled', 'class' => 'secondary', 'icon' => 'slash-circle'],
        'refund_requested' => ['label' => 'Refund Requested', 'class' => 'warning text-dark', 'icon' => 'arrow-return-left'],
        'refunded' => ['label' => 'Refunded', 'class' => 'dark', 'icon' => 'arrow-counterclockwise'],
    ];

    $filterTabs = [
        ['key' => null, 'label' => 'All Orders', 'icon' => 'grid'],
        ['key' => 'paid', 'label' => 'Completed', 'icon' => 'check-circle'],
        ['key' => 'pending', 'label' => 'Pending', 'icon' => 'clock'],
        ['key' => 'refund_requested', 'label' => 'Refund Requested', 'icon' => 'arrow-return-left'],
        ['key' => 'refunded', 'label' => 'Refunded', 'icon' => 'arrow-counterclockwise'],
        ['key' => 'failed', 'label' => 'Failed / Cancelled', 'icon' => 'x-circle'],
    ];

    $invoiceEligibleStatuses = ['paid', 'refund_requested', 'refunded'];
@endphp
<style>
.order-history-shell {
    background: linear-gradient(180deg, #fbfdff 0%, #ffffff 100%);
}

.order-history-heading h2 {
    font-weight: 700;
    color: #0f172a;
}

.order-history-kicker {
    display: inline-block;
    font-size: 0.76rem;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    color: #2563eb;
    font-weight: 700;
}

.order-history-stat,
.order-history-filter,
.order-history-card,
.order-history-empty {
    border-radius: 22px;
}

.order-history-stat .card-body {
    padding: 1.35rem 1rem;
}

.order-history-card .card-body {
    padding: 1.5rem;
}

.order-history-filter .nav-link {
    border-radius: 999px;
    color: #475569;
}

.order-history-filter .nav-link.active {
    background: #2563eb;
    color: #fff;
}

.order-history-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    color: #64748b;
}

.order-history-meta span {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.9rem;
}

.order-history-amount-note {
    color: #64748b;
}

@media (max-width: 767.98px) {
    .order-history-meta {
        flex-direction: column;
        gap: 0.45rem;
    }
}
</style>

<section class="py-5 order-history-shell" style="margin-top: 80px;">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <p class="order-history-kicker mb-2">Your bookings</p>
                <h2 class="fw-bold mb-1">
                    <i class="bi bi-clock-history me-1"></i> Order History
                </h2>
                <p class="text-muted mb-0">View all your orders and transaction details.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm text-center h-100 order-history-stat">
                    <div class="card-body">
                        <h3 class="fw-bold text-primary mb-1">{{ $stats['total'] }}</h3>
                        <small class="text-muted">Total Orders</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm text-center h-100 order-history-stat">
                    <div class="card-body">
                        <h3 class="fw-bold text-success mb-1">{{ $stats['paid'] }}</h3>
                        <small class="text-muted">Completed</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm text-center h-100 order-history-stat">
                    <div class="card-body">
                        <h3 class="fw-bold text-warning mb-1">{{ $stats['pending'] }}</h3>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm text-center h-100 order-history-stat">
                    <div class="card-body">
                        <h3 class="fw-bold text-warning mb-1">{{ $stats['refund_requested'] }}</h3>
                        <small class="text-muted">Refund Review</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm text-center h-100 order-history-stat">
                    <div class="card-body">
                        <h3 class="fw-bold text-dark mb-1">{{ $stats['refunded'] }}</h3>
                        <small class="text-muted">Refunded</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm text-center h-100 order-history-stat">
                    <div class="card-body">
                        <h3 class="fw-bold text-danger mb-1">{{ $stats['failed'] }}</h3>
                        <small class="text-muted">Failed / Cancelled</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4 order-history-filter">
            <div class="card-body py-2">
                <ul class="nav nav-pills gap-2">
                    @foreach($filterTabs as $tab)
                        @php
                            $isActive = $tab['key'] === null
                                ? !request('status')
                                : request('status') === $tab['key'];
                        @endphp
                        <li class="nav-item">
                            <a class="nav-link {{ $isActive ? 'active' : '' }}"
                               href="{{ $tab['key'] ? route('orders.history', ['status' => $tab['key']]) : route('orders.history') }}">
                                <i class="bi bi-{{ $tab['icon'] }}"></i> {{ $tab['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                @if($orders->isEmpty())
                    <div class="card border-0 shadow-sm order-history-empty">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <h5 class="mt-3">No Orders Yet</h5>
                            <p class="text-muted mb-3">Start exploring our travel packages and make your first booking.</p>
                            <a href="{{ route('landing.paket-wisata') }}" class="btn btn-primary">
                                <i class="bi bi-search"></i> Browse Packages
                            </a>
                        </div>
                    </div>
                @else
                    @foreach($orders as $order)
                        @php
                            $meta = $statusMeta[$order->status] ?? ['label' => ucfirst(str_replace('_', ' ', $order->status)), 'class' => 'secondary', 'icon' => 'info-circle'];
                            $amount = (float) ($order->base_amount ?? $order->total_amount ?? 0);
                        @endphp

                        <div class="card border-0 shadow-sm mb-3 order-history-card">
                            <div class="card-body">
                                <div class="row align-items-start g-3">
                                    <div class="col-lg-8">
                                        <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                                            <h5 class="mb-0 fw-semibold">{{ $order->id_order }}</h5>
                                            <span class="badge bg-{{ $meta['class'] }}">
                                                <i class="bi bi-{{ $meta['icon'] }}"></i> {{ $meta['label'] }}
                                            </span>
                                        </div>

                                        <div class="order-history-meta small mb-3">
                                            <span>
                                                <i class="bi bi-calendar-event"></i>
                                                {{ $order->created_at->format('d M Y, H:i') }}
                                            </span>
                                            <span>
                                                <i class="bi bi-wallet2"></i>
                                                {{ payment_method_label($order->payment_method) }}
                                            </span>
                                        </div>

                                        @foreach($order->items as $item)
                                            <div class="small mb-2">
                                                <i class="bi bi-box-seam"></i>
                                                <strong>{{ $item->nama_paket }}</strong>
                                                <span class="text-muted d-inline-block ms-1">{{ $item->jumlah_peserta }} participants</span>
                                                <span class="text-muted d-inline-block ms-2">
                                                    <i class="bi bi-calendar3"></i>
                                                    {{ \Carbon\Carbon::parse($item->tanggal_keberangkatan)->format('d M Y') }}
                                                </span>
                                            </div>
                                        @endforeach

                                        @if($order->redeem_code)
                                            <div class="alert alert-success py-2 mt-3 mb-0">
                                                <i class="bi bi-ticket-perforated"></i>
                                                <strong>Redeem Code:</strong>
                                                <span class="font-monospace">{{ $order->redeem_code }}</span>
                                            </div>
                                        @endif

                                        @if($order->status === 'refund_requested')
                                            <div class="alert alert-warning py-2 mt-3 mb-0">
                                                <strong>Refund request is under review.</strong>
                                                @if($order->refund_reason)
                                                    <div class="small mt-1">Reason: {{ $order->refund_reason }}</div>
                                                @endif
                                            </div>
                                        @endif

                                        @if($order->status === 'refunded')
                                            <div class="alert alert-secondary py-2 mt-3 mb-0">
                                                <strong>Refund completed.</strong>
                                                <div class="small mt-1">
                                                    Amount returned: {{ format_ringgit((float) ($order->refund_amount ?? 0)) }}
                                                    @if(!empty($order->refunded_at))
                                                        <span class="d-inline-block ms-2">on {{ \Carbon\Carbon::parse($order->refunded_at)->format('d M Y, H:i') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        @if($order->refund_rejected_reason && $order->status === 'paid')
                                            <div class="alert alert-danger py-2 mt-3 mb-0">
                                                <strong>Previous refund request was rejected.</strong>
                                                <div class="small mt-1">{{ $order->refund_rejected_reason }}</div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col-lg-4 text-lg-end">
                                        <h4 class="fw-bold text-primary mb-1">{{ format_ringgit($amount) }}</h4>

                                        @if($order->display_currency && $order->display_currency !== 'MYR' && $order->display_amount)
                                            <div class="small order-history-amount-note mb-3">
                                                Display: {{ $order->display_currency }}
                                                {{ in_array($order->display_currency, ['IDR', 'JPY'], true)
                                                    ? number_format($order->display_amount, 0)
                                                    : number_format($order->display_amount, 2) }}
                                            </div>
                                        @else
                                            <div class="small order-history-amount-note mb-3">Charged in MYR</div>
                                        @endif

                                        <div class="d-grid gap-2">
                                            <a href="{{ route('orders.show', $order->id_order) }}"
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-eye"></i> View Details
                                            </a>

                                            @if(in_array($order->status, $invoiceEligibleStatuses, true))
                                                <a href="{{ route('orders.invoice', $order->id_order) }}"
                                                   class="btn btn-outline-secondary btn-sm">
                                                    <i class="bi bi-download"></i> Download Invoice
                                                </a>
                                            @endif

                                            @if($order->status === 'pending')
                                                <form action="{{ route('orders.cancel', $order->id_order) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Are you sure you want to cancel this order?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                                        <i class="bi bi-x-circle"></i> Cancel Order
                                                    </button>
                                                </form>
                                            @elseif($order->status === 'paid')
                                                <button type="button"
                                                        class="btn btn-outline-warning btn-sm w-100"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#refundModal-{{ $order->id_order }}">
                                                    <i class="bi bi-arrow-return-left"></i> Request Refund
                                                </button>

                                                <div class="modal fade" id="refundModal-{{ $order->id_order }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <form action="{{ route('orders.refund.request', $order->id_order) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Request Refund for #{{ $order->id_order }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Reason for Refund</label>
                                                                        <textarea class="form-control" name="reason" rows="3" required placeholder="Please explain why you want to request a refund..."></textarea>
                                                                    </div>

                                                                    <div class="alert alert-warning small mb-3">
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

                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" name="confirm_refund_fee" value="1" id="confirmRefundFee-{{ $order->id_order }}" required>
                                                                        <label class="form-check-label small" for="confirmRefundFee-{{ $order->id_order }}">
                                                                            I understand and agree that a 10% refund fee will be deducted from the ticket price.
                                                                        </label>
                                                                    </div>

                                                                    <div class="alert alert-info small mt-3 mb-0">
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
                                            @elseif($order->status === 'refund_requested')
                                                <button class="btn btn-warning btn-sm w-100" disabled>
                                                    <i class="bi bi-hourglass-split"></i> Refund Requested
                                                </button>
                                            @elseif($order->status === 'refunded')
                                                <button class="btn btn-dark btn-sm w-100" disabled>
                                                    <i class="bi bi-check-circle"></i> Refunded
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="d-flex justify-content-center mt-4">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
