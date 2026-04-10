@extends('layout.sidebar')
@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-12 px-0">
            <div class="position-relative" style="height: 200px; border-radius: 10px; overflow: hidden;">
                <!-- Background Image -->
                <img src="{{ asset('assets/images/backgrounds/bg-destination.jpg') }}" 
                     alt="Background" 
                     class="w-100 h-100" 
                     style="object-fit: cover; filter: brightness(0.6);">
                
                <!-- Overlay Text -->
                <div class="position-absolute top-50 start-50 translate-middle text-center text-white" style="z-index: 2;">
                    <h1 class="display-4 fw-bold mb-2" style="color: white !important; text-shadow: 2px 2px 8px rgba(0,0,0,0.8);">Destination Data</h1>
                    <p class="lead mb-0">Manage All Your Destination Data!</p>
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
                            <p class="text-muted mb-1 small">Total Destination</p>
                            <h3 class="mb-0 fw-bold">{{ $destinasis->total() }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                            <i class="bi bi-geo-alt-fill text-primary fs-4"></i>
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
                               placeholder="Search for destinations by name or location..."
                               id="searchInput">
                    </div>
                </div>
                <div class="col-md-3 mt-3 mt-md-0">
                    <select class="form-select form-select-lg border-0 bg-light" id="sortFilter">
                        <option selected>Filter by</option>
                        <option value="nama-asc">Name (A-Z)</option>
                        <option value="nama-desc">Name (Z-A)</option>
                        <option value="terbaru">Newest</option>
                        <option value="terlama">Lastest</option>
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
    <div id="tableViewContainer">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Destination List</h5>
                <a href="{{ route('destinasis.create') }}" class="btn btn-primary btn-lg shadow-sm">
                    <i class="ti ti-plus me-2"></i> Add Destination
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 rounded-start" style="width: 60px;">No</th>
                                <th class="border-0">Destination ID</th>
                                <th class="border-0">Photos</th>
                                <th class="border-0">Destination Name</th>
                                <th class="border-0">Location</th>
                                <th class="border-0">Description</th>
                                <th class="border-0 text-center rounded-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($destinasis as $index => $destinasi)
                            <tr class="border-bottom destinasi-row">
                                <td class="fw-bold text-muted">{{ $destinasis->firstItem() + $index }}</td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2">
                                        {{ $destinasi->id_destinasi }}
                                    </span>
                                </td>
                                <td>
                                    @if($destinasi->fotos->count() > 0)
                                        <div class="d-flex gap-2 align-items-center">
                                            @foreach($destinasi->fotos->take(3) as $foto)
                                                <div class="position-relative">
                                                <img src="{{ asset('storage/'.$foto->foto) }}" 
                                                     alt="{{ $destinasi->nama }}" 
                                                     class="rounded shadow-sm" 
                                                     loading="lazy"
                                                     decoding="async"
                                                     style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;"
                                                     data-bs-toggle="tooltip"
                                                     title="Klik untuk memperbesar">
                                                </div>
                                            @endforeach
                                            @if($destinasi->fotos->count() > 3)
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-secondary rounded-circle" 
                                                          style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                                        +{{ $destinasi->fotos->count() - 3 }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                             style="width: 60px; height: 60px;">
                                            <i class="bi bi-image text-muted fs-4"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $destinasi->nama }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                                        <span class="text-muted">{{ $destinasi->lokasi }}</span>
                                    </div>
                                </td>
                                <td>
                                    <p class="mb-0 text-muted small" style="max-width: 250px;">
                                        {{ Str::limit($destinasi->deskripsi, 80) }}
                                    </p>
                                </td>
                                <td>
                                    <div class="btn-group shadow-sm" role="group">
                                        <a href="{{ route('destinasis.show', $destinasi) }}" 
                                           class="btn btn-sm btn-info text-white" 
                                           data-bs-toggle="tooltip"
                                           title="Lihat Detail">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                        <a href="{{ route('destinasis.edit', $destinasi) }}" 
                                           class="btn btn-sm btn-warning text-white" 
                                           data-bs-toggle="tooltip"
                                           title="Edit Data">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        <form action="{{ route('destinasis.destroy', $destinasi) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="event.preventDefault(); adminDeleteSwal({ actionUrl: '{{ route('destinasis.destroy', $destinasi) }}', itemLabel: @js($destinasi->nama), title: 'Delete Destination?', html: 'This will permanently delete <strong>' + @js($destinasi->nama) + '</strong>. This action cannot be undone.' });">
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
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                                        <h5 class="text-muted">Belum Ada Data Destinasi</h5>
                                        <p class="text-muted mb-3">Mulai tambahkan destinasi wisata pertama Anda</p>
                                        <a href="{{ route('destinasis.create') }}" class="btn btn-primary">
                                            <i class="bi bi-plus-circle me-2"></i>Tambah Destinasi
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($destinasis->hasPages())
                <div class="mt-4 d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Menampilkan {{ $destinasis->firstItem() ?? 0 }} - {{ $destinasis->lastItem() ?? 0 }} 
                        dari {{ $destinasis->total() }} destinasi
                    </div>
                    <div>
                        {{ $destinasis->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Card View -->
    <div id="cardViewContainer" style="display: none;">
        <div class="row">
            @forelse($destinasis as $destinasi)
            <div class="col-xl-4 col-lg-6 col-md-6 mb-4 destinasi-card">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <!-- Card Image with Gallery -->
                    <div class="position-relative" style="height: 250px; overflow: hidden;">
                        @if($destinasi->fotos->count() > 0)
                            <div id="carousel{{ $destinasi->id }}" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    @foreach($destinasi->fotos as $key => $foto)
                                    <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                                        <img src="{{ asset('storage/'.$foto->foto) }}" 
                                             class="d-block w-100" 
                                             alt="{{ $destinasi->nama }}"
                                             loading="lazy"
                                             decoding="async"
                                             style="height: 250px; object-fit: cover;">
                                    </div>
                                    @endforeach
                                </div>
                                @if($destinasi->fotos->count() > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#carousel{{ $destinasi->id }}" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carousel{{ $destinasi->id }}" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                </button>
                                @endif
                            </div>
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center h-100">
                                <i class="bi bi-image fs-1 text-muted"></i>
                            </div>
                        @endif
                        
                        <!-- Photo Count Badge -->
                        @if(($destinasi->fotos_count ?? $destinasi->fotos->count()) > 0)
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-dark bg-opacity-75 shadow-sm">
                                <i class="bi bi-camera-fill me-1"></i>{{ $destinasi->fotos_count ?? $destinasi->fotos->count() }} Foto
                            </span>
                        </div>
                        @endif

                        <!-- ID Badge -->
                        <div class="position-absolute top-0 start-0 m-3">
                            <span class="badge bg-primary shadow-sm">{{ $destinasi->id_destinasi }}</span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold mb-2">{{ $destinasi->nama }}</h5>
                        
                        <div class="mb-3">
                            <div class="d-flex align-items-center text-muted mb-2">
                                <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                                <small>{{ $destinasi->lokasi }}</small>
                            </div>
                        </div>

                        <p class="card-text text-muted small flex-grow-1" style="line-height: 1.6;">
                            {{ Str::limit($destinasi->deskripsi, 120) }}
                        </p>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 mt-3">
                            <a href="{{ route('destinasis.show', $destinasi) }}" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="ti ti-eye me-1"></i>Lihat Detail
                            </a>
                            <div class="btn-group" role="group">
                                <a href="{{ route('destinasis.edit', $destinasi) }}" 
                                   class="btn btn-warning btn-sm text-white">
                                    <i class="ti ti-edit me-1"></i>Edit
                                </a>
                                <form action="{{ route('destinasis.destroy', $destinasi) }}" 
                                      method="POST" 
                                      class="d-inline w-50"
                                      onsubmit="event.preventDefault(); adminDeleteSwal({ actionUrl: '{{ route('destinasis.destroy', $destinasi) }}', itemLabel: @js($destinasi->nama), title: 'Delete Destination?', html: 'This will permanently delete <strong>' + @js($destinasi->nama) + '</strong>. This action cannot be undone.' });">
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
                        <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="text-muted">Belum Ada Data Destinasi</h5>
                        <p class="text-muted mb-3">Mulai tambahkan destinasi wisata pertama Anda</p>
                        <a href="{{ route('destinasis.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Tambah Destinasi
                        </a>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pagination for Card View -->
        @if($destinasis->hasPages())
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Menampilkan {{ $destinasis->firstItem() ?? 0 }} - {{ $destinasis->lastItem() ?? 0 }} 
                        dari {{ $destinasis->total() }} destinasi
                    </div>
                    <div>
                        {{ $destinasis->links() }}
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

.badge:hover {
    transform: scale(1.1);
}

img.rounded {
    transition: all 0.3s ease;
}

img.rounded:hover {
    transform: scale(1.1);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.carousel-control-prev,
.carousel-control-next {
    width: 40px;
}

.carousel-control-prev-icon,
.carousel-control-next-icon {
    background-color: rgba(0, 0, 0, 0.5);
    border-radius: 50%;
    padding: 10px;
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

    // Search functionality (client-side filter)
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            
            // Filter table rows
            const tableRows = document.querySelectorAll('.destinasi-row');
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });

            // Filter cards
            const cards = document.querySelectorAll('.destinasi-card');
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }
});
</script>
@endsection
