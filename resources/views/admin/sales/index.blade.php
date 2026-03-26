@extends('layout.sidebar')

@section('title', 'Sales Dashboard')

@section('content')
<div class="row">
    <!-- Header & Compact Filter -->
    <div class="col-12 mb-3">
        <div class="d-md-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-0 fw-bold">Sales Record</h4>
                <p class="text-muted small mb-0">Monitor transactions & revenue performance</p>
            </div>
            
            <!-- Compact Filter Form -->
            <form method="GET" action="{{ route('sales.index') }}" class="mt-3 mt-md-0 d-flex gap-2 align-items-center bg-white p-2 rounded shadow-sm" id="filterForm">
                <div class="btn-group btn-group-sm me-2" role="group">
                    <button type="button" class="btn btn-outline-secondary" onclick="setDateRange('today')">Today</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="setDateRange('week')">Week</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="setDateRange('month')">Month</button>
                </div>
                
                <input type="date" name="start_date" id="startDate" class="form-control form-control-sm" value="{{ $startDate }}" style="max-width: 130px;">
                <span class="text-muted">-</span>
                <input type="date" name="end_date" id="endDate" class="form-control form-control-sm" value="{{ $endDate }}" style="max-width: 130px;">
                
                <select name="status" class="form-select form-select-sm" style="max-width: 140px;">
                    <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="failed" {{ $status == 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="refund_requested" {{ $status == 'refund_requested' ? 'selected' : '' }}>Refund Requested</option>
                    <option value="refunded" {{ $status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
                
                <button type="submit" class="btn btn-primary btn-sm px-3">
                    <i class="ti ti-filter"></i>
                </button>
            </form>
        </div>
    </div>
    
    <!-- 1. Recent Transactions (Order Details) - Top Priority -->
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold"><i class="ti ti-list-details me-2 text-primary"></i>Recent Transactions</h5>
                <span class="badge bg-light text-dark border">Latest Orders</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                    <table class="table table-hover table-sm align-middle mb-0" id="ordersTable">
                        <thead class="bg-light sticky-top">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Customer</th>
                                <th>Package</th>
                                <th>Amount (MYR)</th>
                                <th>Profit</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($groupedOrders as $order)
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
                                    <span class="fw-bold fs-3 {{ $order->status == 'paid' ? 'text-success' : 'text-dark' }}">RM {{ number_format($order->base_amount, 2) }}</span>
                                    @if($order->display_currency !== 'MYR')
                                        <div class="small text-muted" style="font-size: 0.7rem;">({{ $order->display_currency }} {{ number_format($order->display_amount, 0) }})</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-success fw-medium small">+RM {{ number_format($order->company_profit, 2) }}</span>
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
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="ti ti-shopping-cart-off fs-6 mb-2 d-block opacity-50"></i>
                                    No orders found for this period
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
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
                                <small class="text-muted d-block lh-1 text-uppercase" style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px;">Net Profit</small>
                                <span class="fw-bold fs-4 text-success">{{ format_ringgit($summary['company_revenue']) }}</span>
                            </div>
                        </div>
                        <div class="bg-info-subtle rounded px-3 py-2 d-flex align-items-center">
                            <div class="me-2 text-info"><i class="ti ti-shopping-cart fs-5"></i></div>
                            <div>
                                <small class="text-muted d-block lh-1 text-uppercase" style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px;">Paid Orders</small>
                                <span class="fw-bold fs-4 text-info">{{ $summary['paid_orders'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="salesChart" style="min-height: 350px;"></div>
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
                                        <th class="pe-4 text-end">Resource Revenue</th>
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
                                        <th class="pe-4 text-end">Resource Revenue</th>
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
                                        <th class="pe-4 text-end">Resource Revenue</th>
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
                                        <th class="pe-4 text-end">Resource Revenue</th>
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
        <!-- Category Revenue -->
        <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-0">
                <h5 class="card-title mb-0 fw-bold">Resource Revenue by Category</h5>
            </div>
            <div class="card-body pt-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center border-0 mb-2">
                        <div class="d-flex align-items-center">
                            <span class="bg-primary-subtle text-primary p-2 rounded me-3"><i class="ti ti-ship fs-5"></i></span>
                            <div>
                                <h6 class="mb-0 fw-semibold">Boat</h6>
                                <small class="text-muted">Rental & Tours</small>
                            </div>
                        </div>
                        <span class="fw-bold">{{ format_ringgit($summary['boat_revenue']) }}</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center border-0 mb-2">
                        <div class="d-flex align-items-center">
                            <span class="bg-success-subtle text-success p-2 rounded me-3"><i class="ti ti-home fs-5"></i></span>
                            <div>
                                <h6 class="mb-0 fw-semibold">Homestay</h6>
                                <small class="text-muted">Accommodation</small>
                            </div>
                        </div>
                        <span class="fw-bold">{{ format_ringgit($summary['homestay_revenue']) }}</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center border-0 mb-2">
                        <div class="d-flex align-items-center">
                            <span class="bg-warning-subtle text-warning p-2 rounded me-3"><i class="ti ti-tools-kitchen fs-5"></i></span>
                            <div>
                                <h6 class="mb-0 fw-semibold">Culinary</h6>
                                <small class="text-muted">Food & Beverages</small>
                            </div>
                        </div>
                        <span class="fw-bold">{{ format_ringgit($summary['culinary_revenue']) }}</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center border-0">
                        <div class="d-flex align-items-center">
                            <span class="bg-info-subtle text-info p-2 rounded me-3"><i class="ti ti-building-store fs-5"></i></span>
                            <div>
                                <h6 class="mb-0 fw-semibold">Kiosk</h6>
                                <small class="text-muted">Merchandise</small>
                            </div>
                        </div>
                        <span class="fw-bold">{{ format_ringgit($summary['kiosk_revenue']) }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Currency Breakdown (If available) -->
        @if($summary['display_currency_breakdown']->count() > 0)
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
                            @foreach($summary['display_currency_breakdown']->take(5) as $curr)
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