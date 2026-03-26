@extends('landing.layout')

@section('title', $culinary->nama . ' - Seafood Restaurant')

@section('content')
<div class="culinary-detail-page">
    <!-- Breadcrumb -->
    <section class="breadcrumb-section py-3 bg-light" style="margin-top: 80px;">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('landing.culinary') }}">Culinary</a></li>
                    <li class="breadcrumb-item active">{{ $culinary->nama }}</li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- Header Section -->
    <section class="header-section py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="display-5 fw-bold mb-3">{{ $culinary->nama }}</h1>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        @if($culinary->lokasi)
                        <span class="badge bg-danger px-3 py-2">
                            <i class="bi bi-geo-alt"></i> {{ $culinary->lokasi }}
                        </span>
                        @endif
                        @if($culinary->pakets->count() > 0)
                        <span class="badge bg-success px-3 py-2">
                            <i class="bi bi-bag-check"></i> {{ $culinary->pakets->count() }} Package
                        </span>
                        @endif
                        <span class="badge bg-primary px-3 py-2">
                            <i class="bi bi-images"></i> {{ $culinary->fotos->count() }} Photo
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    @if($culinary->fotos->count() > 0)
    <section class="gallery-section pb-4">
        <div class="container">
            <!-- Main Image -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="main-gallery-image" style="height: 500px; overflow: hidden; border-radius: 15px;">
                        <img src="{{ asset('storage/' . $culinary->fotos->first()->foto) }}" 
                             class="w-100 h-100" 
                             style="object-fit: cover;"
                             alt="{{ $culinary->nama }}"
                             id="mainImage"
                             loading="lazy">
                    </div>
                </div>
            </div>

            <!-- Thumbnail Gallery -->
            @if($culinary->fotos->count() > 1)
            <div class="row g-2">
                @foreach($culinary->fotos as $index => $foto)
                <div class="col-6 col-md-3 col-lg-2">
                    <div class="thumbnail-item {{ $index == 0 ? 'active' : '' }}" 
                         style="height: 100px; overflow: hidden; border-radius: 10px; cursor: pointer; border: 3px solid transparent;"
                         onclick="changeMainImage('{{ asset('storage/' . $foto->foto) }}', this)">
                        <img src="{{ asset('storage/' . $foto->foto) }}" 
                             class="w-100 h-100" 
                             style="object-fit: cover;"
                             alt="{{ $culinary->nama }}"
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
                                <i class="bi bi-info-circle text-primary"></i> About Restaurant
                            </h4>
                            <p class="text-muted mb-0">{{ $culinary->deskripsi }}</p>
                        </div>
                    </div>

                    <!-- Paket Menu -->
                    @if($culinary->pakets->count() > 0)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h4 class="fw-bold mb-4">
                                <i class="bi bi-basket text-primary"></i> Available Menu Packages
                            </h4>
                            
                            <div class="row g-3">
                                @foreach($culinary->pakets as $paket)
                                <div class="col-md-6">
                                    <div class="card border h-100">
                                        <div class="card-body">
                                            <h5 class="card-title fw-bold">{{ $paket->nama_paket }}</h5>
                                            
                                            @if($paket->kapasitas)
                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    <i class="bi bi-people"></i> For {{ $paket->kapasitas }} People
                                                </small>
                                            </div>
                                            @endif

                                            @if($paket->deskripsi_paket)
                                            <p class="card-text text-muted small">{{ $paket->deskripsi_paket }}</p>
                                            @endif

                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                <div>
                                                    <h4 class="text-primary mb-0">
                                                         {{ format_ringgit($paket->harga) }}
                                                    </h4>
                                                    <small class="text-muted">per package</small>
                                                </div>
                                                <a href="https://wa.me/6281234567890?text=Halo, saya ingin pesan {{ urlencode($paket->nama_paket) }} di {{ urlencode($culinary->nama) }}" 
                                                   class="btn btn-sm btn-success" 
                                                   target="_blank">
                                                    <i class="bi bi-whatsapp"></i> Order
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Features -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h4 class="fw-bold mb-4">
                                <i class="bi bi-star text-primary"></i> Advantages
                            </h4>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-fish text-primary fs-4 me-3"></i>
                                        <div>
                                            <strong>Fresh Seafood</strong>
                                            <p class="text-muted small mb-0">Fresh fish and seafood every day</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-fire text-primary fs-4 me-3"></i>
                                        <div>
                                            <strong>Traditional Cuisine</strong>
                                            <p class="text-muted small mb-0">Authentic regional recipes</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-emoji-smile text-primary fs-4 me-3"></i>
                                        <div>
                                            <strong>Comfortable Atmosphere</strong>
                                            <p class="text-muted small mb-0">A pleasant and clean place to eat</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-cash-coin text-primary fs-4 me-3"></i>
                                        <div>
                                            <strong>Affordable Prices</strong>
                                            <p class="text-muted small mb-0">Affordable prices for everyone</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Contact Card -->
                    <div class="card border-0 shadow sticky-top mb-4" style="top: 100px;">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4 text-center">Contact Us</h5>

                            <!-- Quick Info -->
                            <div class="mb-4">
                                @if($culinary->lokasi)
                                <div class="d-flex align-items-start mb-3">
                                    <i class="bi bi-geo-alt text-primary fs-5 me-3"></i>
                                    <div>
                                        <small class="text-muted d-block">Location</small>
                                        <strong>{{ $culinary->lokasi }}</strong>
                                    </div>
                                </div>
                                @endif

                                <div class="d-flex align-items-start mb-3">
                                    <i class="bi bi-clock text-primary fs-5 me-3"></i>
                                    <div>
                                        <small class="text-muted d-block">Operating Hours</small>
                                        <strong>Every Day<br>8:00 AM - 8:00 PM</strong>
                                    </div>
                                </div>

                                @if($culinary->pakets->count() > 0)
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-cash-stack text-primary fs-5 me-3"></i>
                                    <div>
                                        <small class="text-muted d-block">Starting Price</small>
                                        <strong class="text-primary">
                                        {{ format_ringgit($culinary->pakets->min('harga')) }}
                                        </strong>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <hr class="my-4">

                            <!-- Contact Buttons -->
                            <div class="d-grid gap-2">
                                <a href="https://wa.me/6281234567890?text=Halo, saya tertarik dengan {{ urlencode($culinary->nama) }}" 
                                   class="btn btn-success btn-lg" 
                                   target="_blank">
                                    <i class="bi bi-whatsapp"></i> Chat WhatsApp
                                </a>

                                <a href="tel:081234567890" class="btn btn-outline-primary">
                                    <i class="bi bi-telephone"></i> Direct Call
                                </a>

                                @if($culinary->lokasi)
                                <a href="https://www.google.com/maps/search/{{ urlencode($culinary->lokasi) }}" 
                                   class="btn btn-outline-danger" 
                                   target="_blank">
                                    <i class="bi bi-map"></i> See on Maps
                                </a>
                                @endif
                            </div>

                            <!-- Share -->
                            <div class="mt-4 text-center">
                                <small class="text-muted d-block mb-2">Share:</small>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ url()->current() }}" 
                                       target="_blank" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-facebook"></i>
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?url={{ url()->current() }}&text={{ $culinary->nama }}" 
                                       target="_blank" 
                                       class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-twitter"></i>
                                    </a>
                                    <button onclick="copyLink()" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-link-45deg"></i>
                                    </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Culinaries -->
@if($relatedCulinaries->count() > 0)
<section class="related-section py-5 bg-light">
    <div class="container">
        <h3 class="fw-bold mb-4">Another Restaurant</h3>
        <div class="row g-4">
            @foreach($relatedCulinaries as $related)
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

                        @if($related->lokasi)
                        <span class="position-absolute top-0 start-0 m-2 badge bg-danger">
                            <i class="bi bi-geo-alt"></i> {{ $related->lokasi }}
                        </span>
                        @endif
                    </div>
                    <div class="card-body">
                        <h5 class="card-title fw-bold">{{ $related->nama }}</h5>
                        <p class="text-muted small">{{ Str::limit($related->deskripsi, 80) }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            @if($related->pakets->count() > 0)
                            <div>
                                <small class="text-muted d-block">Start From</small>
                                <strong class="text-primary">{{ format_ringgit($related->pakets->min('harga')) }}</strong>
                            </div>
                            @else
                            <div></div>
                            @endif
                            <a href="{{ route('landing.culinary.show', $related->id_culinary) }}" class="btn btn-sm btn-primary">
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
        alert('Link berhasil disalin!');
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