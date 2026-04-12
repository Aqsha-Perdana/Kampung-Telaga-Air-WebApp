@extends('landing.layout')

@section('title', 'Homestay - Kampung Telaga Air')

@section('content')
<div class="homestay-page">
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
                <div class="col-lg-3 mb-3 mb-lg-0">
                    <h5 class="mb-0">{{ $homestays->total() }} Homestay</h5>
                </div>
                <div class="col-lg-9">
                    <form action="{{ route('landing.homestay') }}" method="GET">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="text" 
                                       name="search" 
                                       class="form-control" 
                                       placeholder="Search homestay..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="kapasitas" class="form-select">
                                    <option value="">Capacity</option>
                                    <option value="2" {{ request('kapasitas') == '2' ? 'selected' : '' }}>Min 2 people</option>
                                    <option value="4" {{ request('kapasitas') == '4' ? 'selected' : '' }}>Min 4 people</option>
                                    <option value="6" {{ request('kapasitas') == '6' ? 'selected' : '' }}>Min 6 people</option>
                                    <option value="8" {{ request('kapasitas') == '8' ? 'selected' : '' }}>Min 8 people</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="sort" class="form-select">
                                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Newest</option>
                                    <option value="harga_murah" {{ request('sort') == 'harga_murah' ? 'selected' : '' }}>Lowest Price</option>
                                    <option value="harga_mahal" {{ request('sort') == 'harga_mahal' ? 'selected' : '' }}>Higher Price</option>
                                    <option value="kapasitas" {{ request('sort') == 'kapasitas' ? 'selected' : '' }}>Maximum Capacity</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('landing.homestay') }}" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Homestay Grid -->
    <section class="homestay-section py-5">
        <div class="container">
            <div class="row g-4">
                @forelse($homestays as $homestay)
                <div class="col-md-6 col-lg-4">
                    <div class="card homestay-card h-100 shadow-sm border-0 hover-lift">
                        <!-- Image -->
                        <div class="position-relative overflow-hidden" style="height: 250px;">
                            @if($homestay->foto)
                                <img src="{{ Storage::url($homestay->foto) }}" 
                                     class="card-img-top h-100 w-100" 
                                     style="object-fit: cover;" 
                                     alt="{{ $homestay->nama }}"
                                     loading="lazy"
                                     decoding="async"
                                     onerror="this.onerror=null; this.src='{{ asset('assets/images/default-homestay.jpg') }}';">
                            @else
                                <div class="h-100 w-100 d-flex align-items-center justify-content-center" 
                                     style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                    <div class="text-center text-white">
                                        <i class="bi bi-house-door" style="font-size: 3rem;"></i>
                                        <p class="mt-2 mb-0 px-3">{{ $homestay->nama }}</p>
                                    </div>
                                </div>
                            @endif

                            <!-- Badge Kapasitas -->
                            <span class="position-absolute top-0 start-0 m-3 badge bg-primary">
                                <i class="bi bi-people"></i> {{ $homestay->kapasitas }} People
                            </span>

                            <!-- Badge Active -->
                            @if($homestay->is_active)
                            <span class="position-absolute top-0 end-0 m-3 badge bg-success">
                                <i class="bi bi-check-circle"></i> Available
                            </span>
                            @endif
                        </div>

                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title fw-bold mb-0">{{ $homestay->nama }}</h5>
                                <small class="text-muted">{{ $homestay->id_homestay }}</small>
                            </div>

                            <!-- Info -->
                            <div class="mb-3 flex-grow-1">
                                <div class="d-flex align-items-center text-muted small mb-2">
                                    <i class="bi bi-people me-2"></i>
                                    <span>Capacity: {{ $homestay->kapasitas }} people</span>
                                </div>
                                @if(($homestay->paket_wisatas_count ?? 0) > 0)
                                <div class="d-flex align-items-center text-muted small mb-2">
                                    <i class="bi bi-bookmark-check me-2"></i>
                                    <span>Included in {{ $homestay->paket_wisatas_count }} Tour Package</span>
                                </div>
                                @endif
                                <div class="d-flex align-items-center text-success small">
                                    <i class="bi bi-shield-check me-2"></i>
                                    <span>Complete and clean facilities</span>
                                </div>
                            </div>

                            <!-- Price & Button -->
                            <div class="d-flex justify-content-between align-items-center mt-auto border-top pt-3">
                                <div>
                                    <small class="text-muted d-block">Price/Night</small>
                                    <h4 class="text-primary fw-bold mb-0">
                                        {{ format_ringgit($homestay->harga_per_malam) }}
                                    </h4>
                                </div>
                                <a href="{{ route('landing.homestay.show', $homestay->id_homestay) }}" class="btn btn-primary">
                                    Detail <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-info text-center py-5" role="alert">
                        <i class="bi bi-house-door fs-1 d-block mb-3"></i>
                        <h5>No Homestays Yet</h5>
                        <p class="mb-0">There are currently no homestays available. Please check back later.</p>
                    </div>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($homestays->hasPages())
            <div class="row mt-5">
                <div class="col-12">
                    <div class="d-flex justify-content-center">
                        {{ $homestays->appends(request()->query())->links() }}
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
                        <i class="bi bi-house-heart text-primary fs-1 mb-3"></i>
                        <h5 class="fw-bold">Cozy atmosphere</h5>
                        <p class="text-muted mb-0">Like being at home with maximum comfort</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <div class="info-card">
                        <i class="bi bi-shield-check text-primary fs-1 mb-3"></i>
                        <h5 class="fw-bold">Safe & Clean</h5>
                        <p class="text-muted mb-0">Cleanliness and safety maintained 24/7</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-card">
                        <i class="bi bi-cash-stack text-primary fs-1 mb-3"></i>
                        <h5 class="fw-bold">Affordable Prices</h5>
                        <p class="text-muted mb-0">Quality accommodation at affordable prices</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@endsection
