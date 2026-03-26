@extends('layout.sidebar')
@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <a href="{{ route('financial-reports.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                        <i class="ti ti-arrow-left me-1"></i> Back to Financial Reports
                    </a>
                    <h4 class="mb-0 fw-bold">{{ ucfirst($type) }} Owner Detail Report</h4>
                    <p class="text-muted mb-0">
                        Period: {{ Carbon\Carbon::parse($startDate)->format('d F Y') }} - {{ Carbon\Carbon::parse($endDate)->format('d F Y') }}
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group">
                        <button onclick="window.print()" class="btn btn-outline-primary">
                            <i class="ti ti-printer me-1"></i> Print
                        </button>
                        <a href="{{ route('financial-reports.owner.pdf', ['type' => $type, 'id' => $report['id']]) }}?start_date={{ $startDate }}&end_date={{ $endDate }}" 
                           class="btn btn-outline-danger">
                            <i class="ti ti-file-type-pdf me-1"></i> Export PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Owner Information -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-3">
                        <i class="ti ti-user-circle me-2"></i>Owner Information
                    </h5>
                    <div class="row">
                        <div class="col-md-3">
                            <p class="mb-2"><strong>ID:</strong></p>
                            <p><span class="badge bg-primary fs-6">{{ $report['id'] }}</span></p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-2"><strong>Name:</strong></p>
                            <p class="fs-5">{{ $report['name'] }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-2"><strong>Type:</strong></p>
                            <p><span class="badge bg-info">{{ $report['type'] }}</span></p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-2"><strong>Price per {{ $report['unit_name'] }}:</strong></p>
                            <p class="fs-5 text-success fw-bold">
                                {{ format_ringgit_report($report['price_per_unit']) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="text-white mb-2">Total Usage</h6>
                    <h3 class="mb-0 fw-bold text-white">{{ $report['usage_count'] }}x</h3>
                    <small>Times Used</small>
                </div>
            </div>
        </div>
        
        @if(isset($report['total_units']))
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="text-white mb-2">Total {{ ucfirst($report['unit_name']) }}s</h6>
                    <h3 class="mb-0 fw-bold text-white">{{ $report['total_units'] }}</h3>
                    <small>{{ ucfirst($report['unit_name']) }}s Booked</small>
                </div>
            </div>
        </div>
        @endif
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="text-white mb-2">Total Participants</h6>
                    <h3 class="mb-0 fw-bold text-white">{{ $report['total_participants'] }}</h3>
                    <small>People Served</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="text-white mb-2">Owner Revenue</h6>
                    <h3 class="mb-0 fw-bold text-white">{{ format_ringgit_report($report['total_revenue']) }}</h3>
                    <small>Earnings</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Details -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-bold mb-4">
                <i class="ti ti-list-details me-2"></i>Transaction Details
            </h5>
            
            @if($report['transactions']->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Package</th>
                            <th>Departure Date</th>
                            <th class="text-center">Participants</th>
                            @if(isset($report['total_units']))
                            <th class="text-center">{{ ucfirst($report['unit_name']) }}s</th>
                            @endif
                            <th class="text-end">Owner Revenue</th>
                            <th>Order Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['transactions'] as $transaction)
                        <tr>
                            <td>
                                <span class="badge bg-primary">{{ $transaction->id_order }}</span>
                            </td>
                            <td class="fw-semibold">{{ $transaction->customer_name }}</td>
                            <td>
                                {{ $transaction->nama_paket }}
                                @if(isset($transaction->variant_name))
                                    <div class="small text-muted fst-italic">{{ $transaction->variant_name }}</div>
                                @endif
                            </td>
                            <td>{{ Carbon\Carbon::parse($transaction->tanggal_keberangkatan)->format('d M Y') }}</td>
                            <td class="text-center">
                                <span class="badge bg-success">{{ $transaction->jumlah_peserta }} pax</span>
                            </td>
                            @if(isset($report['total_units']) && isset($transaction->jumlah_malam))
                            <td class="text-center">
                                <span class="badge bg-info">{{ $transaction->jumlah_malam }} {{ $report['unit_name'] }}s</span>
                            </td>
                            @endif
                            <td class="text-end fw-bold text-success">
                                @if(isset($transaction->variant_price))
                                    {{ format_ringgit_report($transaction->variant_price) }}
                                @elseif(isset($transaction->jumlah_malam))
                                    {{ format_ringgit_report($transaction->jumlah_malam * $report['price_per_unit']) }}
                                @else
                                    {{ format_ringgit_report($report['price_per_unit']) }}
                                @endif
                            </td>
                            <td>{{ Carbon\Carbon::parse($transaction->created_at)->format('d M Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="{{ isset($report['total_units']) ? '6' : '5' }}" class="text-end">TOTAL OWNER REVENUE:</th>
                            <th class="text-end text-success fs-5">
                                {{ format_ringgit_report($report['total_revenue']) }}
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="alert alert-info">
                <i class="ti ti-info-circle me-2"></i>
                No transactions found for this owner in the selected period.
            </div>
            @endif
        </div>
    </div>

    <!-- Performance Analysis -->
    @if($report['transactions']->count() > 0)
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-3">
                        <i class="ti ti-chart-bar me-2"></i>Performance Analysis
                    </h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <p class="text-muted mb-1">Average Participants per Booking</p>
                                <h4 class="fw-bold mb-0">
                                    {{ number_format($report['total_participants'] / $report['usage_count'], 1) }} pax
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <p class="text-muted mb-1">Average Owner Revenue per Booking</p>
                                <h4 class="fw-bold mb-0 text-success">
                                    {{ format_ringgit_report($report['total_revenue'] / $report['usage_count']) }}
                                </h4>
                            </div>
                        </div>
                        @if(isset($report['total_units']))
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <p class="text-muted mb-1">Average {{ ucfirst($report['unit_name']) }}s per Booking</p>
                                <h4 class="fw-bold mb-0">
                                    {{ number_format($report['total_units'] / $report['usage_count'], 1) }} {{ $report['unit_name'] }}s
                                </h4>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('styles')
<style>
@media print {
    .sidebar, .navbar, .btn, .btn-group { display: none !important; }
    .card { border: 1px solid #ddd !important; box-shadow: none !important; page-break-inside: avoid; }
    .card-body { padding: 15px !important; }
}
</style>
@endsection
