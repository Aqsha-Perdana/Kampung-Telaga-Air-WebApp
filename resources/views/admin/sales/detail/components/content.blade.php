@php
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
        ($order->refund_status ?? null) === 'rejected' => 'Customer submitted a refund request and it was rejected.',
        $order->status === 'refund_requested' => 'Customer submitted a refund request and it is awaiting review.',
        $order->status === 'refunded' || ($order->refund_status ?? null) === 'succeeded' => 'Customer refund request was approved.',
        $hasRefundRequest => 'Customer submitted a refund request.',
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
            'icon' => 'clock-hour-4',
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
            'icon' => 'arrow-back-up',
        ],
        [
            'key' => 'refunded',
            'label' => 'Refunded',
            'meta' => $order->status === 'refunded' || ($order->refund_status ?? null) === 'succeeded'
                ? 'Funds have been returned to the customer.'
                : 'Refund has not been completed.',
            'date' => !empty($order->refunded_at) ? \Carbon\Carbon::parse($order->refunded_at) : null,
            'completed' => $order->status === 'refunded' || ($order->refund_status ?? null) === 'succeeded',
            'active' => $currentTimelineKey === 'refunded',
            'icon' => 'cash-banknote',
        ],
        [
            'key' => 'cancelled',
            'label' => 'Cancelled',
            'meta' => $order->status === 'cancelled'
                ? 'Order was closed before completion.'
                : 'Order remains active.',
            'date' => $order->status === 'cancelled' && !empty($order->updated_at) ? \Carbon\Carbon::parse($order->updated_at) : null,
            'completed' => $order->status === 'cancelled',
            'active' => $currentTimelineKey === 'cancelled',
            'icon' => 'ban',
        ],
    ];
@endphp
<div class="container-fluid">
    <!-- Breadcrumb & Back Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Sales</a></li>
                    <li class="breadcrumb-item active">Order Detail</li>
                </ol>
            </nav>
            <h2 class="mt-2 mb-0">Order {{ $order->id_order }}</h2>
        </div>
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left"></i> Back to Sales
        </a>
    </div>


    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ti ti-check me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="ti ti-alert-circle me-1"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Order Info Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0 text-white"><i class="ti ti-info-circle"></i> Order Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 40%;">Order ID:</td>
                                    <td>{{ $order->id_order }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Customer Name:</td>
                                    <td>{{ $order->customer_name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Email:</td>
                                    <td>{{ $order->customer_email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Phone:</td>
                                    <td>{{ $order->customer_phone ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 40%;">Order Date:</td>
                                    <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y, H:i') }}</td>
                                </tr>
                                @if($order->paid_at)
                                <tr>
                                    <td class="fw-bold">Paid Date:</td>
                                    <td>{{ \Carbon\Carbon::parse($order->paid_at)->format('d M Y, H:i') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="fw-bold">Status:</td>
                                    <td>
                                        @php
                                            $statusClass = [
                                                'pending' => 'warning',
                                                'paid' => 'success',
                                                'failed' => 'danger',
                                                'cancelled' => 'secondary',
                                                'refund_requested' => 'warning',
                                                'refunded' => 'dark',
                                            ][$order->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }} px-3 py-2">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Payment Method:</td>
                                    <td>
                                        @if($order->payment_method === 'stripe')
                                            <i class="ti ti-credit-card"></i> Credit/Debit Card (Stripe)
                                        @else
                                            {{ strtoupper(str_replace('_', ' ', (string) $order->payment_method)) }}
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    @if($order->customer_address)
                    <div class="mt-3">
                        <strong>Address:</strong>
                        <p class="mb-0 text-muted">{{ $order->customer_address }}</p>
                    </div>
                    @endif

                    {{-- UPDATED: Display Currency Info --}}
                    @if($order->display_currency && $order->display_currency !== 'MYR')
                    <div class="alert alert-light mt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="ti ti-eye"></i> Customer Display:</strong>
                                <p class="mb-0">
                                    @php
                                        $currencySymbols = [
                                            'MYR' => 'RM',
                                            'USD' => '$',
                                            'SGD' => 'S$',
                                            'IDR' => 'Rp',
                                            'EUR' => '&euro;',
                                            'GBP' => '&pound;',
                                            'AUD' => 'A$',
                                            'JPY' => '&yen;',
                                            'CNY' => '&yen;',
                                            'BND' => 'B$',
                                        ];
                                        $symbol = $currencySymbols[$order->display_currency] ?? $order->display_currency . ' ';
                                    @endphp
                                    {{ $symbol }}{{ number_format($order->display_amount, $order->display_currency === 'IDR' ? 0 : 2) }} {{ $order->display_currency }}
                                    <br>
                                    <small class="text-muted">
                                        Exchange Rate: 1 MYR = {{ number_format($order->display_exchange_rate, 4) }} {{ $order->display_currency }}
                                    </small>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="ti ti-cash"></i> Actual Payment (MYR):</strong>
                                <p class="mb-0">
                                    <span class="text-success h5">RM {{ number_format($order->base_amount, 2) }}</span>
                                    <br>
                                    <small class="text-muted">All payments processed in MYR</small>
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($order->redeem_code)
                    <div class="alert alert-success mt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><i class="ti ti-ticket"></i> Redeem Code:</strong>
                                <h4 class="mb-0 mt-1 font-monospace">{{ $order->redeem_code }}</h4>
                            </div>
                            <button class="btn btn-sm btn-outline-success" onclick="copyRedeemCode()">
                                <i class="ti ti-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="ti ti-git-branch"></i> Order Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="admin-order-timeline">
                        @foreach($timelineEvents as $event)
                        <div class="timeline-item {{ $event['completed'] ? 'is-completed' : '' }} {{ $event['active'] ? 'is-active' : '' }}">
                            <div class="timeline-marker">
                                <i class="ti ti-{{ $event['icon'] }}"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-2">
                                    <div>
                                        <h6 class="mb-1">{{ $event['label'] }}</h6>
                                        <p class="mb-0 text-muted">{{ $event['meta'] }}</p>
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

            <!-- Package Items -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0 text-white"><i class="ti ti-package"></i> Package Details ({{ $itemsWithBreakdown->count() }} items)</h5>
                </div>
                <div class="card-body">
                    @foreach($itemsWithBreakdown as $index => $item)
                    <div class="package-item {{ $index > 0 ? 'mt-4 pt-4 border-top' : '' }}">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="mb-1">{{ $item->nama_paket }}</h5>
                                <div class="text-muted">
                                    <small>
                                        <i class="ti ti-calendar"></i> {{ $item->durasi_hari ?? $item->paket_durasi }} days
                                        @if($item->jumlah_peserta)
                                        | <i class="ti ti-users"></i> {{ $item->jumlah_peserta }} participants
                                        @endif
                                    </small>
                                </div>
                                @if($item->tanggal_keberangkatan)
                                <div class="text-muted">
                                    <small>
                                        <i class="ti ti-calendar-event"></i> Departure: {{ \Carbon\Carbon::parse($item->tanggal_keberangkatan)->format('d M Y') }}
                                    </small>
                                </div>
                                @endif
                            </div>
                            <div class="text-end">
                                <h5 class="text-primary mb-0">{{ format_ringgit($item->harga_satuan) }}</h5>
                                @if($item->subtotal)
                                <small class="text-muted">Subtotal: {{ format_ringgit($item->subtotal) }}</small>
                                @endif
                            </div>
                        </div>

                        @if($item->catatan)
                        <div class="alert alert-light mb-3">
                            <strong><i class="ti ti-note"></i> Notes:</strong> {{ $item->catatan }}
                        </div>
                        @endif

                        <!-- Vendor cost breakdown for this item -->
                        <div class="breakdown-section">
                            <h6 class="mb-3"><i class="ti ti-chart-pie"></i> Vendor Cost Breakdown (MYR):</h6>
                            
                            <div class="row g-3">
                                <!-- Boats -->
                                @if(count($item->breakdown['boat_items']) > 0)
                                <div class="col-md-6">
                                    <div class="card border border-primary">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="p-2 bg-light-primary rounded me-2">
                                                    <i class="ti ti-ship text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">Boats</h6>
                                                    <small class="text-muted">{{ format_ringgit($item->breakdown['boat_total']) }}</small>
                                                </div>
                                            </div>
                                            <ul class="list-unstyled mb-0">
                                                @foreach($item->breakdown['boat_items'] as $boat)
                                                <li class="small mb-1">
                                                    &bull; {{ $boat['nama'] }} <span class="badge bg-light text-dark">Day {{ $boat['hari_ke'] }}</span>
                                                    <br>
                                                    <span class="text-muted ms-2">{{ format_ringgit($boat['revenue']) }}</span>
                                                </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Homestays -->
                                @if(count($item->breakdown['homestay_items']) > 0)
                                <div class="col-md-6">
                                    <div class="card border border-success">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="p-2 bg-light-success rounded me-2">
                                                    <i class="ti ti-home text-success"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">Homestays</h6>
                                                    <small class="text-muted">{{ format_ringgit($item->breakdown['homestay_total']) }}</small>
                                                </div>
                                            </div>
                                            <ul class="list-unstyled mb-0">
                                                @foreach($item->breakdown['homestay_items'] as $homestay)
                                                <li class="small mb-1">
                                                    &bull; {{ $homestay['nama'] }} <span class="badge bg-light text-dark">{{ $homestay['jumlah_malam'] }} nights</span>
                                                    <br>
                                                    <span class="text-muted ms-2">{{ format_ringgit($homestay['revenue']) }}</span>
                                                </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Culinaries -->
                                @if(count($item->breakdown['culinary_items']) > 0)
                                <div class="col-md-6">
                                    <div class="card border border-warning">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="p-2 bg-light-warning rounded me-2">
                                                    <i class="ti ti-tools-kitchen text-warning"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">Culinaries</h6>
                                                    <small class="text-muted">{{ format_ringgit($item->breakdown['culinary_total']) }}</small>
                                                </div>
                                            </div>
                                            <ul class="list-unstyled mb-0">
                                                @foreach($item->breakdown['culinary_items'] as $culinary)
                                                <li class="small mb-1">
                                                    &bull; {{ $culinary['nama'] }} <span class="badge bg-light text-dark">Day {{ $culinary['hari_ke'] }}</span>
                                                    <br>
                                                    <span class="text-muted ms-2">{{ format_ringgit($culinary['revenue']) }}</span>
                                                </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Kiosks -->
                                @if(count($item->breakdown['kiosk_items']) > 0)
                                <div class="col-md-6">
                                    <div class="card border border-info">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="p-2 bg-light-info rounded me-2">
                                                    <i class="ti ti-building-store text-info"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">Kiosks</h6>
                                                    <small class="text-muted">{{ format_ringgit($item->breakdown['kiosk_total']) }}</small>
                                                </div>
                                            </div>
                                            <ul class="list-unstyled mb-0">
                                                @foreach($item->breakdown['kiosk_items'] as $kiosk)
                                                <li class="small mb-1">
                                                    &bull; {{ $kiosk['nama'] }} <span class="badge bg-light text-dark">Day {{ $kiosk['hari_ke'] }}</span>
                                                    <br>
                                                    <span class="text-muted ms-2">{{ format_ringgit($kiosk['revenue']) }}</span>
                                                </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Payment History -->
            @if($paymentLogs->isNotEmpty())
            <div class="card mb-4">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0 text-white"><i class="ti ti-credit-card"></i> Payment History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount (MYR)</th>
                                    <th>Currency</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Intent ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($paymentLogs as $log)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y, H:i') }}</td>
                                    <td>{{ format_ringgit($log->amount) }}</td>
                                    <td><span class="badge bg-light text-dark">{{ strtoupper($log->currency ?? 'MYR') }}</span></td>
                                    <td>
                                        @if(($log->payment_method ?? null) === 'stripe')
                                            Credit/Debit Card (Stripe)
                                        @else
                                            {{ strtoupper(str_replace('_', ' ', (string) ($log->payment_method ?? '-'))) }}
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $log->status == 'success' ? 'success' : ($log->status == 'failed' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($log->status) }}
                                        </span>
                                    </td>
                                    <td><small class="font-monospace">{{ $log->payment_intent_id ?? '-' }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column - Summary -->
        <div class="col-lg-4">
            @if(in_array($order->status, ['refund_requested', 'refunded'], true) || !empty($order->refund_rejected_reason) || !empty($order->refund_failure_reason))
            <div class="card mb-4 shadow-sm refund-control-card">
                <div class="card-header border-0">
                    <h5 class="mb-0"><i class="ti ti-receipt-refund me-1"></i> Refund Management</h5>
                </div>
                <div class="card-body">
                    @php
                        $refundState = $order->refund_status ?? null;
                        $refundBadge = [
                            'requested' => 'warning',
                            'processing' => 'info',
                            'succeeded' => 'success',
                            'failed' => 'danger',
                            'rejected' => 'secondary',
                        ][$refundState] ?? 'light';
                    @endphp

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <small class="text-muted">Refund State</small>
                        <span class="badge bg-{{ $refundBadge }} text-{{ $refundBadge === 'light' ? 'dark' : 'white' }}">
                            {{ $refundState ? ucfirst($refundState) : 'Not Started' }}
                        </span>
                    </div>

                    @if(!empty($order->refund_reason))
                    <div class="refund-note mb-3">
                        <small class="text-muted d-block mb-1">Customer Reason</small>
                        <p class="mb-0">{{ $order->refund_reason }}</p>
                    </div>
                    @endif

                    <div class="refund-breakdown mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total Payment</span>
                            <strong>RM {{ number_format($order->base_amount, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between text-danger">
                            <span>Refund Fee (10%)</span>
                            <strong>- RM {{ number_format($order->base_amount * 0.10, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between fw-bold pt-1 border-top mt-1">
                            <span>Refund Amount</span>
                            <span class="text-success">RM {{ number_format($order->base_amount * 0.90, 2) }}</span>
                        </div>
                    </div>

                    @if($order->status === 'refund_requested')
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveRefundModal">
                            <i class="ti ti-check me-1"></i> Approve Refund
                        </button>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectRefundModal">
                            <i class="ti ti-x me-1"></i> Reject Request
                        </button>
                    </div>
                    @endif

                    @if(!empty($order->refund_rejected_reason))
                    <div class="alert alert-danger mt-3 mb-0 py-2">
                        <small class="fw-semibold d-block">Latest Rejection Reason</small>
                        <small>{{ $order->refund_rejected_reason }}</small>
                    </div>
                    @endif

                    @if(!empty($order->refund_failure_reason))
                    <div class="alert alert-danger mt-3 mb-0 py-2">
                        <small class="fw-semibold d-block">Refund Processing Error</small>
                        <small>{{ $order->refund_failure_reason }}</small>
                    </div>
                    @endif

                    @if($order->status === 'refunded')
                    <div class="alert alert-success mt-3 mb-0 py-2">
                        <small class="fw-semibold d-block">Refund Completed</small>
                        <small>
                            Amount: RM {{ number_format((float) ($order->refund_amount ?? ($order->base_amount * 0.90)), 2) }}
                            @if(!empty($order->refunded_at))
                            <br>Date: {{ \Carbon\Carbon::parse($order->refunded_at)->format('d M Y, H:i') }}
                            @endif
                            @if(!empty($order->stripe_refund_id))
                            <br>Stripe Refund ID: <span class="font-monospace">{{ $order->stripe_refund_id }}</span>
                            @endif
                        </small>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Approve Modal -->
            <div class="modal fade" id="approveRefundModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="{{ route('admin.sales.refund.approve', $order->id_order) }}" method="POST">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Approve Refund</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-3">This action will process a refund for this order.</p>
                                <div class="alert alert-info mb-0">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Total Payment</span>
                                        <strong>RM {{ number_format($order->base_amount, 2) }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between text-danger mb-1">
                                        <span>Refund Fee (10%)</span>
                                        <strong>- RM {{ number_format($order->base_amount * 0.10, 2) }}</strong>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Amount Returned</span>
                                        <span class="text-success">RM {{ number_format($order->base_amount * 0.90, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Confirm Approval</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reject Modal -->
            <div class="modal fade" id="rejectRefundModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="{{ route('admin.sales.refund.reject', $order->id_order) }}" method="POST">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Reject Refund Request</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-0">
                                    <label class="form-label">Rejection Reason</label>
                                    <textarea name="reason" class="form-control" rows="3" required placeholder="Explain why this request is rejected..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">Reject Request</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Total Summary -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0 text-white"><i class="ti ti-receipt"></i> Order Summary</h5>
                </div>
                <div class="card-body">
                    {{-- UPDATED: Show both display and payment amounts --}}
                    @if($order->display_currency && $order->display_currency !== 'MYR')
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted d-block mb-1">Customer Display:</small>
                        @php
                            $currencySymbols = [
                                'MYR' => 'RM',
                                'USD' => '$',
                                'SGD' => 'S$',
                                'IDR' => 'Rp',
                                'EUR' => '&euro;',
                                'GBP' => '&pound;',
                                'AUD' => 'A$',
                                'JPY' => '&yen;',
                                'CNY' => '&yen;',
                                'BND' => 'B$',
                            ];
                            $symbol = $currencySymbols[$order->display_currency] ?? $order->display_currency . ' ';
                        @endphp
                        <h5 class="mb-0">{{ $symbol }}{{ number_format($order->display_amount, $order->display_currency === 'IDR' ? 0 : 2) }}</h5>
                        <small class="text-muted">{{ $order->display_currency }}</small>
                    </div>
                    @endif

                    <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                        <div>
                            <small class="text-muted d-block mb-1">
                                @if($order->display_currency && $order->display_currency !== 'MYR')
                                    Actual Payment (MYR):
                                @else
                                    Total Amount:
                                @endif
                            </small>
                            <strong class="h4 text-success mb-0">{{ format_ringgit($order->base_amount ?? $order->total_amount) }}</strong>
                        </div>
                        <span class="badge bg-success align-self-start">MYR</span>
                    </div>

                    <h6 class="mb-3"><i class="ti ti-chart-donut"></i> Vendor Cost Distribution (MYR):</h6>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small><i class="ti ti-ship text-primary"></i> Boats</small>
                            <small class="fw-bold">{{ format_ringgit($totals['boat']) }}</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            @php
                                $totalAmount = $order->base_amount ?? $order->total_amount;
                                $boatPercent = $totalAmount > 0 ? ($totals['boat'] / $totalAmount * 100) : 0;
                            @endphp
                            <div class="progress-bar bg-primary" style="width: {{ $boatPercent }}%"></div>
                        </div>
                        <small class="text-muted">{{ number_format($boatPercent, 1) }}%</small>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small><i class="ti ti-home text-success"></i> Homestays</small>
                            <small class="fw-bold">{{ format_ringgit($totals['homestay']) }}</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            @php
                                $homestayPercent = $totalAmount > 0 ? ($totals['homestay'] / $totalAmount * 100) : 0;
                            @endphp
                            <div class="progress-bar bg-success" style="width: {{ $homestayPercent }}%"></div>
                        </div>
                        <small class="text-muted">{{ number_format($homestayPercent, 1) }}%</small>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small><i class="ti ti-tools-kitchen text-warning"></i> Culinaries</small>
                            <small class="fw-bold">{{ format_ringgit($totals['culinary']) }}</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            @php
                                $culinaryPercent = $totalAmount > 0 ? ($totals['culinary'] / $totalAmount * 100) : 0;
                            @endphp
                            <div class="progress-bar bg-warning" style="width: {{ $culinaryPercent }}%"></div>
                        </div>
                        <small class="text-muted">{{ number_format($culinaryPercent, 1) }}%</small>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small><i class="ti ti-building-store text-info"></i> Kiosks</small>
                            <small class="fw-bold">{{ format_ringgit($totals['kiosk']) }}</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            @php
                                $kioskPercent = $totalAmount > 0 ? ($totals['kiosk'] / $totalAmount * 100) : 0;
                            @endphp
                            <div class="progress-bar bg-info" style="width: {{ $kioskPercent }}%"></div>
                        </div>
                        <small class="text-muted">{{ number_format($kioskPercent, 1) }}%</small>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0 text-white"><i class="ti ti-currency-dollar"></i> Payment Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Paid by Guest:</span>
                        <span class="fw-bold">RM {{ number_format($order->base_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Vendor Cost Snapshot:</span>
                        <span class="text-danger">- RM {{ number_format($financialSummary['vendor_total'], 2) }}</span>
                    </div>
                    
                    <hr class="my-3">
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h6 mb-0">Original Booking Profit Snapshot:</span>
                        <h4 class="text-primary fw-bold mb-0">
                            RM {{ number_format($financialSummary['original_profit'], 2) }}
                        </h4>
                    </div>

                    @if($order->status === 'refunded')
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <span class="h6 mb-0">Current Reported Profit Impact:</span>
                        <h4 class="text-success fw-bold mb-0">
                            RM {{ number_format($financialSummary['reported_profit_impact'], 2) }}
                        </h4>
                    </div>
                    <small class="text-muted d-block mt-1">
                        Refunded orders are recognized in financial reports through the retained refund fee.
                    </small>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="ti ti-settings"></i> Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($order->status === 'paid')
                            <a href="{{ route('invoice.download', $order->id_order) }}" 
                            class="btn btn-outline-primary" 
                            target="_blank">
                                <i class="ti ti-printer"></i> Print Invoice
                            </a>

                            <a href="{{ route('admin.sales.manifest', $order->id_order) }}" 
                            class="btn btn-dark" 
                            target="_blank">
                                <i class="ti ti-clipboard-list"></i> Print Manifest
                            </a>
                        @endif
                        
                        @if($order->status == 'pending')
                        <button class="btn btn-success" onclick="confirmPayment('{{ $order->id_order }}')">
                            <i class="ti ti-check"></i> Confirm Payment
                        </button>
                        @endif
                        
                        @if($order->status != 'cancelled' && $order->status != 'paid')
                        <button class="btn btn-danger" onclick="cancelOrder('{{ $order->id_order }}')">
                            <i class="ti ti-x"></i> Cancel Order
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>








