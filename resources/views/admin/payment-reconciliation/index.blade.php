@extends('layout.sidebar')

@section('title', 'Payment Exceptions')

@section('content')
<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3 p-lg-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-3">
                    <div>
                        <h4 class="mb-1 fw-bold">Payment Exceptions</h4>
                        <p class="text-muted small mb-0">Monitor payments that still need fee reconciliation, are pending too long, or are waiting for refund handling.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('sales.index') }}" class="btn btn-light border btn-sm">
                            <i class="ti ti-arrow-left me-1"></i>Back to Sales
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success border-0 mb-3">
                        <i class="ti ti-circle-check me-1"></i>{{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger border-0 mb-3">
                        <i class="ti ti-alert-circle me-1"></i>{{ session('error') }}
                    </div>
                @endif

                <form method="GET" action="{{ route('payment-reconciliation.index') }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-sm-6 col-lg-2">
                            <label class="form-label small text-muted mb-1">Start date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $filters['start_date'] }}">
                        </div>
                        <div class="col-sm-6 col-lg-2">
                            <label class="form-label small text-muted mb-1">End date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $filters['end_date'] }}">
                        </div>
                        <div class="col-sm-6 col-lg-2">
                            <label class="form-label small text-muted mb-1">Issue type</label>
                            <select name="issue_type" class="form-select form-select-sm">
                                <option value="all" {{ $filters['issue_type'] === 'all' ? 'selected' : '' }}>All Issues</option>
                                <option value="estimated_fee" {{ $filters['issue_type'] === 'estimated_fee' ? 'selected' : '' }}>Estimated Fee</option>
                                <option value="pending_payment" {{ $filters['issue_type'] === 'pending_payment' ? 'selected' : '' }}>Pending &gt; 30 min</option>
                                <option value="refund_requested" {{ $filters['issue_type'] === 'refund_requested' ? 'selected' : '' }}>Refund Requested</option>
                            </select>
                        </div>
                        <div class="col-sm-6 col-lg-2">
                            <label class="form-label small text-muted mb-1">Payment gateway</label>
                            <select name="payment_method" class="form-select form-select-sm">
                                <option value="all" {{ $filters['payment_method'] === 'all' ? 'selected' : '' }}>All Gateways</option>
                                @foreach($availablePaymentMethods as $method)
                                    <option value="{{ $method }}" {{ $filters['payment_method'] === $method ? 'selected' : '' }}>
                                        {{ payment_method_label($method) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6 col-lg-2">
                            <label class="form-label small text-muted mb-1">Payment channel</label>
                            <select name="payment_channel" class="form-select form-select-sm">
                                <option value="all" {{ $filters['payment_channel'] === 'all' ? 'selected' : '' }}>All Channels</option>
                                @foreach($availablePaymentChannels as $channel)
                                    <option value="{{ $channel }}" {{ $filters['payment_channel'] === $channel ? 'selected' : '' }}>
                                        {{ payment_channel_label($channel) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6 col-lg-1">
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-1">
                            <div class="d-grid">
                                <a href="{{ route('payment-reconciliation.index') }}" class="btn btn-light border btn-sm">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="d-flex flex-wrap gap-2 mt-3">
                    <a href="{{ route('payment-reconciliation.index', array_merge(request()->except(['issue_type', 'reconciliation_page']), ['issue_type' => 'all'])) }}"
                       class="btn btn-sm {{ $filters['issue_type'] === 'all' ? 'btn-primary' : 'btn-light border' }}">
                        All Issues
                    </a>
                    <a href="{{ route('payment-reconciliation.index', array_merge(request()->except(['issue_type', 'reconciliation_page']), ['issue_type' => 'estimated_fee'])) }}"
                       class="btn btn-sm {{ $filters['issue_type'] === 'estimated_fee' ? 'btn-warning text-dark' : 'btn-light border' }}">
                        Estimated Fee
                    </a>
                    <a href="{{ route('payment-reconciliation.index', array_merge(request()->except(['issue_type', 'reconciliation_page']), ['issue_type' => 'pending_payment'])) }}"
                       class="btn btn-sm {{ $filters['issue_type'] === 'pending_payment' ? 'btn-info text-white' : 'btn-light border' }}">
                        Pending &gt; 30 min
                    </a>
                    <a href="{{ route('payment-reconciliation.index', array_merge(request()->except(['issue_type', 'reconciliation_page']), ['issue_type' => 'refund_requested'])) }}"
                       class="btn btn-sm {{ $filters['issue_type'] === 'refund_requested' ? 'btn-danger' : 'btn-light border' }}">
                        Refund Requested
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <p class="text-muted text-uppercase small fw-semibold mb-2">Total Exceptions</p>
                <h3 class="fw-bold mb-0">{{ $summary['total_exceptions'] }}</h3>
                <small class="text-muted">Orders that still need payment attention.</small>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <p class="text-muted text-uppercase small fw-semibold mb-2">Estimated Fee</p>
                <h3 class="fw-bold text-warning mb-0">{{ $summary['estimated_fee_count'] }}</h3>
                <small class="text-muted">Gateway fee still waiting for actual settlement data.</small>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <p class="text-muted text-uppercase small fw-semibold mb-2">Pending Payment</p>
                <h3 class="fw-bold text-info mb-0">{{ $summary['pending_payment_count'] }}</h3>
                <small class="text-muted">Pending more than 30 minutes.</small>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <p class="text-muted text-uppercase small fw-semibold mb-2">Refund Requested</p>
                <h3 class="fw-bold text-danger mb-0">{{ $summary['refund_requested_count'] }}</h3>
                <small class="text-muted">Waiting for admin decision or follow-up.</small>
            </div>
        </div>
    </div>

    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0 bg-light-subtle">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-4">
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Estimated Fee Exposure</div>
                        <div class="d-flex flex-wrap gap-4">
                            <div>
                                <div class="text-muted small">Refreshable Orders</div>
                                <div class="fw-bold fs-5">{{ $summary['refreshable_count'] }}</div>
                            </div>
                            <div>
                                <div class="text-muted small">Fee Recorded</div>
                                <div class="fw-bold fs-5 text-danger">{{ format_ringgit($summary['estimated_fee_amount']) }}</div>
                            </div>
                            <div>
                                <div class="text-muted small">Gross Covered</div>
                                <div class="fw-bold fs-5">{{ format_ringgit($summary['estimated_gross_amount']) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="small text-muted text-uppercase fw-semibold mb-2">Issue Mix by Gateway</div>
                        <div class="d-flex flex-wrap gap-2">
                            @forelse($summary['gateway_mix'] as $gateway)
                                <span class="badge bg-white text-dark border px-3 py-2">
                                    {{ $gateway->label }}: {{ $gateway->total }}
                                </span>
                            @empty
                                <span class="text-muted small">No exception mix available for this filter.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0 fw-bold">Orders Requiring Attention</h5>
                    <p class="text-muted small mb-0">Focus on fee reconciliation first, then pending payments and refund requests.</p>
                </div>
                <span class="badge bg-light text-dark border">{{ $orders->total() }} orders</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Order</th>
                                <th>Issue</th>
                                <th>Customer</th>
                                <th>Payment</th>
                                <th>Amount</th>
                                <th>Gateway Fee</th>
                                <th>Status</th>
                                <th>Age</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold">{{ $order->id_order }}</div>
                                        <div class="text-muted small">{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y, H:i') }}</div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <span class="badge {{ $order->issue_badge_class }} border-0 align-self-start">{{ $order->issue_label }}</span>
                                            <span class="badge {{ $order->issue_priority_badge_class }} border-0 align-self-start">Priority {{ $order->issue_priority }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $order->customer_name }}</td>
                                    <td>
                                        <div class="fw-medium">{{ payment_descriptor($order->payment_method, $order->payment_channel) }}</div>
                                        <div class="text-muted small">{{ payment_channel_label($order->payment_channel) }}</div>
                                    </td>
                                    <td>{{ format_ringgit($order->base_amount) }}</td>
                                    <td>
                                        <div class="fw-medium {{ (float) ($order->gateway_fee_amount ?? 0) > 0 ? 'text-danger' : 'text-muted' }}">
                                            {{ format_ringgit($order->gateway_fee_amount ?? 0) }}
                                        </div>
                                        <div class="small text-muted">{{ gateway_fee_source_label($order->gateway_fee_source ?? null) }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ strtoupper(str_replace('_', ' ', $order->status)) }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-dark">{{ $order->age_label }}</div>
                                        <div class="text-muted small">since {{ $order->status === 'pending' ? 'created' : 'paid' }}</div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('sales.detail', $order->id_order) }}" class="btn btn-sm btn-light border-0 text-primary">
                                                View
                                            </a>
                                            @if($order->can_refresh_fee)
                                                <form method="POST" action="{{ route('payment-reconciliation.refresh', $order->id_order) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        Refresh Fee
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="ti ti-checkup-list fs-6 d-block mb-2 opacity-50"></i>
                                        No payment exceptions found for the selected filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($orders->hasPages())
                    <div class="px-4 py-3 border-top d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                        <small class="text-muted">
                            Showing {{ $orders->firstItem() }}-{{ $orders->lastItem() }} of {{ $orders->total() }} exception orders
                        </small>
                        {{ $orders->onEachSide(1)->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
