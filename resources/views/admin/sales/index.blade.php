@extends('layout.sidebar')

@section('title', 'Sales Dashboard')

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3 p-lg-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-3">
                    <div>
                        <h4 class="mb-1 fw-bold">Sales Record</h4>
                        <p class="text-muted small mb-0">Monitor transactions, gateway fee quality, and payment mix in one place.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('payment-reconciliation.index') }}" class="btn btn-light border btn-sm">
                            <i class="ti ti-alert-circle me-1"></i>Payment Exceptions
                        </a>
                        <a href="{{ route('sales.export', request()->except('sales_page')) }}" class="btn btn-primary btn-sm">
                            <i class="ti ti-file-export me-1"></i>Export Excel
                        </a>
                    </div>
                </div>

                <form method="GET" action="{{ route('sales.index') }}" id="filterForm">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="btn-group btn-group-sm flex-shrink-0" role="group" aria-label="Quick date filters">
                            <button type="button" class="btn btn-outline-secondary" onclick="setDateRange('today')">Today</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="setDateRange('week')">Week</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="setDateRange('month')">Month</button>
                        </div>

                        <label for="startDate" class="visually-hidden">Start date</label>
                        <input
                            type="date"
                            name="start_date"
                            id="startDate"
                            class="form-control form-control-sm flex-grow-1"
                            value="{{ $startDate }}"
                            style="min-width: 150px; max-width: 165px;"
                        >

                        <label for="endDate" class="visually-hidden">End date</label>
                        <input
                            type="date"
                            name="end_date"
                            id="endDate"
                            class="form-control form-control-sm flex-grow-1"
                            value="{{ $endDate }}"
                            style="min-width: 150px; max-width: 165px;"
                        >

                        <label for="statusFilter" class="visually-hidden">Order status</label>
                        <select name="status" id="statusFilter" class="form-select form-select-sm flex-grow-1" style="min-width: 150px; max-width: 170px;">
                            <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Status</option>
                            <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="confirmed" {{ $status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="failed" {{ $status == 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="refund_requested" {{ $status == 'refund_requested' ? 'selected' : '' }}>Refund Requested</option>
                            <option value="refunded" {{ $status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>

                        <label for="paymentMethodFilter" class="visually-hidden">Payment gateway</label>
                        <select name="payment_method" id="paymentMethodFilter" class="form-select form-select-sm flex-grow-1" style="min-width: 160px; max-width: 180px;">
                            <option value="all" {{ $paymentMethod === 'all' ? 'selected' : '' }}>All Gateways</option>
                            @foreach($availablePaymentMethods as $method)
                                <option value="{{ $method }}" {{ $paymentMethod === $method ? 'selected' : '' }}>
                                    {{ payment_method_label($method) }}
                                </option>
                            @endforeach
                        </select>

                        <label for="paymentChannelFilter" class="visually-hidden">Payment channel</label>
                        <select name="payment_channel" id="paymentChannelFilter" class="form-select form-select-sm flex-grow-1" style="min-width: 160px; max-width: 180px;">
                            <option value="all" {{ $paymentChannel === 'all' ? 'selected' : '' }}>All Channels</option>
                            @foreach($availablePaymentChannels as $channel)
                                <option value="{{ $channel }}" {{ $paymentChannel === $channel ? 'selected' : '' }}>
                                    {{ payment_channel_label($channel) }}
                                </option>
                            @endforeach
                        </select>

                        <label for="gatewayFeeSourceFilter" class="visually-hidden">Gateway fee source</label>
                        <select name="gateway_fee_source" id="gatewayFeeSourceFilter" class="form-select form-select-sm flex-grow-1" style="min-width: 160px; max-width: 180px;">
                            <option value="all" {{ $gatewayFeeSource === 'all' ? 'selected' : '' }}>All Sources</option>
                            @foreach($availableFeeSources as $source)
                                <option value="{{ $source }}" {{ $gatewayFeeSource === $source ? 'selected' : '' }}>
                                    {{ gateway_fee_source_label($source) }}
                                </option>
                            @endforeach
                        </select>

                        <label for="displayCurrencyFilter" class="visually-hidden">Display currency</label>
                        <select name="display_currency" id="displayCurrencyFilter" class="form-select form-select-sm flex-grow-1" style="min-width: 150px; max-width: 170px;">
                            <option value="all" {{ $displayCurrency === 'all' ? 'selected' : '' }}>All Currencies</option>
                            @foreach($availableDisplayCurrencies as $currency)
                                <option value="{{ $currency }}" {{ $displayCurrency === $currency ? 'selected' : '' }}>
                                    {{ $currency }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-primary btn-sm px-3 flex-shrink-0">
                            <i class="ti ti-filter me-1"></i>Apply
                        </button>

                        <a href="{{ route('sales.index') }}" class="btn btn-light border btn-sm px-3 flex-shrink-0">Reset</a>
                    </div>
                </form>

                @php
                    $activeFilterBadges = collect([
                        $paymentMethod !== 'all' ? payment_method_label($paymentMethod) : null,
                        $paymentChannel !== 'all' ? payment_channel_label($paymentChannel) : null,
                        $gatewayFeeSource !== 'all' ? gateway_fee_source_label($gatewayFeeSource) : null,
                        $displayCurrency !== 'all' ? 'Currency: ' . $displayCurrency : null,
                    ])->filter();
                @endphp

                @if($activeFilterBadges->isNotEmpty())
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        @foreach($activeFilterBadges as $badge)
                            <span class="badge bg-light text-dark border">{{ $badge }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- 1. Recent Transactions (Order Details) - Top Priority -->
    <div class="col-12 mb-4" id="recent-transactions">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold"><i class="ti ti-list-details me-2 text-primary"></i>Recent Transactions</h5>
                <span class="badge bg-light text-dark border">Latest Orders</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0" id="ordersTable">
                        <thead class="bg-light sticky-top">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Customer</th>
                                <th>Package</th>
                                <th>Payment</th>
                                <th>Amount (MYR)</th>
                                <th>Gateway Fee</th>
                                <th>Net Profit</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $order)
                            <tr>
                                <td class="ps-4"><span class="fw-medium text-dark">#{{ $order->id_order }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary-subtle text-primary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 0.75rem;">
                                            {{ substr($order->customer_name, 0, 1) }}
                                        </div>
                                        <span class="fw-medium text-truncate" style="max-width: 120px;">{{ $order->customer_name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="d-inline-block text-truncate" style="max-width: 150px;" title="{{ $order->package_names }}">
                                        {{ $order->package_names }}
                                    </span>
                                    @if($order->items_count > 1)
                                        <span class="badge bg-secondary-subtle text-secondary ms-1 shadow-none" style="font-size: 0.65rem;">+{{ $order->items_count - 1 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-medium text-dark small">
                                        {{ payment_descriptor($order->payment_method, $order->payment_channel) }}
                                    </div>
                                    <div class="text-muted" style="font-size: 0.7rem;">
                                        {{ payment_channel_label($order->payment_channel) === '-' ? 'Channel not captured' : 'Channel: ' . payment_channel_label($order->payment_channel) }}
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold fs-3 {{ $order->status == 'paid' ? 'text-success' : 'text-dark' }}">RM {{ number_format($order->base_amount, 2) }}</span>
                                    @if($order->display_currency !== 'MYR')
                                        <div class="small text-muted" style="font-size: 0.7rem;">({{ $order->display_currency }} {{ number_format($order->display_amount, 0) }})</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-medium {{ ($order->gateway_fee_amount ?? 0) > 0 ? 'text-danger' : 'text-muted' }}">
                                        {{ format_ringgit($order->gateway_fee_amount ?? 0) }}
                                    </div>
                                    <div class="mt-1">
                                        <span class="badge {{ gateway_fee_source_badge_class($order->gateway_fee_source ?? null) }} border-0">
                                            {{ gateway_fee_source_label($order->gateway_fee_source ?? null) }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $netProfit = (float) ($order->company_profit ?? 0);
                                        $netProfitPrefix = $netProfit > 0 ? '+' : '';
                                        $netProfitClass = $netProfit < 0 ? 'text-danger' : 'text-success';
                                    @endphp
                                    <span class="{{ $netProfitClass }} fw-medium small">{{ $netProfitPrefix }}RM {{ number_format($netProfit, 2) }}</span>
                                    @if(isset($order->original_profit) && abs((float) $order->original_profit - (float) $order->company_profit) > 0.009)
                                        <div class="text-muted" style="font-size: 0.7rem;">Before fee {{ format_ringgit($order->original_profit) }}</div>
                                    @endif
                                    @if(($order->gateway_net_amount ?? 0) > 0)
                                        <div class="text-muted" style="font-size: 0.7rem;">Net {{ format_ringgit($order->gateway_net_amount) }}</div>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusClass = [
                                            'pending' => 'bg-warning-subtle text-warning',
                                            'paid' => 'bg-success-subtle text-success',
                                            'failed' => 'bg-danger-subtle text-danger',
                                            'cancelled' => 'bg-secondary-subtle text-secondary',
                                            'refund_requested' => 'bg-warning text-dark',
                                            'refunded' => 'bg-dark text-white'
                                        ][$order->status] ?? 'bg-secondary-subtle text-secondary';
                                    @endphp
                                    <span class="badge {{ $statusClass }} border-0 rounded-pill px-2">{{ strtoupper(str_replace('_', ' ', $order->status)) }}</span>
                                </td>
                                <td class="small text-muted">{{ \Carbon\Carbon::parse($order->created_at)->format('d M, H:i') }}</td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('sales.detail', $order->id_order) }}" class="btn btn-sm btn-light text-primary py-1 px-2 border-0">
                                        View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-5">
                                    <i class="ti ti-shopping-cart-off fs-6 mb-2 d-block opacity-50"></i>
                                    No orders found for this period
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($recentTransactions->hasPages())
                    <div class="px-4 py-3 border-top d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                        <small class="text-muted">
                            Showing {{ $recentTransactions->firstItem() }}-{{ $recentTransactions->lastItem() }} of {{ $recentTransactions->total() }} recent transactions
                        </small>
                        {{ $recentTransactions->onEachSide(1)->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 mb-4">
        <div class="alert alert-light border small mb-0">
            Sales summary follows Profit &amp; Loss recognition by order date for `paid`, `confirmed`, `completed`, and refunded impact. Cash Flow in Financial Reports remains settlement-based using payment and refund dates.
        </div>
    </div>

    <!-- 2. Daily Sales Trend (Hero Chart) -->
    <div class="col-12 mb-4">
        <div class="card shadow border-0 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                    <div>
                        <h5 class="card-title fw-bold mb-1">Daily Sales Trend (MYR)</h5>
                        <p class="text-muted small mb-0">Revenue performance over time</p>
                    </div>
                    <div class="d-flex gap-3 mt-3 mt-md-0 flex-wrap">
                        <div class="bg-primary-subtle rounded px-3 py-2 d-flex align-items-center">
                            <div class="me-2 text-primary"><i class="ti ti-currency-dollar fs-5"></i></div>
                            <div>
                                <small class="text-muted d-block lh-1 text-uppercase" style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px;">Total Revenue</small>
                                <span class="fw-bold fs-4 text-primary">{{ format_ringgit($summary['total_revenue']) }}</span>
                            </div>
                        </div>
                        <div class="bg-success-subtle rounded px-3 py-2 d-flex align-items-center">
                            <div class="me-2 text-success"><i class="ti ti-cash fs-5"></i></div>
                            <div>
                                <small class="text-muted d-block lh-1 text-uppercase" style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px;">Net Profit After Fee</small>
                                <span class="fw-bold fs-4 text-success">{{ format_ringgit($summary['net_profit_after_gateway_fee']) }}</span>
                            </div>
                        </div>
                        <div class="bg-info-subtle rounded px-3 py-2 d-flex align-items-center">
                            <div class="me-2 text-info"><i class="ti ti-shopping-cart fs-5"></i></div>
                            <div>
                                <small class="text-muted d-block lh-1 text-uppercase" style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px;">Recognized Orders</small>
                                <span class="fw-bold fs-4 text-info">{{ $summary['recognized_orders'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="salesChart" style="min-height: 350px;"></div>
            </div>
        </div>
    </div>

    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0 fw-bold"><i class="ti ti-receipt-2 me-2 text-danger"></i>Gateway Fee Breakdown</h5>
                    <p class="text-muted small mb-0">Grouped by payment gateway and payment type, with per-order detail.</p>
                </div>
                <div class="text-end">
                    <small class="text-muted d-block">Total Gateway Fee</small>
                    <span class="fw-bold text-danger">{{ format_ringgit($summary['gateway_fee_total'] ?? 0) }}</span>
                </div>
            </div>
            <div class="card-body">
                @if(($summary['gateway_fee_groups'] ?? collect())->count() > 0)
                    <div class="accordion" id="gatewayFeeAccordion">
                        @foreach($summary['gateway_fee_groups'] as $group)
                            <div class="accordion-item border rounded-3 mb-3 overflow-hidden">
                                <h2 class="accordion-header" id="gatewayFeeHeading{{ $loop->index }}">
                                    <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#gatewayFeeCollapse{{ $loop->index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="gatewayFeeCollapse{{ $loop->index }}">
                                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center w-100 me-3 gap-2">
                                            <div>
                                                <div class="fw-semibold">{{ $group->label }}</div>
                                                <div class="text-muted small">
                                                    {{ $group->transactions }} transaction{{ $group->transactions > 1 ? 's' : '' }}
                                                    @if(!empty($group->source_breakdown))
                                                        | {{ collect($group->source_breakdown)->map(fn ($count, $source) => $source . ': ' . $count)->implode(', ') }}
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-3 small">
                                                <span><span class="text-muted">Gross</span> <strong>{{ format_ringgit($group->gross_amount) }}</strong></span>
                                                <span><span class="text-muted">Fee</span> <strong class="text-danger">{{ format_ringgit($group->gateway_fee_total) }}</strong></span>
                                                <span><span class="text-muted">Net</span> <strong>{{ format_ringgit($group->net_settlement_total) }}</strong></span>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="gatewayFeeCollapse{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="gatewayFeeHeading{{ $loop->index }}" data-bs-parent="#gatewayFeeAccordion">
                                    <div class="accordion-body bg-light-subtle">
                                        <div class="table-responsive">
                                            <table class="table table-sm align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Order</th>
                                                        <th>Customer</th>
                                                        <th>Date</th>
                                                        <th class="text-end">Gross</th>
                                                        <th class="text-end">Gateway Fee</th>
                                                        <th>Source</th>
                                                        <th class="text-end">Net Settlement</th>
                                                        <th class="text-end">Net Profit</th>
                                                        <th class="text-end">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($group->details as $detail)
                                                        <tr>
                                                            <td class="fw-medium">{{ $detail->id_order }}</td>
                                                            <td>{{ $detail->customer_name }}</td>
                                                            <td class="text-muted small">{{ \Carbon\Carbon::parse($detail->created_at)->format('d M Y, H:i') }}</td>
                                                            <td class="text-end">{{ format_ringgit($detail->base_amount) }}</td>
                                                            <td class="text-end text-danger">{{ format_ringgit($detail->gateway_fee_amount) }}</td>
                                                            <td>
                                                                <span class="badge {{ gateway_fee_source_badge_class($detail->gateway_fee_source ?? null) }} border-0">
                                                                    {{ gateway_fee_source_label($detail->gateway_fee_source ?? null) }}
                                                                </span>
                                                            </td>
                                                            <td class="text-end">{{ format_ringgit($detail->gateway_net_amount) }}</td>
                                                            <td class="text-end text-success">{{ format_ringgit($detail->company_profit) }}</td>
                                                            <td class="text-end">
                                                                <a href="{{ route('sales.detail', $detail->id_order) }}" class="btn btn-sm btn-light text-primary border-0">View</a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="ti ti-receipt-off fs-6 mb-2 d-block opacity-50"></i>
                        No gateway fee data available for this period
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 3. Performance Breakdown (2 Columns) -->
    <!-- Left: Entity Details (Tabs) -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">Performance by Entity</h5>
                    
                    <ul class="nav nav-pills nav-pills-sm" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active py-1 px-3 fs-3" data-bs-toggle="tab" href="#boats-tab">Boats</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-1 px-3 fs-3" data-bs-toggle="tab" href="#homestays-tab">Homestays</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-1 px-3 fs-3" data-bs-toggle="tab" href="#culinaries-tab">Culinaries</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-1 px-3 fs-3" data-bs-toggle="tab" href="#kiosks-tab">Kiosks</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-body p-0">
                    <div class="tab-content">
                    <!-- Boats Tab -->
                    <div class="tab-pane fade show active" id="boats-tab">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Boat Name</th>
                                        <th>Price/Day</th>
                                        <th>Orders</th>
                                        <th class="pe-4 text-end">Vendor Cost Snapshot</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($entityBreakdown['boats'] as $boat)
                                    <tr>
                                        <td class="ps-4 fw-medium">{{ $boat->nama }}</td>
                                        <td class="text-muted">{{ format_ringgit($boat->harga_sewa) }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $boat->total_orders }}</span></td>
                                        <td class="pe-4 text-end fw-bold text-dark">{{ format_ringgit($boat->total_revenue) }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center py-4 text-muted">No data available</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Homestays Tab -->
                    <div class="tab-pane fade" id="homestays-tab">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Homestay</th>
                                        <th>Price/Night</th>
                                        <th>Orders</th>
                                        <th class="pe-4 text-end">Vendor Cost Snapshot</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($entityBreakdown['homestays'] as $homestay)
                                    <tr>
                                        <td class="ps-4 fw-medium">{{ $homestay->nama }}</td>
                                        <td class="text-muted">{{ format_ringgit($homestay->harga_per_malam) }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $homestay->total_orders }}</span></td>
                                        <td class="pe-4 text-end fw-bold text-dark">{{ format_ringgit($homestay->total_revenue) }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center py-4 text-muted">No data available</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Culinaries Tab -->
                    <div class="tab-pane fade" id="culinaries-tab">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Item Name</th>
                                        <th>Orders</th>
                                        <th class="pe-4 text-end">Vendor Cost Snapshot</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($entityBreakdown['culinaries'] as $culinary)
                                    <tr>
                                        <td class="ps-4 fw-medium">{{ $culinary->nama }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $culinary->total_orders }}</span></td>
                                        <td class="pe-4 text-end fw-bold text-dark">{{ format_ringgit($culinary->total_revenue) }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="text-center py-4 text-muted">No data available</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Kiosks Tab -->
                    <div class="tab-pane fade" id="kiosks-tab">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Kiosk</th>
                                        <th>Price</th>
                                        <th>Orders</th>
                                        <th class="pe-4 text-end">Vendor Cost Snapshot</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($entityBreakdown['kiosks'] as $kiosk)
                                    <tr>
                                        <td class="ps-4 fw-medium">{{ $kiosk->nama }}</td>
                                        <td class="text-muted">{{ format_ringgit($kiosk->harga_per_paket) }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $kiosk->total_orders }}</span></td>
                                        <td class="pe-4 text-end fw-bold text-dark">{{ format_ringgit($kiosk->total_revenue) }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center py-4 text-muted">No data available</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Category & Currency Stats -->
    <div class="col-lg-4 mb-4">
        <!-- Vendor Cost Breakdown -->
        <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-0">
                <h5 class="card-title mb-0 fw-bold">Vendor Cost by Category</h5>
            </div>
            <div class="card-body pt-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center border-0 mb-2">
                        <div class="d-flex align-items-center">
                            <span class="bg-primary-subtle text-primary p-2 rounded me-3"><i class="ti ti-ship fs-5"></i></span>
                            <div>
                                <h6 class="mb-0 fw-semibold">Boat Cost</h6>
                                <small class="text-muted">Vendor snapshot</small>
                            </div>
                        </div>
                        <span class="fw-bold">{{ format_ringgit($summary['boat_cost_total']) }}</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center border-0 mb-2">
                        <div class="d-flex align-items-center">
                            <span class="bg-success-subtle text-success p-2 rounded me-3"><i class="ti ti-home fs-5"></i></span>
                            <div>
                                <h6 class="mb-0 fw-semibold">Homestay Cost</h6>
                                <small class="text-muted">Vendor snapshot</small>
                            </div>
                        </div>
                        <span class="fw-bold">{{ format_ringgit($summary['homestay_cost_total']) }}</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center border-0 mb-2">
                        <div class="d-flex align-items-center">
                            <span class="bg-warning-subtle text-warning p-2 rounded me-3"><i class="ti ti-tools-kitchen fs-5"></i></span>
                            <div>
                                <h6 class="mb-0 fw-semibold">Culinary Cost</h6>
                                <small class="text-muted">Vendor snapshot</small>
                            </div>
                        </div>
                        <span class="fw-bold">{{ format_ringgit($summary['culinary_cost_total']) }}</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center border-0">
                        <div class="d-flex align-items-center">
                            <span class="bg-info-subtle text-info p-2 rounded me-3"><i class="ti ti-building-store fs-5"></i></span>
                            <div>
                                <h6 class="mb-0 fw-semibold">Kiosk Cost</h6>
                                <small class="text-muted">Vendor snapshot</small>
                            </div>
                        </div>
                        <span class="fw-bold">{{ format_ringgit($summary['kiosk_cost_total']) }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Currency Breakdown (If available) -->
        @if($summary['currency_breakdown']->count() > 0)
        <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-0">
                <h5 class="card-title mb-0 fw-bold">Top Currencies</h5>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-sm table-borderless align-middle mb-0">
                        <thead class="text-muted small text-uppercase">
                            <tr>
                                <th>Currency</th>
                                <th class="text-end">Orders</th>
                                <th class="text-end">Value (MYR)</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @foreach($summary['currency_breakdown']->take(5) as $curr)
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $curr->currency }}</span>
                                </td>
                                <td class="text-end text-muted">{{ $curr->total_orders }}</td>
                                <td class="text-end fw-medium">{{ number_format($curr->total_revenue_myr, 0) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    function setDateRange(range) {
        const today = new Date();
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');
        const form = document.getElementById('filterForm');
        
        // Helper to format date as YYYY-MM-DD
        const formatDate = (date) => {
            const d = new Date(date);
            let month = '' + (d.getMonth() + 1);
            let day = '' + d.getDate();
            const year = d.getFullYear();
    
            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;
    
            return [year, month, day].join('-');
        }
    
        if (range === 'today') {
            startDateInput.value = formatDate(today);
            endDateInput.value = formatDate(today);
        } else if (range === 'week') {
            const firstDay = new Date(today.setDate(today.getDate() - today.getDay() + 1)); // Monday
            const lastDay = new Date(today.setDate(today.getDate() - today.getDay() + 7)); // Sunday
            startDateInput.value = formatDate(firstDay);
            endDateInput.value = formatDate(lastDay);
        } else if (range === 'month') {
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            startDateInput.value = formatDate(firstDay);
            endDateInput.value = formatDate(lastDay);
        }
    
        form.submit();
    }

document.addEventListener('DOMContentLoaded', function () {
    // Sales Trend Chart (ApexCharts)
    var salesData = @json($chartData['revenue']);
    var ordersData = @json($chartData['orders']);
    var labels = @json($chartData['labels']);

    var options = {
        series: [{
            name: 'Revenue (MYR)',
            type: 'area',
            data: salesData
        }, {
            name: 'Orders',
            type: 'line',
            data: ordersData
        }],
        chart: {
            height: 380, // Slightly increased height
            type: 'line',
            fontFamily: 'inherit',
            toolbar: {
                show: true,
                offsetY: -25, // Move toolbar up slightly
                tools: {
                    download: true,
                    selection: true,
                    zoom: true,
                    zoomin: true,
                    zoomout: true,
                    pan: true,
                }
            }
        },
        colors: ['#5D87FF', '#13DEB9'],
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: [2, 2],
            dashArray: [0, 5]
        },
        fill: {
            type: ['gradient', 'solid'],
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.1,
                stops: [0, 90, 100]
            }
        },
        labels: labels,
        xaxis: {
            type: 'category',
            tooltip: {
                enabled: false
            }
        },
        yaxis: [
            {
                title: {
                    text: 'Revenue (MYR)',
                    style: {
                        color: '#5D87FF',
                    }
                },
                labels: {
                    formatter: function (value) {
                         return "RM " + value.toLocaleString();
                    }
                }
            },
            {
                opposite: true,
                title: {
                    text: 'Orders',
                    style: {
                        color: '#13DEB9',
                    }
                }
            }
        ],
        grid: {
            borderColor: '#ecf0f2',
            strokeDashArray: 4,
            xaxis: {
                lines: {
                    show: true
                }
            },
            padding: {
                top: 0,
                right: 0,
                bottom: 0,
                left: 10
            } 
        },
        tooltip: {
            theme: 'light',
            y: {
                formatter: function (val, { seriesIndex }) {
                    if(seriesIndex === 0) return "RM " + val.toLocaleString();
                    return val + " orders";
                }
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'left', // Aligned to left to avoid toolbar overlap
            offsetY: 0,
            offsetX: -10
        }
    };

    var chart = new ApexCharts(document.querySelector("#salesChart"), options);
    chart.render();
});
</script>
@endpush

@endsection
