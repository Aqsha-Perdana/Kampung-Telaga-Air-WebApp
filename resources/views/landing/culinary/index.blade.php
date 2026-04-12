@extends('landing.layout')

@section('title', 'Seafood Restaurant - Kampung Telaga Air')

@section('content')
<div class="culinary-page">
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
                    <h5 class="mb-0">{{ $culinaries->total() }} Restaurant</h5>
                </div>
                <div class="col-lg-9">
                    <form action="{{ route('landing.culinary') }}" method="GET">
                        <div class="row g-2">
                            <div class="col-md-5">
                                <input type="text" 
                                       name="search" 
                                       class="form-control" 
                                       placeholder="Cari restaurant..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="lokasi" class="form-select">
                                    <option value="">All Location</option>
                                    @foreach($locations as $location)
                                    <option value="{{ $location }}" {{ request('lokasi') == $location ? 'selected' : '' }}>
                                        {{ $location }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="sort" class="form-select">
                                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Newest</option>
                                    <option value="nama" {{ request('sort') == 'nama' ? 'selected' : '' }}>Name A-Z</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <div class="col-md-1">
                                <a href="{{ route('landing.culinary') }}" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Restaurant Grid -->
    <section class="culinary-section py-5">
        <div class="container">
            <div class="row g-4">
                @forelse($culinaries as $culinary)
                <div class="col-md-6 col-lg-4">
                    <div class="card culinary-card h-100 shadow-sm border-0 hover-lift">
                        <!-- Image -->
                        <div class="position-relative overflow-hidden" style="height: 250px;">
                            @php
                                $firstFoto = $culinary->fotos->first();
                            @endphp

                            @if($firstFoto && $firstFoto->foto)
                                <img src="{{ Storage::url($firstFoto->foto) }}" 
                                     class="card-img-top h-100 w-100" 
                                     style="object-fit: cover;" 
                                     alt="{{ $culinary->nama }}"
                                     loading="lazy"
                                     decoding="async"
                                     onerror="this.onerror=null; this.src='{{ asset('assets/images/default-culinary.jpg') }}';">
                            @else
                                <div class="h-100 w-100 d-flex align-items-center justify-content-center" 
                                     style="background: linear-gradient(135deg, #FA8BFF 0%, #2BD2FF 52%, #2BFF88 90%);">
                                    <div class="text-center text-white">
                                        <i class="bi bi-shop" style="font-size: 3rem;"></i>
                                        <p class="mt-2 mb-0 px-3">{{ $culinary->nama }}</p>
                                    </div>
                                </div>
                            @endif

                            <!-- Badge Lokasi -->
                            @if($culinary->lokasi)
                            <span class="position-absolute top-0 start-0 m-3 badge bg-danger">
                                <i class="bi bi-geo-alt"></i> {{ $culinary->lokasi }}
                            </span>
                            @endif

                            <!-- Badge Paket -->
                            @if(($culinary->pakets_count ?? 0) > 0)
                            <span class="position-absolute top-0 end-0 m-3 badge bg-success">
                                <i class="bi bi-tag"></i> {{ $culinary->pakets_count }} Package
                            </span>
                            @endif

                            <!-- Badge Foto Count -->
                            @if(($culinary->fotos_count ?? 0) > 1)
                            <span class="position-absolute bottom-0 end-0 m-3 badge bg-dark bg-opacity-75">
                                <i class="bi bi-images"></i> {{ $culinary->fotos_count }} Photo
                            </span>
                            @endif
                        </div>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold">{{ $culinary->nama }}</h5>
                            
                            @if($culinary->lokasi)
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="bi bi-geo-alt"></i> {{ $culinary->lokasi }}
                                </small>
                            </div>
                            @endif

                            <p class="card-text text-muted small flex-grow-1">
                                {{ Str::limit($culinary->deskripsi, 100) }}
                            </p>

                            <!-- Info -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center text-muted small mb-2">
                                    <i class="bi bi-images me-2"></i>
                                    <span>{{ $culinary->fotos_count ?? 0 }} Menu Photos</span>
                                </div>
                                @if(($culinary->pakets_count ?? 0) > 0)
                                <div class="d-flex align-items-center text-muted small">
                                    <i class="bi bi-bag-check me-2"></i>
                                    <span>{{ $culinary->pakets_count }} Package Available</span>
                                </div>
                                @endif
                            </div>

                            <!-- Button -->
                            <div class="d-flex justify-content-between align-items-center mt-auto border-top pt-3">
                                @if(($culinary->pakets_count ?? 0) > 0)
                                <div>
                                    <small class="text-muted d-block">Start From</small>
                                    <strong class="text-primary">
                                    {{ format_ringgit($culinary->pakets_min_harga ?? 0) }}
                                    </strong>
                                </div>
                                @else
                                <div>
                                    <small class="text-muted">See All Menus</small>
                                </div>
                                @endif
                                <a href="{{ route('landing.culinary.show', $culinary->id_culinary) }}" class="btn btn-primary">
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
                        <h5>No Restaurant Yet</h5>
                        <p class="mb-0">There are currently no restaurants available. Please check back later.</p>
                    </div>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($culinaries->hasPages())
            <div class="row mt-5">
                <div class="col-12">
                    <div class="d-flex justify-content-center">
                        {{ $culinaries->appends(request()->query())->links() }}
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
                        <i class="bi bi-emoji-smile text-primary fs-1 mb-3"></i>
                        <h5 class="fw-bold">Fresh Seafood</h5>
                        <p class="text-muted mb-0">Straight from local fishermen's catches</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <div class="info-card">
                        <i class="bi bi-award text-primary fs-1 mb-3"></i>
                        <h5 class="fw-bold">Authentic Taste</h5>
                        <p class="text-muted mb-0">Traditional local recipes</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-card">
                        <i class="bi bi-cash-stack text-primary fs-1 mb-3"></i>
                        <h5 class="fw-bold">Affordable Prices</h5>
                        <p class="text-muted mb-0">Enjoy culinary delights at affordable prices</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>

@endsection
