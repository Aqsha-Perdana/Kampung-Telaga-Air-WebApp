@extends('landing.layout')

@section('content')
<section class="py-4" style="background: #f8f9fa; margin-top: 80px;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('landing.destinasi') }}">Destinations</a></li>
                <li class="breadcrumb-item active">{{ $destinasi->nama }}</li>
            </ol>
        </nav>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="mb-4" data-aos="fade-up">
                    <h1 class="display-5 fw-bold mb-3">{{ $destinasi->nama }}</h1>
                    <div class="d-flex align-items-center text-muted mb-3">
                        <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                        <span class="me-4">{{ $destinasi->lokasi }}</span>
                        <i class="bi bi-star-fill text-warning me-2"></i>
                        <span>4.8 (125 reviews)</span>
                    </div>
                </div>

                <div class="mb-4" data-aos="fade-up">
                    @if($destinasi->fotos->count() > 0)
                        <div class="mb-3">
                            <img src="{{ Storage::url($destinasi->fotos->first()->foto) }}" 
                                 alt="{{ $destinasi->nama }}"
                                 class="img-fluid rounded shadow-lg detail-hero-image"
                                 style="width: 100%; height: 500px; object-fit: cover;"
                                 loading="lazy">
                        </div>

                        @if($destinasi->fotos->count() > 1)
                        <div class="row g-2">
                            @foreach($destinasi->fotos->skip(1)->take(4) as $foto)
                            <div class="col-6 col-md-3">
                                <img src="{{ Storage::url($foto->foto) }}" 
                                     alt="{{ $destinasi->nama }}"
                                     class="img-fluid rounded detail-thumb-image"
                                     style="width: 100%; height: 150px; object-fit: cover; cursor: pointer;"
                                     loading="lazy"
                                     data-bs-toggle="modal" 
                                     data-bs-target="#galleryModal">
                            </div>
                            @endforeach
                            @if($destinasi->fotos->count() > 5)
                            <div class="col-6 col-md-3">
                                <div class="position-relative" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#galleryModal">
                                    <img src="{{ Storage::url($destinasi->fotos->get(5)->foto) }}" 
                                         alt="{{ $destinasi->nama }}"
                                         class="img-fluid rounded detail-thumb-image"
                                         style="width: 100%; height: 150px; object-fit: cover;"
                                         loading="lazy">
                                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-50 rounded">
                                        <span class="text-white fw-bold fs-4">+{{ $destinasi->fotos->count() - 5 }} More</span>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif
                    @else
                        <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200" 
                             alt="{{ $destinasi->nama }}"
                             class="img-fluid rounded shadow-lg detail-hero-image"
                             style="width: 100%; height: 500px; object-fit: cover;"
                             loading="lazy">
                    @endif
                </div>

                <div class="card border-0 shadow-sm" data-aos="fade-up">
                    <div class="card-body p-4">
                        <ul class="nav nav-tabs mb-4" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#deskripsi">
                                    <i class="bi bi-info-circle"></i> Description
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#fasilitas">
                                    <i class="bi bi-star"></i> Facilities
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="deskripsi">
                                <h5 class="mb-3">About {{ $destinasi->nama }}</h5>
                                <p class="text-muted" style="line-height: 1.8;">{{ $destinasi->deskripsi }}</p>
                                
                                <div class="row mt-4">
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                                <i class="bi bi-clock text-primary fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">Opening Hours</h6>
                                                <small class="text-muted">08:00 AM - 05:00 PM</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                                                <i class="bi bi-ticket-perforated text-success fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">Ticket Price</h6>
                                                <small class="text-muted">Starts from RM 10.00</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                                                <i class="bi bi-people text-warning fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">Capacity</h6>
                                                <small class="text-muted">Up to 100 people</small>
                                            </div>  
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-info bg-opacity-10 rounded-circle p-3 me-3">
                                                <i class="bi bi-calendar-check text-info fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">Availability</h6>
                                                <small class="text-muted">Open Daily</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="fasilitas">
                                <h5 class="mb-4">Available Facilities</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                            <span>Spacious Parking Area</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                            <span>Clean Restrooms</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                            <span>Prayer Room (Musholla)</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                            <span>Food Court / Eateries</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                            <span>Gazebos & Rest Areas</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                            <span>Instagrammable Photo Spots</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($paketTerkait->count() > 0)
                <div class="mt-5" data-aos="fade-up">
                    <h4 class="mb-4">Available Tour Packages</h4>
                    <div class="row g-3">
                        @foreach($paketTerkait as $paket)
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $paket->nama_paket }}</h6>
                                    <p class="text-muted small mb-2">{{ $paket->durasi_hari }} Days</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-primary fw-bold">{{ format_ringgit($paket->harga_total) }}</span>
                                        <a href="{{ route('landing.detail-paket', $paket->id_paket) }}" class="btn btn-sm btn-outline-primary">
                                            Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div class="col-lg-4">
                @if($rekomendasiDestinasi->count() > 0)
                <div class="mt-4" data-aos="fade-up">
                    <h5 class="mb-3">Other Destinations</h5>
                    @foreach($rekomendasiDestinasi as $rekomendasi)
                    <a href="{{ route('landing.detail-destinasi', $rekomendasi->id_destinasi) }}" class="text-decoration-none">
                        <div class="card mb-3 border-0 shadow-sm">
                            <div class="row g-0">
                                <div class="col-12 col-sm-4">
                                    @if($rekomendasi->fotos->count() > 0)
                                        <img src="{{ Storage::url($rekomendasi->fotos->first()->foto) }}" 
                                             class="img-fluid rounded-start h-100" 
                                             style="object-fit: cover;"
                                             alt="{{ $rekomendasi->nama }}"
                                             loading="lazy">
                                    @else
                                        <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=300" 
                                             class="img-fluid rounded-start h-100" 
                                             style="object-fit: cover;"
                                             alt="{{ $rekomendasi->nama }}"
                                             loading="lazy">
                                    @endif
                                </div>
                                <div class="col-12 col-sm-8">
                                    <div class="card-body py-2 px-3">
                                        <h6 class="card-title mb-1 text-dark">{{ $rekomendasi->nama }}</h6>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <i class="bi bi-geo-alt"></i> {{ $rekomendasi->lokasi }}
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="galleryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $destinasi->nama }} Gallery</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="galleryCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @foreach($destinasi->fotos as $index => $foto)
                        <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                            <img src="{{ Storage::url($foto->foto) }}" class="d-block w-100" alt="{{ $destinasi->nama }}" loading="lazy">
                        </div>
                        @endforeach
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
