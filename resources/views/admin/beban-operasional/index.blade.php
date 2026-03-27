@extends('layout.sidebar')
@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="mb-2 fw-bold">
                        <i class="bi bi-receipt-cutoff text-primary"></i> Operating Expenses
                    </h2>
                    <p class="text-muted mb-0">Track and manage your business operating expenses</p>
                </div>
                <a href="{{ route('beban-operasional.create') }}" class="btn btn-primary btn-lg shadow-sm px-4">
                    <i class="bi bi-plus-circle me-2"></i> Record Expense
                </a>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Dashboard -->
    <div class="row g-3 mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 text-uppercase small fw-semibold">Total Expenses</p>
                            <h3 class="mb-0 fw-bold text-danger">{{ format_ringgit($totalBeban) }}</h3>
                            <p class="text-muted small mb-0 mt-2">
                                <i class="bi bi-calendar3 me-1"></i>
                                @if(request('tanggal_mulai') && request('tanggal_akhir'))
                                    {{ date('d M Y', strtotime(request('tanggal_mulai'))) }} - {{ date('d M Y', strtotime(request('tanggal_akhir'))) }}
                                @else
                                    All Time
                                @endif
                            </p>
                        </div>
                        <div class="stat-icon bg-danger bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-receipt text-danger fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 text-uppercase small fw-semibold">Total Transactions</p>
                            <h3 class="mb-0 fw-bold text-primary">{{ $bebans->total() }}</h3>
                            <p class="text-muted small mb-0 mt-2">
                                <i class="bi bi-list-check me-1"></i>Recorded entries
                            </p>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-file-text text-primary fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 text-uppercase small fw-semibold">Average per Transaction</p>
                            <h3 class="mb-0 fw-bold text-info">
                                {{ $bebans->total() > 0 ? format_ringgit($totalBeban / $bebans->total()) : 'RM 0.00' }}
                            </h3>
                            <p class="text-muted small mb-0 mt-2">
                                <i class="bi bi-graph-up me-1"></i>Mean expense value
                            </p>
                        </div>
                        <div class="stat-icon bg-info bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-calculator text-info fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Filter Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-funnel me-2"></i>Filter & Search
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" id="filterForm">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-2">
                            <i class="bi bi-search me-1"></i>Search
                        </label>
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Transaction code, description..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-2">
                            <i class="bi bi-tag me-1"></i>Category
                        </label>
                        <select name="kategori" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($kategoriList as $kat)
                                <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>
                                    {{ $kat }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-2">
                            <i class="bi bi-credit-card me-1"></i>Payment Method
                        </label>
                        <select name="metode_pembayaran" class="form-select">
                            <option value="">All Methods</option>
                            @foreach($metodePembayaran as $metode)
                                <option value="{{ $metode }}" {{ request('metode_pembayaran') == $metode ? 'selected' : '' }}>
                                    {{ $metode }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-2">
                            <i class="bi bi-calendar-event me-1"></i>From Date
                        </label>
                        <input type="date" 
                               name="tanggal_mulai" 
                               class="form-control" 
                               value="{{ request('tanggal_mulai') }}">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-2">
                            <i class="bi bi-calendar-check me-1"></i>To Date
                        </label>
                        <input type="date" 
                               name="tanggal_akhir" 
                               class="form-control" 
                               value="{{ request('tanggal_akhir') }}">
                    </div>
                    <div class="col-lg-1 col-md-6 d-flex align-items-end">
                        <div class="btn-group w-100" role="group">
                            <button type="submit" class="btn btn-primary" title="Apply Filter">
                                <i class="bi bi-funnel me-1">Filter</i>
                            </button>
                            <a href="{{ route('beban-operasional.index') }}" 
                               class="btn btn-outline-secondary" 
                               title="Reset Filter">
                                <i class="bi bi-arrow-clockwise">Reset</i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 py-3 ps-4" style="width: 60px;">No</th>
                            <th class="border-0 py-3">Transaction Code</th>
                            <th class="border-0 py-3">Date</th>
                            <th class="border-0 py-3">Category</th>
                            <th class="border-0 py-3">Description</th>
                            <th class="border-0 py-3 text-end">Amount</th>
                            <th class="border-0 py-3 text-center">Payment</th>
                            <th class="border-0 py-3 text-center pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bebans as $key => $beban)
                        <tr class="expense-row">
                            <td class="ps-4 fw-semibold text-muted">{{ $bebans->firstItem() + $key }}</td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2">
                                    <i class="bi bi-hash"></i>{{ $beban->kode_transaksi }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-calendar3 text-primary me-2"></i>
                                    <span class="fw-medium">{{ $beban->tanggal->format('d M Y') }}</span>
                                </div>
                            </td>
                            <td>
                                @php
                                    $categoryColors = [
                                        'Salaries and Wages' => 'primary',
                                        'Electricity' => 'warning',
                                        'Water' => 'info',
                                        'Telephone and Internet' => 'secondary',
                                        'Building Rent' => 'success',
                                        'Office Supplies' => 'dark',
                                        'Transportation' => 'danger',
                                        'Maintenance and Repairs' => 'warning',
                                        'Insurance' => 'info',
                                        'Taxes and Licenses' => 'danger',
                                        'Advertising and Marketing' => 'success',
                                        'Professional Fees' => 'primary',
                                        'Depreciation' => 'secondary',
                                        'Bank Charges' => 'dark',
                                        'Utilities' => 'warning',
                                    ];
                                    $color = $categoryColors[$beban->kategori] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} px-3 py-2">
                                    <i class="bi bi-tag-fill me-1"></i>{{ $beban->kategori }}
                                </span>
                            </td>
                            <td>
                                <div class="text-dark fw-medium">{{ Str::limit($beban->deskripsi, 40) }}</div>
                                @if($beban->nomor_referensi)
                                    <small class="text-muted">
                                        <i class="bi bi-file-text me-1"></i>Ref: {{ $beban->nomor_referensi }}
                                    </small>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="fw-bold text-danger fs-6">{{ format_ringgit($beban->jumlah) }}</div>
                            </td>
                            <td class="text-center">
                                @php
                                    $paymentIcons = [
                                        'Cash' => 'cash-coin',
                                        'Bank Transfer' => 'bank'
                                    ];
                                    $icon = $paymentIcons[$beban->metode_pembayaran] ?? 'credit-card';
                                @endphp
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2">
                                    <i class="bi bi-{{ $icon }} me-1"></i>{{ $beban->metode_pembayaran }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group shadow-sm" role="group">
                                    <a href="{{ route('beban-operasional.show', $beban) }}" 
                                       class="btn btn-sm btn-info text-white" 
                                       data-bs-toggle="tooltip"
                                       title="See Detail">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <a href="{{ route('beban-operasional.edit', $beban) }}" 
                                       class="btn btn-sm btn-warning text-white" 
                                       data-bs-toggle="tooltip"
                                       title="Edit Data">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <form action="{{ route('beban-operasional.destroy', $beban) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="event.preventDefault(); adminDeleteSwal({ actionUrl: '{{ route('beban-operasional.destroy', $beban) }}', itemLabel: @js($beban->kode_transaksi), title: 'Delete Expense?', html: 'This will permanently delete <strong>' + @js($beban->kode_transaksi) + '</strong>. This action cannot be undone.' });">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-sm btn-danger" 
                                                data-bs-toggle="tooltip"
                                                title="Delete Data">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="py-5">
                                    <div class="mb-4">
                                        <i class="bi bi-inbox display-1 text-muted opacity-50"></i>
                                    </div>
                                    <h5 class="text-muted fw-semibold">No Expenses Recorded</h5>
                                    <p class="text-muted mb-4">Start tracking your operating expenses to maintain better financial records</p>
                                    <a href="{{ route('beban-operasional.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i>Record First Expense
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($bebans->count() > 0)
                    <tfoot class="bg-light border-top">
                        <tr>
                            <td colspan="5" class="text-end fw-bold py-3 ps-4">Total Expenses:</td>
                            <td class="text-end fw-bold text-danger fs-5 py-3">
                                {{ format_ringgit($totalBeban) }}
                            </td>
                            <td colspan="2" class="pe-4"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            
            <!-- Pagination -->
            @if($bebans->hasPages())
            <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                <div class="text-muted small">
                    Showing {{ $bebans->firstItem() ?? 0 }} to {{ $bebans->lastItem() ?? 0 }} 
                    of {{ $bebans->total() }} entries
                </div>
                <div>
                    {{ $bebans->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>

   <!-- Enhanced Analytics Section -->
    @if($bebans->count() > 0)
    <div class="row mt-4 g-4">

    <!-- Monthly Trend (if applicable) -->
    @php
        $monthlyData = $bebans->groupBy(function($item) {
            return $item->tanggal->format('F Y');
        });
    @endphp
    @if($monthlyData->count() > 1)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-graph-up text-success me-2"></i>Monthly Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($monthlyData->take(6) as $month => $expenses)
                        <div class="col-md-4 col-lg-2">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="text-muted small mb-1">{{ $month }}</div>
                                <div class="fw-bold text-danger fs-5">{{ format_ringgit($expenses->sum('jumlah')) }}</div>
                                <small class="text-muted">{{ $expenses->count() }} entries</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif
</div>

<style>
/* Enhanced Styling */
.stat-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1) !important;
}

.stat-icon {
    transition: transform 0.2s ease;
}

.stat-card:hover .stat-icon {
    transform: scale(1.1);
}

.expense-row {
    transition: background-color 0.2s ease;
}

.expense-row:hover {
    background-color: rgba(13, 110, 253, 0.03);
}

.btn-group .btn {
    transition: all 0.2s ease;
}

.btn-group .btn:hover {
    transform: scale(1.05);
}

.progress {
    background-color: rgba(0, 0, 0, 0.05);
}

.form-control:focus,
.form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}

.badge {
    font-weight: 500;
    letter-spacing: 0.3px;
}

.card {
    border-radius: 0.5rem;
}

.table > :not(caption) > * > * {
    padding: 1rem 0.75rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Auto-dismiss alerts
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});

function confirmDelete(code, url) {
    adminDeleteSwal({
        actionUrl: url,
        itemLabel: code,
        title: 'Delete Expense?',
        html: 'This will permanently delete <strong>' + code + '</strong>. This action cannot be undone.'
    });
}
</script>
@endsection
