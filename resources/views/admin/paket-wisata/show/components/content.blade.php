
<div class="container-fluid">
    <!-- Header dengan Background Image -->
    <div class="row mb-4">
        <div class="col-md-12 px-0">
            <div class="position-relative" style="height: 200px; border-radius: 10px; overflow: hidden;">
                <!-- Background Image -->
                <img src="{{ asset('assets/images/backgrounds/bg-package.png') }}" 
                     alt="Background" 
                     class="w-100 h-100" 
                     style="object-fit: cover; filter: brightness(0.6);">
                
                <!-- Overlay Text -->
                <div class="position-absolute top-50 start-50 translate-middle text-center text-white" style="z-index: 2;">
                    <h1 class="display-4 fw-bold mb-2" style="color: white !important; text-shadow: 2px 2px 8px rgba(0,0,0,0.8);">Tour Package Details</h1>
                    <p class="lead mb-0">Complete Information About Tour Package</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Breadcrumb -->
    <div class="row mb-3">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('paket-wisata.index') }}">Tour Package</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Main Info Card -->
    <div class="card border-0 shadow-lg mb-4 overflow-hidden">
        <div class="card-header bg-gradient-primary text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-luggage fs-3 me-3"></i>
                        <div>
                            <h2 class="mb-1 fw-bold text-white">{{ $paketWisata->nama_paket }}</h2>
                            <p class="mb-0 opacity-75">Premium tour package for the best experience</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="d-inline-block">
                        <span class="badge bg-white text-primary px-3 py-2 fs-6 me-2">
                            <i class="bi bi-tag me-1"></i>{{ $paketWisata->id_paket }}
                        </span>
                        @if($paketWisata->status == 'aktif')
                            <span class="badge bg-success px-3 py-2 fs-6">
                                <i class="bi bi-check-circle me-1"></i>Active
                            </span>
                        @else
                            <span class="badge bg-secondary px-3 py-2 fs-6">
                                <i class="bi bi-x-circle me-1"></i>Inactive
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <!-- Left Side -->
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-box p-3 bg-light rounded">
                                <div class="d-flex align-items-center">
                                    <div class="icon-wrapper bg-primary bg-opacity-10 p-3 rounded me-3">
                                        <i class="bi bi-calendar-range text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Travel Duration</small>
                                        <h5 class="mb-0 fw-bold">{{ $paketWisata->durasi_hari }} Days</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box p-3 bg-light rounded">
                                <div class="d-flex align-items-center">
                                    <div class="icon-wrapper bg-success bg-opacity-10 p-3 rounded me-3">
                                        <i class="bi bi-cash-stack text-success fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Cost Price</small>
                                        <h5 class="mb-0 fw-bold text-success">{{ format_ringgit($paketWisata->harga_total) }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box p-3 bg-light rounded">
                                <div class="d-flex align-items-center">
                                    <div class="icon-wrapper bg-warning bg-opacity-10 p-3 rounded me-3">
                                        <i class="bi bi-tag-fill text-warning fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Selling Price (Per Package)</small>
                                        <h5 class="mb-0 fw-bold text-warning">{{ format_ringgit($paketWisata->harga_jual) }}</h5>
                                        @if($paketWisata->tipe_diskon !== 'none')
                                            <small class="badge bg-danger">
                                                @if($paketWisata->tipe_diskon === 'nominal')
                                                    -{{ format_ringgit($paketWisata->diskon_nominal) }}
                                                @else
                                                    -{{ $paketWisata->diskon_persen }}%
                                                @endif
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box p-3 bg-light rounded">
                                <div class="d-flex align-items-center">
                                    <div class="icon-wrapper bg-info bg-opacity-10 p-3 rounded me-3">
                                        <i class="bi bi-currency-dollar text-info fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Final Price (Per Package)</small>
                                        <h5 class="mb-0 fw-bold text-info">{{ format_ringgit($paketWisata->harga_final) }}</h5>
                                        @php
                                            $profit = $paketWisata->harga_final - $paketWisata->harga_modal;
                                            $profitPersen = $paketWisata->harga_modal > 0 ? (($profit / $paketWisata->harga_modal) * 100) : 0;
                                        @endphp
                                        <small class="text-muted">
                                            Profit: {{ format_ringgit($profit) }} ({{ number_format($profitPersen, 2) }}%)
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box p-3 bg-light rounded">
                                <div class="d-flex align-items-center">
                                    <div class="icon-wrapper bg-secondary bg-opacity-10 p-3 rounded me-3">
                                        <i class="bi bi-clock-history text-secondary fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Created</small>
                                        <h6 class="mb-0">{{ $paketWisata->created_at->format('d M Y, H:i') }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box p-3 bg-light rounded">
                                <div class="d-flex align-items-center">
                                    <div class="icon-wrapper bg-primary bg-opacity-10 p-3 rounded me-3">
                                        <i class="bi bi-arrow-clockwise text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Last Updated</small>
                                        <h6 class="mb-0">{{ $paketWisata->updated_at->format('d M Y, H:i') }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box p-3 bg-light rounded">
                                <div class="d-flex align-items-center">
                                    <div class="icon-wrapper bg-dark bg-opacity-10 p-3 rounded me-3">
                                        <i class="bi bi-people-fill text-dark fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Participant Rule</small>
                                        <h6 class="mb-0 fw-bold">{{ $paketWisata->participant_range_label }}</h6>
                                        <small class="text-muted">Flat package pricing per booking</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($paketWisata->deskripsi)
                    <div class="mt-4 p-4 bg-light rounded">
                        <h6 class="fw-bold mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Package Description</h6>
                        <p class="mb-0 text-justify lh-lg">{{ $paketWisata->deskripsi }}</p>
                    </div>
                    @endif
                </div>

                <!-- Right Side - Quick Stats -->
                <div class="col-lg-4">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3"><i class="bi bi-graph-up text-primary me-2"></i>Package Summary</h6>
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-geo-alt-fill text-danger fs-5 me-2"></i>
                                    <span>Destination</span>
                                </div>
                                <span class="badge bg-danger">{{ $paketWisata->destinasis->count() }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-house-fill text-success fs-5 me-2"></i>
                                    <span>Homestay</span>
                                </div>
                                <span class="badge bg-success">{{ $paketWisata->homestays->count() }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-cup-hot-fill text-warning fs-5 me-2"></i>
                                    <span>Culinary</span>
                                </div>
                                <span class="badge bg-warning text-dark">{{ $paketWisata->paketCulinaries->count() }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-water text-info fs-5 me-2"></i>
                                    <span>Boat</span>
                                </div>
                                <span class="badge bg-info">{{ $paketWisata->boats->count() }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-shop text-secondary fs-5 me-2"></i>
                                    <span>Kiosk</span>
                                </div>
                                <span class="badge bg-secondary">{{ $paketWisata->kiosks->count() }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-people-fill text-dark fs-5 me-2"></i>
                                    <span>Participant Rule</span>
                                </div>
                                <span class="badge bg-dark">{{ $paketWisata->participant_range_label }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Package Contents -->
    <div class="row mb-4">
        <!-- Destination -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 d-flex align-items-center">
                        <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                        Tourist Destination
                        <span class="badge bg-danger ms-auto">{{ $paketWisata->destinasis->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($paketWisata->destinasis as $destinasi)
                        <div class="package-item p-3 mb-3 rounded border bg-light">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold">{{ $destinasi->nama }}</h6>
                                    <p class="text-muted mb-0 small">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $destinasi->lokasi }}
                                    </p>
                                </div>
                                <span class="badge bg-danger">Day {{ $destinasi->pivot->hari_ke }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="bi bi-inbox fs-1 text-muted mb-2 d-block"></i>
                            <p class="text-muted mb-0">No destination yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Homestay -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 d-flex align-items-center">
                        <i class="bi bi-house-fill text-success me-2"></i>
                        Homestay
                        <span class="badge bg-success ms-auto">{{ $paketWisata->homestays->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($paketWisata->homestays as $homestay)
                        <div class="package-item p-3 mb-3 rounded border bg-light">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold">{{ $homestay->nama }}</h6>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <small class="text-muted">
                                            <i class="bi bi-people-fill me-1"></i>{{ $homestay->kapasitas }} people
                                        </small>
                                        <small class="text-success fw-semibold">
                                            <i class="bi bi-cash me-1"></i>{{ format_ringgit($homestay->harga_per_malam) }}/night
                                        </small>
                                    </div>
                                </div>
                                <span class="badge bg-success">{{ $homestay->pivot->jumlah_malam }} Night{{ $homestay->pivot->jumlah_malam > 1 ? 's' : '' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="bi bi-inbox fs-1 text-muted mb-2 d-block"></i>
                            <p class="text-muted mb-0">No homestay yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Culinary -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 d-flex align-items-center">
                        <i class="bi bi-cup-hot-fill text-warning me-2"></i>
                        Culinary
                        <span class="badge bg-warning text-dark ms-auto">{{ $paketWisata->paketCulinaries->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($paketWisata->paketCulinaries as $paketCulinary)
                        <div class="package-item p-3 mb-3 rounded border bg-light">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold">{{ $paketCulinary->culinary->nama }}</h6>
                                    <p class="mb-1 small text-primary">{{ $paketCulinary->nama_paket }}</p>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <small class="text-muted">
                                            <i class="bi bi-people-fill me-1"></i>{{ $paketCulinary->kapasitas }} people
                                        </small>
                                        <small class="text-success fw-semibold">
                                            <i class="bi bi-cash me-1"></i>{{ format_ringgit($paketCulinary->harga) }}
                                        </small>
                                    </div>
                                </div>
                                <span class="badge bg-warning text-dark">Day {{ $paketCulinary->pivot->hari_ke }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="bi bi-inbox fs-1 text-muted mb-2 d-block"></i>
                            <p class="text-muted mb-0">No culinary yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Boat -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 d-flex align-items-center">
                        <i class="bi bi-water text-info me-2"></i>
                        Boat
                        <span class="badge bg-info ms-auto">{{ $paketWisata->boats->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($paketWisata->boats as $boat)
                        <div class="package-item p-3 mb-3 rounded border bg-light">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold">{{ $boat->nama }}</h6>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <small class="text-muted">
                                            <i class="bi bi-people-fill me-1"></i>{{ $boat->kapasitas }} people
                                        </small>
                                        <small class="text-success fw-semibold">
                                            <i class="bi bi-cash me-1"></i>{{ format_ringgit($boat->harga_sewa) }}
                                        </small>
                                    </div>
                                </div>
                                <span class="badge bg-info">Day {{ $boat->pivot->hari_ke }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="bi bi-inbox fs-1 text-muted mb-2 d-block"></i>
                            <p class="text-muted mb-0">No boat yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Kiosk -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 d-flex align-items-center">
                        <i class="bi bi-shop text-secondary me-2"></i>
                        Kiosk
                        <span class="badge bg-secondary ms-auto">{{ $paketWisata->kiosks->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($paketWisata->kiosks as $kiosk)
                        <div class="package-item p-3 mb-3 rounded border bg-light">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold">{{ $kiosk->nama }}</h6>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <small class="text-muted">
                                            <i class="bi bi-people-fill me-1"></i>{{ $kiosk->kapasitas }} people
                                        </small>
                                        <small class="text-success fw-semibold">
                                            <i class="bi bi-cash me-1"></i>{{ format_ringgit($kiosk->harga_per_paket) }}
                                        </small>
                                    </div>
                                </div>
                                <span class="badge bg-secondary">Day {{ $kiosk->pivot->hari_ke }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="bi bi-inbox fs-1 text-muted mb-2 d-block"></i>
                            <p class="text-muted mb-0">No kiosk yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Itinerary -->
    @if($paketWisata->itineraries->count() > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0 d-flex align-items-center">
                <i class="bi bi-calendar-check text-dark me-2"></i>
                Itinerary / Daily Schedule
                <span class="badge bg-dark ms-auto">{{ $paketWisata->itineraries->count() }} Day{{ $paketWisata->itineraries->count() > 1 ? 's' : '' }}</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="timeline">
                @foreach($paketWisata->itineraries as $key => $itinerary)
                <div class="timeline-item mb-4">
                    <div class="row">
                        <div class="col-auto">
                            <div class="timeline-badge bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 50px; height: 50px;">
                                <strong>{{ $itinerary->hari_ke }}</strong>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card border-start border-primary border-4">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-2">{{ $itinerary->judul_hari }}</h6>
                                    <p class="text-muted mb-0">{{ $itinerary->deskripsi_kegiatan }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="card border-0 shadow-sm sticky-bottom">
        <div class="card-body">
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <a href="{{ route('paket-wisata.edit', $paketWisata->id_paket) }}" class="btn btn-warning btn-lg px-4">
                    <i class="ti ti-edit me-2"></i>Edit Package
                </a>
                <a href="{{ route('paket-wisata.index') }}" class="btn btn-outline-secondary btn-lg px-4">
                    <i class="ti ti-arrow-left me-2"></i>Back
                </a>
                <form action="{{ route('paket-wisata.destroy', $paketWisata->id_paket) }}" 
                      method="POST" 
                      style="display: inline;"
                      onsubmit="return confirm('Are you sure you want to delete this tour package {{ $paketWisata->nama_paket }}? Deleted data cannot be restored!')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-lg px-4">
                        <i class="ti ti-trash me-2"></i>Delete Package
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

