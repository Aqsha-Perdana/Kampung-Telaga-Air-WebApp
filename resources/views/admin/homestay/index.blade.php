@extends('layout.sidebar')
@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-12 px-0">
            <div class="position-relative" style="height: 200px; border-radius: 10px; overflow: hidden;">
                <!-- Background Image -->
                <img src="{{ asset('assets/images/backgrounds/bg-homestayy.jpg') }}" 
                     alt="Background" 
                     class="w-100 h-100" 
                     style="object-fit: cover; filter: brightness(0.6);">
                
                <!-- Overlay Text -->
                <div class="position-absolute top-50 start-50 translate-middle text-center text-white" style="z-index: 2;">
                    <h1 class="display-4 fw-bold mb-2" style="color: white !important; text-shadow: 2px 2px 8px rgba(0,0,0,0.8);">Homestay Data</h1>
                    <p class="lead mb-0">Manage All Your Homestay Data!</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Homestay</p>
                            <h3 class="mb-0 fw-bold">{{ $homestays->total() }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                            <i class="bi bi-house-door-fill text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Homestay Active</p>
                            <h3 class="mb-0 fw-bold">{{ $homestays->where('is_active', true)->count() }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-3">
                            <i class="bi bi-check-circle-fill text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Toggle & Search Section -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               class="form-control border-0 bg-light" 
                               placeholder="Search homestays by name..."
                               id="searchInput">
                    </div>
                </div>
                <div class="col-md-3 mt-3 mt-md-0">
                    <select class="form-select form-select-lg border-0 bg-light" id="statusFilter">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Unactive</option>
                    </select>
                </div>
                <div class="col-md-3 mt-3 mt-md-0">
                    <div class="btn-group w-100" role="group">
                        <button type="button" class="btn btn-outline-primary active" id="tableView">
                            <i class="bi bi-table"></i> Table View
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="cardView">
                            <i class="bi bi-grid-3x3-gap"></i> Card View
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table View -->
    <div class="card border-0 shadow-sm" id="tableViewContainer">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Homestay List</h5>
            <a href="{{ route('homestays.create') }}" class="btn btn-primary btn-lg shadow-sm">
                    <i class="ti ti-plus me-2"></i> Add Homestay
                </a>
    </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 rounded-start" style="width: 60px;">No</th>
                            <th class="border-0">Homestay ID</th>
                            <th class="border-0">Photo</th>
                            <th class="border-0">Homestay Name</th>
                            <th class="border-0 text-center">Capacity</th>
                            <th class="border-0">Price/Night</th>
                            <th class="border-0 text-center">Status</th>
                            <th class="border-0 text-center rounded-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($homestays as $homestay)
                        <tr class="border-bottom" data-status="{{ $homestay->is_active ? 'active' : 'inactive' }}">
                            <td class="fw-bold text-muted">{{ $loop->iteration + ($homestays->currentPage() - 1) * $homestays->perPage() }}</td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2">
                                    {{ $homestay->id_homestay }}
                                </span>
                            </td>
                            <td>
                                @if($homestay->foto)
                                    <img src="{{ asset('storage/' . $homestay->foto) }}" 
                                         alt="{{ $homestay->nama }}" 
                                         class="rounded shadow-sm" 
                                         loading="lazy"
                                         decoding="async"
                                         style="width: 70px; height: 70px; object-fit: cover; cursor: pointer;"
                                         data-bs-toggle="tooltip"
                                         title="Klik untuk memperbesar">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                         style="width: 70px; height: 70px;">
                                        <i class="ti ti-photo fs-3 text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $homestay->nama }}</div>
                            </td>
                            <td class="text-center">
                                <div class="badge bg-info bg-opacity-10 text-info px-3 py-2">
                                    <i class="ti ti-users me-1"></i>{{ $homestay->kapasitas }} people
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-success">{{ $homestay->formatted_harga }}</div>
                                <small class="text-muted">per night</small>
                            </td>
                            <td class="text-center">
                                @if($homestay->is_active)
                                    <span class="badge bg-success px-3 py-2">
                                        <i class="bi bi-check-circle me-1"></i>Aktif
                                    </span>
                                @else
                                    <span class="badge bg-secondary px-3 py-2">
                                        <i class="bi bi-x-circle me-1"></i>Tidak Aktif
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group shadow-sm" role="group">
                                    <a href="{{ route('homestays.show', $homestay) }}" 
                                       class="btn btn-sm btn-info text-white" 
                                       data-bs-toggle="tooltip"
                                       title="Lihat Detail">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <a href="{{ route('homestays.edit', $homestay) }}" 
                                       class="btn btn-sm btn-warning text-white" 
                                       data-bs-toggle="tooltip"
                                       title="Edit Data">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <form action="{{ route('homestays.destroy', $homestay) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="event.preventDefault(); adminDeleteSwal({ actionUrl: '{{ route('homestays.destroy', $homestay) }}', itemLabel: @js($homestay->nama), title: 'Delete Homestay?', html: 'This will permanently delete <strong>' + @js($homestay->nama) + '</strong>. This action cannot be undone.' });">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-sm btn-danger" 
                                                data-bs-toggle="tooltip"
                                                title="Hapus Data">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="py-4">
                                    <i class="ti ti-database-off fs-1 text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">Belum Ada Data Homestay</h5>
                                    <p class="text-muted mb-3">Mulai tambahkan homestay pertama Anda</p>
                                    <a href="{{ route('homestays.create') }}" class="btn btn-primary">
                                        <i class="ti ti-plus me-2"></i>Tambah Homestay
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($homestays->hasPages())
            <div class="mt-4 d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Menampilkan {{ $homestays->firstItem() ?? 0 }} - {{ $homestays->lastItem() ?? 0 }} 
                    dari {{ $homestays->total() }} homestay
                </div>
                <div>
                    {{ $homestays->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Card View -->
    <div id="cardViewContainer" style="display: none;">
        <div class="row">
            @forelse($homestays as $homestay)
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4 homestay-card" data-status="{{ $homestay->is_active ? 'active' : 'inactive' }}">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <!-- Card Image -->
                    <div class="position-relative">
                        @if($homestay->foto)
                            <img src="{{ asset('storage/' . $homestay->foto) }}" 
                                 class="card-img-top" 
                                 alt="{{ $homestay->nama }}"
                                 loading="lazy"
                                 decoding="async"
                                 style="height: 200px; object-fit: cover;">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 200px;">
                                <i class="ti ti-photo fs-1 text-muted"></i>
                            </div>
                        @endif
                        
                        <!-- Status Badge -->
                        <div class="position-absolute top-0 end-0 m-3">
                            @if($homestay->is_active)
                                <span class="badge bg-success shadow-sm">
                                    <i class="bi bi-check-circle me-1"></i>Aktif
                                </span>
                            @else
                                <span class="badge bg-secondary shadow-sm">
                                    <i class="bi bi-x-circle me-1"></i>Tidak Aktif
                                </span>
                            @endif
                        </div>

                        <!-- ID Badge -->
                        <div class="position-absolute top-0 start-0 m-3">
                            <span class="badge bg-primary shadow-sm">{{ $homestay->id_homestay }}</span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-3">{{ $homestay->nama }}</h5>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <small class="text-muted d-block">Kapasitas</small>
                                <span class="badge bg-info bg-opacity-10 text-info">
                                    <i class="ti ti-users me-1"></i>{{ $homestay->kapasitas }} Orang
                                </span>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">Harga/Malam</small>
                                <span class="fw-bold text-success">{{ $homestay->formatted_harga }}</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <a href="{{ route('homestays.show', $homestay) }}" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="ti ti-eye me-1"></i>Detail
                            </a>
                            <div class="btn-group" role="group">
                                <a href="{{ route('homestays.edit', $homestay) }}" 
                                   class="btn btn-warning btn-sm text-white">
                                    <i class="ti ti-edit me-1"></i>Edit
                                </a>
                                <form action="{{ route('homestays.destroy', $homestay) }}" 
                                      method="POST" 
                                      class="d-inline w-50"
                                      onsubmit="event.preventDefault(); adminDeleteSwal({ actionUrl: '{{ route('homestays.destroy', $homestay) }}', itemLabel: @js($homestay->nama), title: 'Delete Homestay?', html: 'This will permanently delete <strong>' + @js($homestay->nama) + '</strong>. This action cannot be undone.' });">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm w-100">
                                        <i class="ti ti-trash me-1"></i>Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="ti ti-database-off fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="text-muted">Belum Ada Data Homestay</h5>
                        <p class="text-muted mb-3">Mulai tambahkan homestay pertama Anda</p>
                        <a href="{{ route('homestays.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-2"></i>Tambah Homestay
                        </a>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pagination for Card View -->
        @if($homestays->hasPages())
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Menampilkan {{ $homestays->firstItem() ?? 0 }} - {{ $homestays->lastItem() ?? 0 }} 
                        dari {{ $homestays->total() }} homestay
                    </div>
                    <div>
                        {{ $homestays->links() }}
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
/* Custom Styles */
.card {
    transition: all 0.3s ease;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
}

.table tbody tr {
    transition: background-color 0.2s ease;
}

.table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.btn-group .btn {
    transition: all 0.2s ease;
}

.btn-group .btn:hover {
    transform: scale(1.05);
}

.input-group-lg .form-control:focus,
.form-select-lg:focus {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}

.badge {
    transition: all 0.2s ease;
}

img.rounded {
    transition: all 0.3s ease;
}

img.rounded:hover {
    transform: scale(1.05);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.card-img-top {
    transition: all 0.3s ease;
}

.hover-card:hover .card-img-top {
    transform: scale(1.05);
}

.btn-group.w-100 .btn {
    transition: all 0.2s ease;
}

.btn-group .btn.active {
    background-color: #0d6efd;
    color: white;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // View Toggle
    const tableViewBtn = document.getElementById('tableView');
    const cardViewBtn = document.getElementById('cardView');
    const tableViewContainer = document.getElementById('tableViewContainer');
    const cardViewContainer = document.getElementById('cardViewContainer');

    tableViewBtn.addEventListener('click', function() {
        tableViewContainer.style.display = 'block';
        cardViewContainer.style.display = 'none';
        tableViewBtn.classList.add('active');
        cardViewBtn.classList.remove('active');
    });

    cardViewBtn.addEventListener('click', function() {
        tableViewContainer.style.display = 'none';
        cardViewContainer.style.display = 'block';
        cardViewBtn.classList.add('active');
        tableViewBtn.classList.remove('active');
    });

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            
            // Filter table rows
            const tableRows = document.querySelectorAll('#tableViewContainer tbody tr:not(.empty-state)');
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });

            // Filter cards
            const cards = document.querySelectorAll('.homestay-card');
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }

    // Status Filter
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const status = this.value;
            
            // Filter table rows
            const tableRows = document.querySelectorAll('#tableViewContainer tbody tr[data-status]');
            tableRows.forEach(row => {
                if (status === 'all') {
                    row.style.display = '';
                } else {
                    row.style.display = row.dataset.status === status ? '' : 'none';
                }
            });

            // Filter cards
            const cards = document.querySelectorAll('.homestay-card');
            cards.forEach(card => {
                if (status === 'all') {
                    card.style.display = '';
                } else {
                    card.style.display = card.dataset.status === status ? '' : 'none';
                }
            });
        });
    }
});
</script>
@endsection
