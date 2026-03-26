@extends('landing.layout')

@section('title', $homestay->nama . ' - Homestay')

@section('content')
<div class="homestay-detail-page">
    <!-- Breadcrumb -->
    <section class="breadcrumb-section py-3 bg-light" style="margin-top: 80px;">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('landing.homestay') }}">Homestay</a></li>
                    <li class="breadcrumb-item active">{{ $homestay->nama }}</li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- Header Section -->
    <section class="header-section py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h1 class="display-5 fw-bold mb-2">{{ $homestay->nama }}</h1>
                            <p class="text-muted mb-0">ID: {{ $homestay->id_homestay }}</p>
                        </div>
                        @if($homestay->is_active)
                        <span class="badge bg-success px-3 py-2 fs-6">
                            <i class="bi bi-check-circle"></i> Available
                        </span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-primary px-3 py-2">
                            <i class="bi bi-people"></i> Capacity {{ $homestay->kapasitas }} People
                        </span>
                        @if($homestay->paketWisatas->count() > 0)
                        <span class="badge bg-info px-3 py-2">
                            <i class="bi bi-bookmark-check"></i> {{ $homestay->paketWisatas->count() }} Tour Package
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Image Section -->
    @if($homestay->foto)
    <section class="image-section pb-4">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="main-image" style="height: 500px; overflow: hidden; border-radius: 15px;">
                        <img src="{{ asset('storage/' . $homestay->foto) }}" 
                             class="w-100 h-100" 
                             style="object-fit: cover;"
                             alt="{{ $homestay->nama }}"
                             loading="lazy">
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- Detail Section -->
    <section class="detail-section py-4">
        <div class="container">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Tentang Homestay -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h4 class="fw-bold mb-3">
                                <i class="bi bi-house-door text-primary"></i> About Homestay
                            </h4>
                            <p class="text-muted">
                                {{ $homestay->nama }} is a comfortable and strategically located homestay in Kampung Telaga Air. 
                                With a capacity of up to {{ $homestay->kapasitas }} people, this homestay is suitable for families 
                                or groups who want to enjoy a family atmosphere and the comfort of their own home.
                            </p>
                        </div>
                    </div>

                    <!-- Fasilitas -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h4 class="fw-bold mb-4">
                                <i class="bi bi-star text-primary"></i> Complete Facilities
                            </h4>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="facility-item p-3 border rounded">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-wifi text-primary fs-4 me-3"></i>
                                            <div>
                                                <strong>Free WiFi </strong>
                                                <p class="text-muted small mb-0">24-hour high-speed internet</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="facility-item p-3 border rounded">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-snow text-primary fs-4 me-3"></i>
                                            <div>
                                                <strong>Air Conditioner</strong>
                                                <p class="text-muted small mb-0">Cool and comfortable room</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="facility-item p-3 border rounded">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-droplet text-primary fs-4 me-3"></i>
                                            <div>
                                                <strong>Ensuite Bathroom</strong>
                                                <p class="text-muted small mb-0">Hot & cold water</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="facility-item p-3 border rounded">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-tv text-primary fs-4 me-3"></i>
                                            <div>
                                                <strong>TV & Entertainment</strong>
                                                <p class="text-muted small mb-0">Cable TV is available</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="facility-item p-3 border rounded">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-cup-hot text-primary fs-4 me-3"></i>
                                            <div>
                                                <strong>Shared Kitchen</strong>
                                                <p class="text-muted small mb-0">Complete equipment</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="facility-item p-3 border rounded">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-p-circle text-primary fs-4 me-3"></i>
                                            <div>
                                                <strong>Free Parking</strong>
                                                <p class="text-muted small mb-0">Spacious parking area</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="facility-item p-3 border rounded">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-moon-stars text-primary fs-4 me-3"></i>
                                            <div>
                                                <strong>Lounge</strong>
                                                <p class="text-muted small mb-0">Porch & family room</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="facility-item p-3 border rounded">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-lock text-primary fs-4 me-3"></i>
                                            <div>
                                                <strong>24-Hour Security</strong>
                                                <p class="text-muted small mb-0">Safe environment</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paket Wisata yang Menggunakan -->
                    @if($homestay->paketWisatas->count() > 0)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h4 class="fw-bold mb-4">
                                <i class="bi bi-bookmark-check text-primary"></i> Included in the Tour Package
                            </h4>
                            
                            <div class="row g-3">
                                @foreach($homestay->paketWisatas as $paket)
                                <div class="col-12">
                                    <div class="card border">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h5 class="fw-bold mb-2">{{ $paket->nama_paket }}</h5>
                                                    <p class="text-muted small mb-2">{{ Str::limit($paket->deskripsi, 100) }}</p>
                                                    <div class="d-flex gap-3">
                                                        <span class="badge bg-primary">
                                                            <i class="bi bi-calendar-check"></i> {{ $paket->durasi_hari }} Day
                                                        </span>
                                                        <span class="badge bg-info">
                                                            <i class="bi bi-moon"></i> {{ $paket->pivot->jumlah_malam }} Night
                                                        </span>
                                                    </div>
                                                </div>
                                                <a href="{{ route('landing.detail-paket', $paket->id_paket) }}" 
                                                   class="btn btn-outline-primary btn-sm ms-3">See Package
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
                    <!-- House Rules -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-4">
                            <i class="bi bi-exclamation-circle text-primary"></i> Homestay Rules
                        </h4>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="bi bi-check-circle"></i> Permitted
                                </h6>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i> Check-in: 2:00 PM</li>
                                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i> Check-out: 12:00 PM</li>
                                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i> Bringing children</li>
                                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i> Cooking together in the kitchen</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-danger fw-bold mb-3">
                                    <i class="bi bi-x-circle"></i> Not allowed
                                </h6>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="bi bi-x text-danger me-2"></i> Smoking indoors</li>
                                    <li class="mb-2"><i class="bi bi-x text-danger me-2"></i> Bringing pets</li>
                                    <li class="mb-2"><i class="bi bi-x text-danger me-2"></i> Noisy party or event</li>
                                    <li class="mb-2"><i class="bi bi-x text-danger me-2"></i> Damaging facilities</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Booking Card -->
                <div class="card border-0 shadow sticky-top mb-4" style="top: 100px;">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h3 class="text-primary fw-bold mb-1">
                            {{ format_ringgit($homestay->harga_per_malam) }}
                            </h3>
                            <small class="text-muted">per night</small>
                        </div>

                        <hr class="my-4">

                        <!-- Quick Info -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-people text-primary fs-5 me-3"></i>
                                <div>
                                    <small class="text-muted d-block">Max Capacity</small>
                                    <strong>{{ $homestay->kapasitas }} People</strong>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-house-door text-primary fs-5 me-3"></i>
                                <div>
                                    <small class="text-muted d-block">Type</small>
                                    <strong>Family Homestay</strong>
                                </div>
                            </div>

                            <div class="d-flex align-items-center">
                                <i class="bi bi-shield-check text-primary fs-5 me-3"></i>
                                <div>
                                    <small class="text-muted d-block">Status</small>
                                    <strong class="text-success">Available</strong>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Contact Buttons -->
                        <div class="d-grid gap-2">
                            <button onclick="bookNow()" class="btn btn-success btn-lg">
                                <i class="bi bi-whatsapp"></i> Book via WhatsApp
                            </button>

                            <a href="tel:081234567890" class="btn btn-outline-primary">
                                <i class="bi bi-telephone"></i> Call Directly
                            </a>
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
                                <a href="https://twitter.com/intent/tweet?url={{ url()->current() }}&text={{ $homestay->nama }}" 
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
    </div>
</section>

<!-- Related Homestays -->
@if($relatedHomestays->count() > 0)
<section class="related-section py-5 bg-light">
    <div class="container">
        <h3 class="fw-bold mb-4">More Homestays</h3>
        <div class="row g-4">
            @foreach($relatedHomestays as $related)
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 hover-lift">
                    <div class="position-relative overflow-hidden" style="height: 200px;">
                        @if($related->foto)
                            <img src="{{ asset('storage/' . $related->foto) }}" 
                                 class="card-img-top h-100 w-100" 
                                 style="object-fit: cover;" 
                                 alt="{{ $related->nama }}"
                                 loading="lazy">
                        @else
                            <div class="h-100 w-100 d-flex align-items-center justify-content-center bg-light">
                                <i class="bi bi-house-door text-muted" style="font-size: 3rem;"></i>
                            </div>
                        @endif

                        <span class="position-absolute top-0 start-0 m-2 badge bg-primary">
                            <i class="bi bi-people"></i> {{ $related->kapasitas }} People
                        </span>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title fw-bold">{{ $related->nama }}</h5>
                        <p class="text-muted small mb-3">Capacity for up to {{ $related->kapasitas }} guests with complete facilities</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">Price / Night</small>
                                <strong class="text-primary">{{  format_ringgit($related->harga_per_malam) }}</strong>
                            </div>
                            <a href="{{ route('landing.homestay.show', $related->id_homestay) }}" class="btn btn-sm btn-primary">
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
function bookNow() {
    const checkin = document.getElementById('checkin').value;
    const checkout = document.getElementById('checkout').value;
    const guests = document.getElementById('guests').value;
    
    if (!checkin || !checkout) {
        alert('Please select the check-in and check-out dates first.');
        return;
    }
    
    const message = `Hello, I would like to book *{{ $homestay->nama }}*

Check-in: ${checkin}
Check-out: ${checkout}
Number of guests: ${guests}
Price: {{  format_ringgit($homestay->harga_per_malam) }}/night

Please let me know the availability and confirmation. Thank you.`;
    
    const whatsappUrl = `https://wa.me/6281234567890?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank');
}

function copyLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        alert('Link copied successfully!');
    });
}

// Set minimum checkout date based on checkin
document.getElementById('checkin').addEventListener('change', function() {
    const checkinDate = new Date(this.value);
    checkinDate.setDate(checkinDate.getDate() + 1);
    const minCheckout = checkinDate.toISOString().split('T')[0];
    document.getElementById('checkout').setAttribute('min', minCheckout);
});
</script>
<style>
.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}

.facility-item {
    transition: all 0.3s ease;
}

.facility-item:hover {
    background-color: #f8f9fa;
    border-color: #0d6efd !important;
}

@media print {
    .navbar, .breadcrumb-section, .related-section, .cta-section {
        display: none !important;
    }
}
</style>
@endsection

