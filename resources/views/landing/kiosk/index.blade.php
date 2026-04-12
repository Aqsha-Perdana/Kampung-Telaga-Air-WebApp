@extends('landing.layout')

@section('title', 'Kiosk Penjual - Kampung Telaga Air')

@section('content')
<div class="kiosk-page">
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
                        <div class="mt-4">
                        </div>
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
                        <div class="mt-4">
                        </div>
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
                        <div class="mt-4">
                        </div>
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
                        <div class="mt-4">
                        </div>
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
                <div class="col-lg-4 mb-3 mb-lg-0">
                    <h5 class="mb-0">Found {{ $kiosks->total() }} Kiosk</h5>
                </div>
                <div class="col-lg-8">
                    <form action="{{ route('landing.kiosk') }}" method="GET">
                        <div class="row g-2">
                            <div class="col-md-5">
                                <input type="text" 
                                       name="search" 
                                       class="form-control" 
                                       placeholder="Cari kiosk..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="sort" class="form-select">
                                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Newest</option>
                                    <option value="harga_murah" {{ request('sort') == 'harga_murah' ? 'selected' : '' }}>Lower Price</option>
                                    <option value="harga_mahal" {{ request('sort') == 'harga_mahal' ? 'selected' : '' }}>Higher Price</option>
                                    <option value="nama" {{ request('sort') == 'nama' ? 'selected' : '' }}>Name A-Z</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('landing.kiosk') }}" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Kiosk Grid -->
    <section class="kiosk-section py-5">
        <div class="container">
            <div class="row g-4">
                @forelse($kiosks as $kiosk)
                <div class="col-md-6 col-lg-4">
                    <div class="card kiosk-card h-100 shadow-sm border-0 hover-lift">
                        <!-- Image -->
                        <div class="position-relative overflow-hidden" style="height: 250px;">
                            @php
                                $firstFoto = $kiosk->fotos->first();
                            @endphp

                            @if($firstFoto && $firstFoto->foto)
                                <img src="{{ Storage::url($firstFoto->foto) }}" 
                                     class="card-img-top h-100 w-100" 
                                     style="object-fit: cover;" 
                                     alt="{{ $kiosk->nama }}"
                                     loading="lazy"
                                     decoding="async"
                                     onerror="this.onerror=null; this.src='{{ asset('assets/images/default-kiosk.jpg') }}';">
                            @else
                                <div class="h-100 w-100 d-flex align-items-center justify-content-center" 
                                     style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                    <div class="text-center text-white">
                                        <i class="bi bi-shop" style="font-size: 3rem;"></i>
                                        <p class="mt-2 mb-0 px-3">{{ $kiosk->nama }}</p>
                                    </div>
                                </div>
                            @endif

                            <!-- Badge Kapasitas -->
                            @if($kiosk->kapasitas)
                            <span class="position-absolute top-0 end-0 m-3 badge bg-info">
                                <i class="bi bi-people"></i> Capacity {{ $kiosk->kapasitas }}
                            </span>
                            @endif

                            <!-- Badge Foto Count -->
                            @if(($kiosk->fotos_count ?? 0) > 1)
                            <span class="position-absolute bottom-0 end-0 m-3 badge bg-dark bg-opacity-75">
                                <i class="bi bi-images"></i> {{ $kiosk->fotos_count }} Photo
                            </span>
                            @endif
                        </div>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold">{{ $kiosk->nama }}</h5>
                            <p class="card-text text-muted small flex-grow-1">
                                {{ Str::limit($kiosk->deskripsi, 100) }}
                            </p>

                            <!-- Info -->
                            <div class="mb-3">
                                @if($kiosk->kapasitas)
                                <div class="d-flex align-items-center text-muted small mb-2">
                                    <i class="bi bi-people me-2"></i>
                                    <span>Capacity: {{ $kiosk->kapasitas }} people</span>
                                </div>
                                @endif
                                <div class="d-flex align-items-center text-muted small">
                                    <i class="bi bi-images me-2"></i>
                                    <span>{{ $kiosk->fotos_count ?? 0 }} Product Photo</span>
                                </div>
                            </div>

                            <!-- Price & Button -->
                            <div class="d-flex justify-content-between align-items-center mt-auto border-top pt-3">
                                <div>
                                    <small class="text-muted d-block">Price/Package</small>
                                    <h4 class="text-primary fw-bold mb-0">
                                        {{ format_ringgit($kiosk->harga_per_paket) }}
                                    </h4>
                                </div>
                                <a href="{{ route('landing.kiosk.show', $kiosk->id_kiosk) }}" class="btn btn-primary">
                                    Detail <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-info text-center py-5" role="alert">
                        <i class="bi bi-shop fs-1 d-block mb-3"></i>
                        <h5>No Kiosk Yet</h5>
                        <p class="mb-0">There are currently no kiosks available. Please check back later..</p>
                    </div>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($kiosks->hasPages())
            <div class="row mt-5">
                <div class="col-12">
                    <div class="d-flex justify-content-center">
                        {{ $kiosks->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </section>

    <!-- Info Section -->
    <section class="info-section py-5 bg-light">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4 mb-4 mb-md-0">
                    <div class="info-card">
                        <i class="bi bi-shop-window text-primary fs-1 mb-3"></i>
                        <h5 class="fw-bold">Local Products</h5>
                        <p class="text-muted mb-0">Various authentic products from Kampung Telaga Air</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <div class="info-card">
                        <i class="bi bi-cash-stack text-primary fs-1 mb-3"></i>
                        <h5 class="fw-bold">Affordable Prices</h5>
                        <p class="text-muted mb-0">Friendly prices directly from the seller</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-card">
                        <i class="bi bi-hand-thumbs-up text-primary fs-1 mb-3"></i>
                        <h5 class="fw-bold">Guaranteed Quality</h5>
                        <p class="text-muted mb-0">Quality products from local artisans</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@endsection
