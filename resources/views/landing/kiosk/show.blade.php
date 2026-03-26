@extends('landing.layout')

@section('title', $kiosk->nama . ' - Vendor Kiosk')

@section('content')

<div class="kiosk-detail-page">
    <!-- Breadcrumb -->
    <section class="breadcrumb-section py-3 bg-light" style="margin-top: 80px;">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('landing.kiosk') }}">Vendor Kiosks</a></li>
                    <li class="breadcrumb-item active">{{ $kiosk->nama }}</li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- Header Section -->
    <section class="header-section py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="display-5 fw-bold mb-3">{{ $kiosk->nama }}</h1>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        @if($kiosk->kapasitas)
                        <span class="badge bg-info px-3 py-2">
                            <i class="bi bi-people"></i> Capacity {{ $kiosk->kapasitas }}
                        </span>
                        @endif
                        <span class="badge bg-primary px-3 py-2">
                            <i class="bi bi-images"></i> {{ $kiosk->fotos->count() }} Photos
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    @if($kiosk->fotos->count() > 0)
    <section class="gallery-section pb-4">
        <div class="container">
            <!-- Main Image -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="main-gallery-image" style="height: 500px; overflow: hidden; border-radius: 15px;">
                        <img src="{{ asset('storage/' . $kiosk->fotos->first()->foto) }}" 
                             class="w-100 h-100" 
                             style="object-fit: cover;"
                             alt="{{ $kiosk->nama }}"
                             id="mainImage"
                             loading="lazy">
                    </div>
                </div>
            </div>

            <!-- Thumbnail Gallery -->
            @if($kiosk->fotos->count() > 1)
            <div class="row g-2">
                @foreach($kiosk->fotos as $index => $foto)
                <div class="col-6 col-md-3 col-lg-2">
                    <div class="thumbnail-item {{ $index == 0 ? 'active' : '' }}" 
                         style="height: 100px; overflow: hidden; border-radius: 10px; cursor: pointer; border: 3px solid transparent;"
                         onclick="changeMainImage('{{ asset('storage/' . $foto->foto) }}', this)">
                        <img src="{{ asset('storage/' . $foto->foto) }}" 
                             class="w-100 h-100" 
                             style="object-fit: cover;"
                             alt="{{ $kiosk->nama }}"
                             loading="lazy">
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </section>
    @endif

    <!-- Detail Section -->
    <section class="detail-section py-4">
        <div class="container">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Description -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h4 class="fw-bold mb-3">
                                <i class="bi bi-info-circle text-primary"></i> About This Kiosk
                            </h4>
                            <p class="text-muted mb-0">{{ $kiosk->deskripsi }}</p>
                        </div>
                    </div>

                    <!-- Features -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h4 class="fw-bold mb-4">
                                <i class="bi bi-check-circle text-primary"></i> Facilities & Services
                            </h4>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-shop text-primary fs-4 me-3"></i>
                                        <div>
                                            <strong>Permanent Kiosk</strong>
                                            <p class="text-muted small mb-0">Fixed selling space</p>
                                        </div>
                                    </div>
                                </div>
                                @if($kiosk->kapasitas)
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-people text-primary fs-4 me-3"></i>
                                        <div>
                                            <strong>Capacity {{ $kiosk->kapasitas }}</strong>
                                            <p class="text-muted small mb-0">Can serve many visitors</p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-cash-coin text-primary fs-4 me-3"></i>
                                        <div>
                                            <strong>Cash Payment</strong>
                                            <p class="text-muted small mb-0">Easy payment system</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-shield-check text-primary fs-4 me-3"></i>
                                        <div>
                                            <strong>Trusted Products</strong>
                                            <p class="text-muted small mb-0">Quality guaranteed</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h4 class="fw-bold mb-4">
                                <i class="bi bi-lightbulb text-primary"></i> Important Information
                            </h4>
                            
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                Prices may change at any time depending on product availability
                            </div>

                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                For bulk orders, please contact us in advance
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Price Card -->
                    <div class="card border-0 shadow sticky-top mb-4" style="top: 100px;">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <h3 class="text-primary fw-bold mb-1">
                                     {{  format_ringgit($kiosk->harga_per_paket) }}
                                </h3>
                                <small class="text-muted">per package</small>
                            </div>

                            <hr class="my-4">

                            <!-- Quick Info -->
                            <div class="mb-4">
                                @if($kiosk->kapasitas)
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-people text-primary fs-5 me-3"></i>
                                    <div>
                                        <small class="text-muted d-block">Capacity</small>
                                        <strong>{{ $kiosk->kapasitas }} people</strong>
                                    </div>
                                </div>
                                @endif

                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-images text-primary fs-5 me-3"></i>
                                    <div>
                                        <small class="text-muted d-block">Product Gallery</small>
                                        <strong>{{ $kiosk->fotos->count() }} Photos</strong>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center">
                                    <i class="bi bi-clock text-primary fs-5 me-3"></i>
                                    <div>
                                        <small class="text-muted d-block">Operating Hours</small>
                                        <strong>Daily</strong>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Contact Buttons -->
                            <div class="d-grid gap-2">
                                <a href="https://wa.me/6281234567890?text=Hello, I'm interested in {{ urlencode($kiosk->nama) }}" 
                                   class="btn btn-success btn-lg" 
                                   target="_blank">
                                    <i class="bi bi-whatsapp"></i> Contact via WhatsApp
                                </a>

                                <a href="tel:081234567890" class="btn btn-outline-primary">
                                    <i class="bi bi-telephone"></i> Call Directly
                                </a>

                                <button class="btn btn-outline-secondary" onclick="window.print()">
                                    <i class="bi bi-printer"></i> Print Info
                                </button>
                            </div>

                            <!-- Share -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Kiosks -->
    @if($relatedKiosks->count() > 0)
    <section class="related-section py-5 bg-light">
        <div class="container">
            <h3 class="fw-bold mb-4">Other Kiosks</h3>
            <div class="row g-4">
                @foreach($relatedKiosks as $related)
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0 hover-lift">
                        <div class="position-relative overflow-hidden" style="height: 200px;">
                            @php
                                $relatedFoto = $related->fotos->first();
                            @endphp

                            @if($relatedFoto && $relatedFoto->foto)
                                <img src="{{ asset('storage/' . $relatedFoto->foto) }}" 
                                     class="card-img-top h-100 w-100" 
                                     style="object-fit: cover;" 
                                     alt="{{ $related->nama }}"
                                     loading="lazy">
                            @else
                                <div class="h-100 w-100 d-flex align-items-center justify-content-center bg-light">
                                    <i class="bi bi-shop text-muted" style="font-size: 3rem;"></i>
                                </div>
                            @endif
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold">{{ $related->nama }}</h5>
                            <p class="text-muted small">{{ Str::limit($related->deskripsi, 80) }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block">Price/Package</small>
                                    <strong class="text-primary">{{  format_ringgit($related->harga_per_paket) }}</strong>
                                </div>
                                <a href="{{ route('landing.kiosk.show', $related->id_kiosk) }}" class="btn btn-sm btn-primary">
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
</div>

<script>
function changeMainImage(src, element) {
    document.getElementById('mainImage').src = src;
    
    // Remove active class from all thumbnails
    document.querySelectorAll('.thumbnail-item').forEach(item => {
        item.style.borderColor = 'transparent';
        item.classList.remove('active');
    });
    
    // Add active class to clicked thumbnail
    element.style.borderColor = '#0d6efd';
    element.classList.add('active');
}

function copyLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        alert('Link copied successfully!');
    });
}
</script>

<style>
.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}

.thumbnail-item {
    transition: all 0.3s ease;
}

.thumbnail-item:hover {
    border-color: #0d6efd !important;
    transform: scale(1.05);
}

.thumbnail-item.active {
    border-color: #0d6efd !important;
}

@media print {
    .navbar, .breadcrumb-section, .related-section, .cta-section {
        display: none !important;
    }
}
</style>
@endsection