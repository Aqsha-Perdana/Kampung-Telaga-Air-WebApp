@extends('landing.layout')

@section('title', 'Tour Packages - Kampung Telaga Air')

@section('content')
<div class="paket-wisata-page">
    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
            <!-- Slides -->
            <div class="carousel-inner">
                <!-- Slide 1 -->
                <div class="carousel-item active">
                    <div class="hero-image" style="background-image: url('{{ asset('assets/images/backgrounds/bg-lp0.jpg') }}');"></div>
                    <div class="carousel-overlay"></div>
                    <div class="carousel-caption-custom text-left">
                        <div class="container" data-aos="fade-up">
                            <h1 class="hero-title">Visit Kampung</h1>
                            <h1 class="hero-title">Telaga Air</h1>
                        </div>
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="carousel-item">
                    <div class="hero-image hero-lazy" data-bg="{{ asset('assets/images/backgrounds/bg-lp1.jpg') }}"></div>
                    <div class="carousel-overlay"></div>
                    <div class="carousel-caption-custom">
                        <div class="container" data-aos="fade-up">
                            <h1 class="hero-title">Visit Kampung</h1>
                            <h1 class="hero-title">Telaga Air</h1>
                        </div>
                    </div>
                </div>

                <!-- Slide 3 -->
                <div class="carousel-item">
                    <div class="hero-image hero-lazy" data-bg="{{ asset('assets/images/backgrounds/bg-lp2.jpg') }}"></div>
                    <div class="carousel-overlay"></div>
                    <div class="carousel-caption-custom">
                        <div class="container" data-aos="fade-up">
                            <h1 class="hero-title">Visit Kampung</h1>
                            <h1 class="hero-title">Telaga Air</h1>
                        </div>
                    </div>
                </div>

                <!-- Slide 4 -->
                <div class="carousel-item">
                    <div class="hero-image hero-lazy" data-bg="{{ asset('assets/images/backgrounds/bg-lp3.jpg') }}"></div>
                    <div class="carousel-overlay"></div>
                    <div class="carousel-caption-custom">
                        <div class="container" data-aos="fade-up">
                            <h1 class="hero-title">Visit Kampung</h1>
                            <h1 class="hero-title">Telaga Air</h1>
                        </div>
                    </div>
                </div>

                <div class="carousel-item">
                    <div class="hero-image hero-lazy" data-bg="{{ asset('assets/images/backgrounds/bg-lp4.jpg') }}"></div>
                    <div class="carousel-overlay"></div>
                    <div class="carousel-caption-custom">
                        <div class="container" data-aos="fade-up">
                            <h1 class="hero-title">Visit Kampung</h1>
                            <h1 class="hero-title">Telaga Air</h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </section>

    <!-- Filter Section -->
    <section class="filter-section py-4 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-funnel text-primary"></i> 
                        Found {{ $paketWisata->total() }} Tour Packages
                    </h5>
                </div>
                <div class="col-md-6">
                    <form action="{{ route('landing.paket-wisata') }}" method="GET">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search tour packages..." value="{{ request('search') }}">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Paket Wisata Grid -->
    <section class="paket-section py-5">
        <div class="container">
            <div class="row g-4">
                @forelse($paketWisata as $index => $paket)
                @php
                    $hasDiscount = ((float) ($paket->diskon_nominal ?? 0) > 0) || ((float) ($paket->diskon_persen ?? 0) > 0);
                @endphp
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                    <div class="package-card h-100">
                        {{-- Foto Thumbnail (opsional) --}}
                        @if($paket->foto_thumbnail)
                        <div class="package-thumbnail">
                            <img src="{{ Storage::url($paket->foto_thumbnail) }}" 
                                 alt="{{ $paket->nama_paket }}" 
                                 class="w-100" 
                                 style="height: 180px; object-fit: cover;"
                                 loading="lazy"
                                 decoding="async">
                        </div>
                        @endif
                        <!-- Package Body -->
                        <div class="package-body">
                            <!-- Package Header -->
                            <div class="package-header">
                                <h4 class="package-title">{{ $paket->nama_paket }}</h4>
                                <div class="package-badges">
                                    <span class="duration-badge">
                                        <i class="bi bi-calendar-event"></i> {{ $paket->durasi_hari }} Day{{ $paket->durasi_hari > 1 ? 's' : '' }}
                                    </span>
                                    <span class="status-badge {{ $paket->status == 'aktif' ? 'status-active' : 'status-inactive' }}">
                                        <i class="bi bi-{{ $paket->status == 'aktif' ? 'check-circle' : 'x-circle' }}"></i>
                                        {{ ucfirst($paket->status) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="package-description">
                                @if($paket->deskripsi)
                                    <p class="small">{{ Str::limit($paket->deskripsi, 100) }}</p>
                                @else
                                    <p class="small text-muted">No description available</p>
                                @endif
                            </div>

                            <!-- Package Info -->
                            <div class="package-info">
                                <div class="info-item">
                                    <i class="bi bi-clock-fill text-primary"></i>
                                    <span>{{ $paket->durasi_hari }} Day {{ $paket->durasi_hari - 1 }} Night</span>
                                </div>
                                <div class="info-item">
                                    <i class="bi bi-geo-alt-fill text-success"></i>
                                    <span>{{ $paket->destinasis_count }} Destination{{ $paket->destinasis_count != 1 ? 's' : '' }}</span>
                                </div>
                                @if($paket->homestays_count > 0)
                                <div class="info-item">
                                    <i class="bi bi-house-heart-fill text-warning"></i>
                                    <span>{{ $paket->homestays_count }} Homestay</span>
                                </div>
                                @endif
                                @if($paket->boats_count > 0)
                                <div class="info-item">
                                    <i class="bi bi-water text-info"></i>
                                    <span>{{ $paket->boats_count }} Boat</span>
                                </div>
                                @endif
                            </div>

                            <!-- Price Section -->
                            <div class="package-price-section">
                                @if($hasDiscount)
                                    <div class="original-price d-flex align-items-center gap-2 flex-wrap mb-1">
                                        <small class="text-decoration-line-through text-muted">
                                            {{ format_ringgit($paket->harga_jual) }}
                                        </small>
                                        <span class="badge bg-danger ms-2">
                                            @if($paket->diskon_persen > 0)
                                                -{{ number_format($paket->diskon_persen, 0) }}%
                                            @else
                                                Discount
                                            @endif
                                        </span>
                                    </div>
                                @endif
                                <div class="package-price text-primary fw-bold">{{ format_ringgit($paket->harga_final) }}</div>
                                <small class="text-muted">per Package</small>
                            </div>

                            <!-- Action Buttons -->
                            <div class="package-actions">
                                <a href="{{ route('landing.detail-paket', $paket->id_paket) }}" 
                                   class="btn btn-detail w-100">
                                    <i class="bi bi-eye"></i> View Details
                                </a>
                                @if($paket->status == 'aktif')
                                    <a href="{{ route('landing.detail-paket', $paket->id_paket) }}" 
                                       class="btn btn-primary w-100 mt-2">
                                        <i class="bi bi-cart-plus"></i> Book Now
                                    </a>
                                @else
                                    <button class="btn btn-secondary w-100 mt-2" disabled>
                                        <i class="bi bi-x-circle"></i> Not Available
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @empty  
                <div class="col-12">
                    <div class="alert alert-info text-center py-5" role="alert" data-aos="fade-up">
                        <i class="bi bi-info-circle fs-1 d-block mb-3 text-primary"></i>
                        <h5 class="fw-bold">No Tour Packages Available</h5>
                        <p class="mb-0 text-muted">There are currently no tour packages available. Please check back later.</p>
                    </div>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($paketWisata->hasPages())
            <div class="row mt-5">
                <div class="col-12">
                    <div class="d-flex justify-content-center">
                        {{ $paketWisata->links() }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </section>
</div>
@endsection
