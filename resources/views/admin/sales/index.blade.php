@extends('layout.sidebar')

@section('title', 'Sales Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Sales Dashboard</h2>
            <p class="text-muted">Monitor tour package sales and revenue (all amounts in MYR)</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('sales.index') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ $status == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="ti ti-filter"></i> Filter
                    </button>
                    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-refresh"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- UPDATED: Display Currency Breakdown Card --}}
    @if($summary['display_currency_breakdown']->count() > 0)
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">
                    <i class="ti ti-currency-dollar"></i> Revenue by Display Currency
                </h5>
                <span class="badge bg-info">All payments processed in MYR</span>
            </div>
            <p class="text-muted small mb-3">
                <i class="bi bi-info-circle"></i> This shows what currency customers <strong>viewed</strong> during checkout. 
                All payments were charged in MYR.
            </p>
            <div class="row">
                @foreach($summary['display_currency_breakdown'] as $curr)
                    @php
                        $currencySymbols = [
                            'MYR' => 'RM',
                            'USD' => '$',
                            'SGD' => 'S$',
                            'IDR' => 'Rp',
                            'EUR' => '€',
                            'GBP' => '£',
                            'AUD' => 'A$',
                            'JPY' => '¥',
                            'CNY' => '¥',
                            'THB' => '฿',
                            'BND' => 'B$',
                        ];
                        $symbol = $currencySymbols[$curr->currency] ?? $curr->currency . ' ';
                    @endphp
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3 h-100 {{ $curr->currency === 'MYR' ? 'bg-light-success' : '' }}">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-0">
                                        {{ $curr->currency }}
                                        @if($curr->currency === 'MYR')
                                            <span class="badge bg-success ms-1">Primary</span>
                                        @endif
                                    </h6>
                                    <small class="text-muted">{{ $curr->total_orders }} orders</small>
                                </div>
                                <span class="badge bg-light text-dark">{{ $symbol }}</span>
                            </div>
                            
                            @if($curr->currency == 'MYR')
                                {{-- Direct MYR orders --}}
                                <h4 class="mb-0 text-success">RM {{ number_format($curr->total_revenue_myr, 2) }}</h4>
                                <small class="text-muted">Charged directly in MYR</small>
                            @else
                                {{-- Display currency orders --}}
                                <div class="mb-2">
                                    <small class="text-muted d-block">Displayed as:</small>
                                    <div class="text-muted">
                                        {{ $symbol }}{{ number_format($curr->total_display_amount, $curr->currency === 'IDR' ? 0 : 2) }}
                                    </div>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Actual revenue (MYR):</small>
                                    <h5 class="mb-0 text-success">
                                        RM {{ number_format($curr->total_revenue_myr, 2) }}
                                    </h5>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    Avg rate: 1 MYR ≈ {{ number_format($curr->avg_exchange_rate, 4) }} {{ $curr->currency }}
                                </small>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 text-white">Total Revenue</h6>
                            <h3 class="mb-0 mt-2 text-white">{{ format_ringgit($summary['total_revenue']) }}</h3>
                            <small>From paid orders (MYR)</small>
                        </div>
                        <div class="fs-1">
                            <i class="ti ti-currency-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 text-white">Paid Orders</h6>
                            <h3 class="mb-0 mt-2 text-white">{{ $summary['paid_orders'] }}</h3>
                            <small>of {{ $summary['total_orders'] }} total orders</small>
                        </div>
                        <div class="fs-1">
                            <i class="ti ti-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 text-white">Avg. Order Value</h6>
                            <h3 class="mb-0 mt-2 text-white">
                                {{ $summary['paid_orders'] > 0 ? format_ringgit($summary['total_revenue'] / $summary['paid_orders']) : format_ringgit(0) }}
                            </h3>
                            <small>Per transaction (MYR)</small>
                        </div>
                        <div class="fs-1">
                            <i class="ti ti-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 text-white">Company Revenue</h6>
                            <h3 class="mb-0 mt-2 text-white">
                                {{ format_ringgit($summary['company_revenue']) }}
                            </h3>
                            <small>Profit from paid orders</small>
                        </div>
                        <div class="fs-1">
                            <i class="ti ti-cash"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue by Category -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="p-2 bg-light-primary rounded me-3">
                            <i class="ti ti-ship fs-5 text-primary"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Boat Revenue</h6>
                        </div>
                    </div>
                    <h4 class="mb-0">{{ format_ringgit($summary['boat_revenue']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="p-2 bg-light-success rounded me-3">
                            <i class="ti ti-home fs-5 text-success"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Homestay Revenue</h6>
                        </div>
                    </div>
                    <h4 class="mb-0">{{ format_ringgit($summary['homestay_revenue']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="p-2 bg-light-warning rounded me-3">
                            <i class="ti ti-tools-kitchen fs-5 text-warning"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Culinary Revenue</h6>
                        </div>
                    </div>
                    <h4 class="mb-0">{{ format_ringgit($summary['culinary_revenue']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="p-2 bg-light-info rounded me-3">
                            <i class="ti ti-building-store fs-5 text-info"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Kiosk Revenue</h6>
                        </div>
                    </div>
                    <h4 class="mb-0">{{ format_ringgit($summary['kiosk_revenue']) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Chart -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Daily Sales Trend (MYR)</h5>
            <canvas id="salesChart" height="80"></canvas>
        </div>
    </div>

    <!-- Entity Breakdown Tabs -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-4">Revenue Breakdown by Entity (MYR)</h5>
            
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#boats-tab">
                        <i class="ti ti-ship"></i> Boats
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#homestays-tab">
                        <i class="ti ti-home"></i> Homestays
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#culinaries-tab">
                        <i class="ti ti-tools-kitchen"></i> Culinaries
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#kiosks-tab">
                        <i class="ti ti-building-store"></i> Kiosks
                    </a>
                </li>
            </ul>

            <div class="tab-content mt-4">
                <!-- Boats Tab -->
                <div class="tab-pane fade show active" id="boats-tab">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Boat Name</th>
                                    <th>Price/Day</th>
                                    <th>Total Orders</th>
                                    <th>Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entityBreakdown['boats'] as $boat)
                                <tr>
                                    <td>{{ $boat->nama }}</td>
                                    <td>{{ format_ringgit($boat->harga_sewa) }}</td>
                                    <td>{{ $boat->total_orders }}</td>
                                    <td><strong>{{ format_ringgit($boat->total_revenue) }}</strong></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Homestays Tab -->
                <div class="tab-pane fade" id="homestays-tab">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Homestay Name</th>
                                    <th>Price/Night</th>
                                    <th>Total Orders</th>
                                    <th>Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entityBreakdown['homestays'] as $homestay)
                                <tr>
                                    <td>{{ $homestay->nama }}</td>
                                    <td>{{ format_ringgit($homestay->harga_per_malam) }}</td>
                                    <td>{{ $homestay->total_orders }}</td>
                                    <td><strong>{{ format_ringgit($homestay->total_revenue) }}</strong></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Culinaries Tab -->
                <div class="tab-pane fade" id="culinaries-tab">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Culinary Name</th>
                                    <th>Total Orders</th>
                                    <th>Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entityBreakdown['culinaries'] as $culinary)
                                <tr>
                                    <td>{{ $culinary->nama }}</td>
                                    <td>{{ $culinary->total_orders }}</td>
                                    <td><strong>{{ format_ringgit($culinary->total_revenue) }}</strong></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Kiosks Tab -->
                <div class="tab-pane fade" id="kiosks-tab">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Kiosk Name</th>
                                    <th>Price/Package</th>
                                    <th>Total Orders</th>
                                    <th>Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entityBreakdown['kiosks'] as $kiosk)
                                <tr>
                                    <td>{{ $kiosk->nama }}</td>
                                    <td>{{ format_ringgit($kiosk->harga_per_paket) }}</td>
                                    <td>{{ $kiosk->total_orders }}</td>
                                    <td><strong>{{ format_ringgit($kiosk->total_revenue) }}</strong></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Detail Table -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-4">Order Details</h5>
            <div class="table-responsive">
                <table class="table table-hover" id="ordersTable">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Package Name</th>
                            <th>Display Currency</th>
                            <th>Amount (MYR)</th>
                            <th class="text-primary">Profit (MYR)</th>
                            <th>Status</th>
                            <th>Order Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groupedOrders as $order)
                        <tr>
                            <td>{{ $order->id_order }}</td>
                            <td>{{ $order->customer_name }}</td>
                            <td>
                                {{ $order->package_names }}
                                @if($order->items_count > 1)
                                    <span class="badge bg-info">{{ $order->items_count }} items</span>
                                @endif
                            </td>
                            {{-- UPDATED: Display Currency Column --}}
                            <td>
                                @php
                                    $displayCurr = $order->display_currency ?? 'MYR';
                                    $currencySymbols = [
                                        'MYR' => 'RM',
                                        'USD' => '$',
                                        'SGD' => 'S$',
                                        'IDR' => 'Rp',
                                        'EUR' => '€',
                                        'GBP' => '£',
                                        'AUD' => 'A$',
                                        'JPY' => '¥',
                                        'CNY' => '¥',
                                        'BND' => 'B$',
                                    ];
                                    $symbol = $currencySymbols[$displayCurr] ?? $displayCurr . ' ';
                                @endphp
                                
                                <span class="badge bg-light text-dark">{{ $displayCurr }}</span>
                                @if($displayCurr !== 'MYR' && $order->display_amount)
                                    <br>
                                    <small class="text-muted">
                                        {{ $symbol }}{{ number_format($order->display_amount, $displayCurr === 'IDR' ? 0 : 2) }}
                                    </small>
                                @endif
                            </td>
                            {{-- UPDATED: Amount Column (ALWAYS MYR) --}}
                            <td>
                                <strong class="text-success">RM {{ number_format($order->base_amount, 2) }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    RM {{ number_format($order->company_profit, 2) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusClass = [
                                        'pending' => 'warning',
                                        'paid' => 'success',
                                        'failed' => 'danger',
                                        'cancelled' => 'secondary'
                                    ][$order->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y H:i') }}</td>
                            <td>
                                <a href="{{ route('sales.detail', $order->id_order) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No orders found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sales Chart
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($chartData['labels']),
        datasets: [{
            label: 'Revenue (MYR)',
            data: @json($chartData['revenue']),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.4,
            yAxisID: 'y'
        }, {
            label: 'Orders',
            data: @json($chartData['orders']),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            tension: 0.4,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                ticks: {
                    callback: function(value) {
                        return 'RM ' + value.toLocaleString();
                    }
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                grid: {
                    drawOnChartArea: false,
                }
            }
        }
    }
});
</script>
@endpush

@endsection