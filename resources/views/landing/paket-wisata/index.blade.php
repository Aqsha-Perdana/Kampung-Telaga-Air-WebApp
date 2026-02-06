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
                    <div class="hero-image" style="background-image: url('{{ asset('assets/images/backgrounds/bg-lp1.jpg') }}');"></div>
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
                    <div class="hero-image" style="background-image: url('{{ asset('assets/images/backgrounds/bg-lp2.jpg') }}');"></div>
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
                    <div class="hero-image" style="background-image: url('{{ asset('assets/images/backgrounds/bg-lp3.jpg') }}');"></div>
                    <div class="carousel-overlay"></div>
                    <div class="carousel-caption-custom">
                        <div class="container" data-aos="fade-up">
                            <h1 class="hero-title">Visit Kampung</h1>
                            <h1 class="hero-title">Telaga Air</h1>
                        </div>
                    </div>
                </div>

                <div class="carousel-item">
                    <div class="hero-image" style="background-image: url('{{ asset('assets/images/backgrounds/bg-lp4.jpg') }}');"></div>
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
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                    <div class="package-card h-100">
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
                                    <span>{{ $paket->destinasis->count() }} Destination{{ $paket->destinasis->count() != 1 ? 's' : '' }}</span>
                                </div>
                                @if($paket->homestays->count() > 0)
                                <div class="info-item">
                                    <i class="bi bi-house-heart-fill text-warning"></i>
                                    <span>{{ $paket->homestays->count() }} Homestay</span>
                                </div>
                                @endif
                                @if($paket->boats->count() > 0)
                                <div class="info-item">
                                    <i class="bi bi-water text-info"></i>
                                    <span>{{ $paket->boats->count() }} Boat</span>
                                </div>
                                @endif
                            </div>

                            <!-- Price Section -->
                            <div class="package-price-section">
                                @if($paket->diskon_nominal > 0 || $paket->diskon_persen > 0)
                                    <div class="original-price">
                                        <small class="text-decoration-line-through">
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
                                <div class="package-price">{{ format_ringgit($paket->harga_final) }}</div>
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

<style>
/* Package Card Styling */
.package-card {
    background: #ffffff;
    border: 1px solid #e8e8e8;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
}

.package-card:hover {
    border-color: #667eea;
    box-shadow: 0 8px 30px rgba(102, 126, 234, 0.12);
    transform: translateY(-4px);
}

/* Package Header */
.package-header {
    padding-bottom: 1.5rem;
    margin-bottom: 1.5rem;
    border-bottom: 1px solid #f0f0f0;
}

.package-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 1rem;
    line-height: 1.3;
    min-height: 60px;
    display: flex;
    align-items: center;
}

.package-badges {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

/* Duration Badge */
.duration-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #f8f9fa;
    color: #495057;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.duration-badge i {
    color: #667eea;
}

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-inactive {
    background: #e9ecef;
    color: #6c757d;
}

/* Package Body */
.package-body {
    padding: 2rem;
    display: flex;
    flex-direction: column;
    flex: 1;
}

/* Description */
.package-description {
    padding-bottom: 1rem;
    border-bottom: 1px solid #f0f0f0;
    margin-bottom: 1rem;
}

.package-description p {
    color: #495057;
    line-height: 1.6;
    margin: 0;
}

/* Package Info */
.package-info {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 6px;
    font-size: 0.9rem;
    color: #495057;
}

.info-item i {
    font-size: 1.1rem;
}

/* Price Section */
.package-price-section {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    text-align: center;
}

.original-price {
    margin-bottom: 0.5rem;
}

.original-price small {
    font-size: 1rem;
    color: #999;
}

.original-price .badge {
    font-size: 0.75rem;
    padding: 0.35rem 0.7rem;
    font-weight: 600;
}

.package-price {
    font-size: 2rem;
    font-weight: 700;
    color: #1a1a1a;
    line-height: 1;
    margin: 0.5rem 0;
    letter-spacing: -1px;
}

.package-price-section small {
    color: #6c757d;
    font-size: 0.85rem;
}

/* Action Buttons */
.package-actions {
    margin-top: auto;
}

.btn-detail {
    background: #ffffff;
    color: #495057;
    border: 2px solid #e8e8e8;
    font-weight: 600;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.btn-detail:hover {
    background: #f8f9fa;
    border-color: #667eea;
    color: #667eea;
    transform: translateY(-1px);
}

.package-actions .btn-primary {
    background: linear-gradient(135deg, #acdff3 20%, #2a93cc);
    border: none;
    font-weight: 600;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.package-actions .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.package-actions .btn-secondary {
    background: #e9ecef;
    border: none;
    color: #6c757d;
    font-weight: 600;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
}

/* Filter Section */
.filter-section {
    background: #ffffff;
    border-bottom: 2px solid #f0f0f0;
}

.filter-section .input-group .form-control {
    border: 2px solid #e8e8e8;
    padding: 0.75rem 1rem;
    border-radius: 8px 0 0 8px;
}

.filter-section .input-group .form-control:focus {
    border-color: #667eea;
    box-shadow: none;
}

.filter-section .btn-primary {
    background: linear-gradient(135deg, #acdff3 20%, #2a93cc);
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 0 8px 8px 0;
    font-weight: 600;
}

/* Empty State */
.alert-info {
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 12px;
}

.alert-info i {
    opacity: 0.5;
}

/* Responsive */
@media (max-width: 768px) {
    .package-title {
        font-size: 1.25rem;
        min-height: 50px;
    }
    
    .package-price {
        font-size: 1.75rem;
    }
    
    .package-body {
        padding: 1.5rem;
    }
}
</style>
@endsection