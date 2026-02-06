@extends('landing.layout')

@section('content')
<!-- Breadcrumb -->
<section class="py-4" style="background: #f8f9fa; margin-top: 80px;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('landing.paket-wisata') }}">Packages</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $paket->nama_paket }}</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Paket Detail -->
<section class="paket-detail">
    <div class="container">
        <div class="row g-4">
            <!-- Left Content -->
            <div class="col-lg-8">
                <!-- Hero Card -->
                <div class="card mb-4" data-aos="fade-up">
                    <div class="card-header-primary">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                            <div>
                                <h1 class="h2 mb-2 text-black">{{ $paket->nama_paket }}</h1>
                                <div class="meta-group">
                                    <span class="meta-item text-black"><i class="bi bi-calendar-event"></i> {{ $paket->durasi_hari }} Days {{ $paket->durasi_hari - 1 }} Nights</span>   
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge {{ $paket->status == 'aktif' ? 'bg-success' : 'bg-danger' }} mb-2">
                                    <i class="bi bi-{{ $paket->status == 'aktif' ? 'check' : 'x' }}-circle"></i> {{ $paket->status == 'aktif' ? 'Available' : 'Not Available' }}
                                </span>
                                @if($paket->diskon_nominal > 0 || $paket->diskon_persen > 0)
                                    <div class="text-decoration-line-through small mb-1">{{ format_ringgit(amount: $paket->harga_jual) }}</div>
                                    <span class="badge bg-danger mb-2">SAVE {{ $paket->diskon_persen > 0 ?  format_ringgit($paket->diskon_persen).'%' : format_ringgit($paket->diskon_nominal) }}</span>
                                @endif
                                <div class="h3 mb-0">{{ format_ringgit($paket->harga_final) }}</div>
                                <small class="text-white-50">per package</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">{{ $paket->deskripsi ?? 'Enjoy an unforgettable travel experience with our complete package specially prepared for you.' }}</p>
                        <div class="row g-3">
                            @foreach([
                                ['icon' => 'geo-alt-fill', 'value' => $paket->destinasis->count(), 'label' => 'Destinations'],
                                ['icon' => 'house-heart-fill', 'value' => $paket->homestays->count(), 'label' => 'Homestay'],
                                ['icon' => 'cup-hot-fill', 'value' => $paket->paketCulinaries->count(), 'label' => 'Culinary'],
                                ['icon' => 'water', 'value' => $paket->boats->count(), 'label' => 'Boat'],
                                ['icon' => 'shop', 'value' => $paket->kiosks->count(), 'label' => 'Kiosk']
                            ] as $stat)
                            <div class="col-3">
                                <div class="stat-box text-center">
                                    <i class="bi bi-{{ $stat['icon'] }} fs-4 text-primary"></i>
                                    <div class="h4 mb-0">{{ $stat['value'] }}</div>
                                    <small class="text-muted">{{ $stat['label'] }}</small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Itinerary -->
                @if($paket->itineraries->count() > 0)
                <div class="card mb-4" data-aos="fade-up">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="bi bi-calendar-check-fill text-primary"></i> Travel Itinerary</h4>
                    </div>
                    <div class="card-body">
                        @foreach($paket->itineraries->sortBy('hari_ke') as $itinerary)
                        <div class="d-flex gap-3 mb-3">
                            <div class="timeline-badge">{{ $itinerary->hari_ke }}</div>
                            <div class="flex-grow-1">
                                <div class="bg-light p-3 rounded">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="text-primary mb-0">{{ $itinerary->judul_hari }}</h5>
                                        <span class="badge bg-light text-dark border">Day {{ $itinerary->hari_ke }}</span>
                                    </div>
                                    <p class="text-muted mb-0 small">{{ $itinerary->deskripsi_kegiatan }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Isi Paket -->
                <div class="card mb-4" data-aos="fade-up">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="bi bi-box-seam-fill text-primary"></i> Package Contents</h4>
                    </div>
                    <div class="card-body">
                        <!-- Destinasi -->
                        @if($paket->destinasis->count() > 0)
                        <div class="mb-4">
                            <h5 class="mb-3"><i class="bi bi-geo-alt-fill text-info"></i> Tourist Destinations <span class="badge bg-info">{{ $paket->destinasis->count() }}</span></h5>
                            <div class="row g-3">
                                @foreach($paket->destinasis as $destinasi)
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="row g-0">
                                            <div class="col-5">
                                                <img src="{{ $destinasi->fotos->count() > 0 ? asset('storage/'.$destinasi->fotos->first()->foto) : 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400' }}" class="img-fluid h-100 object-fit-cover" alt="{{ $destinasi->nama }}">
                                            </div>
                                            <div class="col-7">
                                                <div class="card-body p-2">
                                                    <h6 class="mb-1">{{ $destinasi->nama }}</h6>
                                                    <p class="small text-muted mb-2"><i class="bi bi-geo-alt"></i> {{ $destinasi->lokasi }}</p>
                                                    <span class="badge bg-info">Day {{ $destinasi->pivot->hari_ke }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Homestay -->
                        @if($paket->homestays->count() > 0)
                        <div class="mb-4">
                            <h5 class="mb-3"><i class="bi bi-house-heart-fill text-success"></i> Homestay Accommodation <span class="badge bg-success">{{ $paket->homestays->count() }}</span></h5>
                            <div class="row g-3">
                                @foreach($paket->homestays as $homestay)
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex gap-2 align-items-start">
                                                <div class="icon-box bg-success"><i class="bi bi-house-door"></i></div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">{{ $homestay->nama }}</h6>
                                                    <div class="small text-muted mb-2">
                                                        <i class="bi bi-people"></i> {{ $homestay->kapasitas }} people | 
                                                        <span class="text-success">{{ format_ringgit($homestay->harga_per_malam) }}/night</span>
                                                    </div>
                                                    <span class="badge bg-success">{{ $homestay->pivot->jumlah_malam }} Nights</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Kuliner -->
                        @if($paket->paketCulinaries->count() > 0)
                        <div class="mb-4">
                            <h5 class="mb-3"><i class="bi bi-cup-hot-fill text-warning"></i> Culinary Package <span class="badge bg-warning">{{ $paket->paketCulinaries->count() }}</span></h5>
                            <div class="row g-3">
                                @foreach($paket->paketCulinaries as $paketCulinary)
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex gap-2 align-items-start">
                                                <div class="icon-box bg-warning"><i class="bi bi-cup-straw"></i></div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">{{ $paketCulinary->culinary->nama }}</h6>
                                                    <span class="badge bg-warning text-dark mb-2">{{ $paketCulinary->nama_paket }}</span>
                                                    <div class="small text-muted mb-2">
                                                        <i class="bi bi-people"></i> {{ $paketCulinary->kapasitas }} portions | 
                                                        <span class="text-success">{{ format_ringgit($paketCulinary->harga) }}</span>
                                                    </div>
                                                    @if($paketCulinary->deskripsi_paket)
                                                        <p class="small text-muted mb-2">{{ Str::limit($paketCulinary->deskripsi_paket, 80) }}</p>
                                                    @endif
                                                    <span class="badge bg-warning">Day {{ $paketCulinary->pivot->hari_ke }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Boat -->
                        @if($paket->boats->count() > 0)
                        <div class="mb-4">
                            <h5 class="mb-3"><i class="bi bi-water text-primary"></i> Boat Transportation <span class="badge bg-primary">{{ $paket->boats->count() }}</span></h5>
                            <div class="row g-3">
                                @foreach($paket->boats as $boat)
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex gap-2 align-items-start">
                                                <div class="icon-box bg-primary"><i class="bi bi-water"></i></div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">{{ $boat->nama }}</h6>
                                                    <div class="small text-muted mb-2">
                                                        <i class="bi bi-people"></i> {{ $boat->kapasitas }} passengers | 
                                                        <span class="text-success">{{ format_ringgit($boat->harga_sewa) }}</span>
                                                    </div>
                                                    <span class="badge bg-primary">Day {{ $boat->pivot->hari_ke }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Kiosk -->
                        @if($paket->kiosks->count() > 0)
                        <div class="mb-0">
                            <h5 class="mb-3"><i class="bi bi-shop text-secondary"></i> Kiosk Visit <span class="badge bg-secondary">{{ $paket->kiosks->count() }}</span></h5>
                            <div class="row g-3">
                                @foreach($paket->kiosks as $kiosk)
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex gap-2 align-items-start">
                                                <div class="icon-box bg-secondary"><i class="bi bi-shop-window"></i></div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">{{ $kiosk->nama }}</h6>
                                                    <div class="small text-muted mb-2">
                                                        <i class="bi bi-people"></i> {{ $kiosk->kapasitas }} people | 
                                                        <span class="text-success">{{ format_ringgit($kiosk->harga_per_paket) }}</span>
                                                    </div>
                                                    <span class="badge bg-secondary">Day {{ $kiosk->pivot->hari_ke }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Include & Exclude -->
                <div class="row g-3 mb-4" data-aos="fade-up">
                    <div class="col-md-6">
                        <div class="card border-success h-100">
                            <div class="card-body">
                                <h5 class="text-success mb-3"><i class="bi bi-check-circle-fill"></i> Included</h5>
                                <ul class="list-unstyled small">
                                    @foreach(['Entrance tickets to all tourist destinations', 'Homestay accommodation as per package', 'Meals as per itinerary', 'Boat transportation between islands', 'Professional tour guide', 'Photo & video documentation', 'Travel insurance', 'Bottled water during the trip'] as $item)
                                    <li class="mb-2"><i class="bi bi-check-lg text-success"></i> {{ $item }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-danger h-100">
                            <div class="card-body">
                                <h5 class="text-danger mb-3"><i class="bi bi-x-circle-fill"></i> Not Included</h5>
                                <ul class="list-unstyled small">
                                    @foreach(['Flight tickets/transportation to starting location', 'Personal expenses outside the package', 'Additional activities outside itinerary', 'Tips for guide (optional)', 'Homestay room upgrade', 'Laundry and telephone'] as $item)
                                    <li class="mb-2"><i class="bi bi-x-lg text-danger"></i> {{ $item }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms -->
                <div class="card mb-4" data-aos="fade-up">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle-fill text-primary"></i> Terms & Conditions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="small text-muted">
                                    <li>Minimum booking 3 days before departure</li>
                                    <li>Minimum down payment 50% of total package price</li>
                                    <li>Full payment maximum 1 day before departure</li>
                                    <li>Cancellation 2 days before gets 50% refund</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="small text-muted">
                                    <li>Cancellation 1 day before or on departure day gets no refund</li>
                                    <li>Prices may change during holiday season</li>
                                    <li>Minimum 2 participants for package execution</li>
                                    <li>Participants must bring official identification</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="col-lg-4">
    <div class="sticky-top" style="top: 100px;">
        <!-- Booking Card -->
        <div class="card mb-3" data-aos="fade-up">
            <div class="card-header-primary text-center">
                <h5 class="mb-0"><i class="bi bi-cart-check"></i> Book This Package</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('cart.add') }}" method="POST" id="addToCartForm">
                    @csrf
                    <input type="hidden" name="id_paket" value="{{ $paket->id_paket }}">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="bi bi-people-fill text-primary"></i> Number of Participants</label>
                        <input type="number" 
                               class="form-control" 
                               name="jumlah_peserta" 
                               min="1" 
                               value="1" 
                               {{ Auth::guest() ? 'disabled' : 'required' }}>
                        <small class="text-muted">Minimum 1 people for departure</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="bi bi-calendar-event text-primary"></i> Departure Date</label>
                        <input type="date" 
                               class="form-control" 
                               name="tanggal_keberangkatan" 
                               min="{{ date('Y-m-d', strtotime('+3 days')) }}" 
                               {{ Auth::guest() ? 'disabled' : 'required' }}>
                        <small class="text-muted">Minimum 3 days from today</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="bi bi-chat-left-text text-primary"></i> Notes (Optional)</label>
                        <textarea class="form-control" 
                                  name="catatan" 
                                  rows="3" 
                                  placeholder="Special requests, food allergies, etc..."
                                  {{ Auth::guest() ? 'disabled' : '' }}></textarea>
                    </div>

                    <div class="bg-light p-3 rounded mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Package Price:</span>
                            <span class="fw-bold">{{ format_ringgit($paket->harga_final) }}</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Total:</span>
                            <span class="h5 text-primary mb-0">{{ format_ringgit($paket->harga_final) }}</span>
                        </div>
                    </div>

                    @if($paket->status == 'aktif')
                        @guest
                            <!-- Tombol untuk Guest (Belum Login) -->
                            <a href="{{ route('wisatawan.login') }}" class="btn btn-primary w-100 mb-2">
                                <i class="bi bi-lock-fill"></i> Login to Book Package
                            </a>
                            <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#inquiryModal">
                                <i class="bi bi-whatsapp"></i> Ask via WhatsApp
                            </button>
                        @else
                            <!-- Tombol untuk User yang Sudah Login -->
                            <button type="submit" class="btn btn-primary w-100 mb-2">
                                <i class="bi bi-cart-plus"></i> Add to Cart
                            </button>
                            <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#inquiryModal">
                                <i class="bi bi-whatsapp"></i> Ask via WhatsApp
                            </button>
                        @endguest
                    @else
                        <button type="button" class="btn btn-secondary w-100 mb-2" disabled>
                            <i class="bi bi-x-circle"></i> Package Not Available
                        </button>
                    @endif
                </form>

                @auth
                    <a href="{{ route('cart.index') }}" class="btn btn-outline-primary w-100 mb-3">
                        <i class="bi bi-cart3"></i> View Cart
                    </a>
                @endauth

                <div class="border-top pt-3">
                    @foreach([['icon' => 'shield-check', 'text' => 'Secure Payment'], ['icon' => 'arrow-clockwise', 'text' => 'Refund Policy'], ['icon' => 'headset', 'text' => '24/7 Support']] as $trust)
                    <div class="d-flex align-items-center gap-2 mb-2 small text-muted">
                        <i class="bi bi-{{ $trust['icon'] }} text-primary"></i>
                        <span>{{ $trust['text'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Login Notice Card (Only for Guest) -->
        @guest
        <div class="card mb-3 border-warning" data-aos="fade-up">
            <div class="card-body text-center">
                <i class="bi bi-info-circle text-warning" style="font-size: 2.5rem;"></i>
                <h6 class="mt-3 mb-2">Login Required</h6>
                <p class="small text-muted mb-3">Please login to book this package and enjoy exclusive member benefits!</p>
                <div class="d-grid gap-2">
                    <a href="{{ route('wisatawan.login') }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-box-arrow-in-right"></i> Login Now
                    </a>
                    <a href="{{ route('wisatawan.register') }}" class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-person-plus"></i> Create Account
                    </a>
                </div>
            </div>
        </div>
        @endguest

        <!-- Info Card -->
        <div class="card mb-3" data-aos="fade-up">
            <div class="card-body">
                <h6 class="mb-2"><i class="bi bi-question-circle text-primary"></i> Need Help?</h6>
                <p class="small text-muted mb-3">Our team is ready to help you anytime</p>
                @foreach([
                    ['url' => 'https://wa.me/6281234567890', 'class' => 'success', 'icon' => 'whatsapp', 'text' => 'Chat WhatsApp'],
                    ['url' => 'mailto:info@wisata.com', 'class' => 'outline-primary', 'icon' => 'envelope', 'text' => 'Email Us'],
                    ['url' => 'tel:+6281234567890', 'class' => 'outline-secondary', 'icon' => 'telephone', 'text' => 'Call']
                ] as $contact)
                <a href="{{ $contact['url'] }}" class="btn btn-{{ $contact['class'] }} btn-sm w-100 mb-2" {{ Str::contains($contact['url'], 'http') ? 'target="_blank"' : '' }}>
                    <i class="bi bi-{{ $contact['icon'] }}"></i> {{ $contact['text'] }}
                </a>
                @endforeach
            </div>
        </div>

        <!-- Why Choose Us -->
        <div class="card" data-aos="fade-up">
            <div class="card-body">
                <h6 class="mb-3"><i class="bi bi-star text-warning"></i> Why Choose Us?</h6>
                <ul class="list-unstyled small">
                    @foreach(['Affordable & transparent prices', 'Experienced guide', 'Comfortable accommodation', 'Professional documentation', 'Responsive customer service'] as $reason)
                    <li class="mb-2"><i class="bi bi-check2-circle text-success"></i> {{ $reason }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
        </div>
    </div>
</section>

<!-- Inquiry Modal -->
<div class="modal fade" id="inquiryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-whatsapp"></i> Contact Us</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Fill the form below or directly chat our WhatsApp</p>
                <form action="https://wa.me/6281234567890" method="GET" target="_blank">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Name</label>
                        <input type="text" class="form-control" name="text" required placeholder="Enter your name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">WhatsApp Number</label>
                        <input type="tel" class="form-control" required placeholder="08xx xxxx xxxx">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Number of Participants</label>
                        <input type="number" class="form-control" min="1" required placeholder="How many people?">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Message</label>
                        <textarea class="form-control" rows="3" placeholder="Ask anything about this package...">Hello, I'm interested in the {{ $paket->nama_paket }} package. Please provide more information.</textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100"><i class="bi bi-whatsapp"></i> Send via WhatsApp</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>

.paket-detail { padding: 2rem 0; background: #f8fafc; }
.card { border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.card-header-primary { background: #ffffffff; color: white; padding: 1.25rem; }
.meta-group { display: flex; flex-wrap: wrap; gap: 0.75rem; }
.meta-item { display: inline-flex; align-items-center; gap: 0.4rem; background: rgba(255,255,255,0.2); padding: 0.4rem 0.8rem; border-radius: 4px; font-size: 0.9rem; }
.stat-box { background: #f8fafc; padding: 1rem; border-radius: 6px; }
.timeline-badge { width: 50px; height: 50px; background: #2563eb; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem; font-weight: 700; flex-shrink: 0; }
.icon-box { width: 40px; height: 40px; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem; flex-shrink: 0; }
<style>
/* Additional Styles for Auth Check */
.card.border-warning {
    border-width: 2px;
    background: linear-gradient(to bottom, #fffbf0 0%, #ffffff 100%);
}

/* Disabled Input Style */
input:disabled, textarea:disabled {
    background-color: #f8f9fa;
    cursor: not-allowed;
    opacity: 0.6;
}

/* Login Button Animation */
.btn-primary[href*="login"],
.btn-warning {
    position: relative;
    overflow: hidden;
}

.btn-primary[href*="login"]::before,
.btn-warning::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-primary[href*="login"]:hover::before,
.btn-warning:hover::before {
    width: 300px;
    height: 300px;
}

/* Pulse Animation for Login Card */
@keyframes pulse-warning {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4);
    }
    50% {
        box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
    }
}

.card.border-warning {
    animation: pulse-warning 2s infinite;
}

/* Lock Icon Animation */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.btn-primary[href*="login"]:hover i {
    animation: shake 0.5s ease;
}


/* Toast Notification Styles */
.toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    z-index: 9999;
    min-width: 350px;
    max-width: 400px;
    animation: slideInRight 0.4s ease-out;
    border-left: 4px solid #10b981;
}

.toast-notification.error {
    border-left-color: #ef4444;
}

.toast-notification.warning {
    border-left-color: #f59e0b;
}

@keyframes slideInRight {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

.toast-notification.hiding {
    animation: slideOutRight 0.3s ease-in forwards;
}

.toast-header-custom {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.toast-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.toast-icon.success {
    background: #d1fae5;
    color: #10b981;
}

.toast-icon.error {
    background: #fee2e2;
    color: #ef4444;
}

.toast-icon.warning {
    background: #fef3c7;
    color: #f59e0b;
}

.toast-body-custom {
    margin-left: 52px;
}

.toast-title {
    font-weight: 600;
    font-size: 16px;
    margin-bottom: 4px;
    color: #1f2937;
}

.toast-message {
    font-size: 14px;
    color: #6b7280;
    line-height: 1.5;
}

.toast-close {
    position: absolute;
    top: 15px;
    right: 15px;
    background: none;
    border: none;
    font-size: 20px;
    color: #9ca3af;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s;
}

.toast-close:hover {
    background: #f3f4f6;
    color: #4b5563;
}

.toast-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    background: #10b981;
    border-radius: 0 0 0 12px;
    animation: progressBar 4s linear forwards;
}

.toast-progress.error {
    background: #ef4444;
}

.toast-progress.warning {
    background: #f59e0b;
}

@keyframes progressBar {
    from { width: 100%; }
    to { width: 0%; }
}

.toast-actions {
    margin-top: 12px;
    margin-left: 52px;
    display: flex;
    gap: 8px;
}

.toast-btn {
    padding: 6px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.toast-btn-primary {
    background: #2563eb;
    color: white;
}

.toast-btn-primary:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
}

.toast-btn-secondary {
    background: #f3f4f6;
    color: #4b5563;
}

.toast-btn-secondary:hover {
    background: #e5e7eb;
}

/* Loading Overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9998;
    animation: fadeIn 0.2s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.loading-spinner {
    width: 60px;
    height: 60px;
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Confetti Animation */
@keyframes confetti {
    0% { transform: translateY(0) rotate(0deg); opacity: 1; }
    100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
}

.confetti-piece {
    position: fixed;
    width: 10px;
    height: 10px;
    background: #2563eb;
    top: -10px;
    z-index: 10000;
    animation: confetti 3s ease-out forwards;
}

/* Cart Badge Animation */
@keyframes cartBounce {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
}

.cart-badge-animate {
    animation: cartBounce 0.5s ease;
}
</style>

<script>
document.getElementById('addToCartForm')?.addEventListener('submit', function(e) {
        @guest
            e.preventDefault();
            alert('Silakan login terlebih dahulu untuk melakukan booking!');
            window.location.href = "{{ route('wisatawan.login') }}";
        @endguest
    });

    // Show tooltip when trying to interact with disabled inputs
    document.querySelectorAll('input:disabled, textarea:disabled').forEach(function(element) {
        element.addEventListener('click', function() {
            @guest
                const tooltip = document.createElement('div');
                tooltip.className = 'alert alert-warning alert-dismissible fade show position-fixed';
                tooltip.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
                tooltip.innerHTML = `
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <i class="bi bi-exclamation-triangle"></i> 
                    <strong>Login Required!</strong><br>
                    <small>Please login to access booking form</small>
                `;
                document.body.appendChild(tooltip);
                
                setTimeout(() => {
                    tooltip.remove();
                }, 3000);
            @endguest
        });
    });

document.querySelector('input[name="jumlah_peserta"]').addEventListener('input', function() {
    const basePrice = {{ $paket->harga_final }};
    const totalPrice = basePrice * this.value;
    console.log('Total: RM ' + totalPrice.toFixed(2));
});

document.getElementById('addToCartForm').addEventListener('submit', function(e) {
    const participants = document.querySelector('input[name="jumlah_peserta"]').value;
    const date = document.querySelector('input[name="tanggal_keberangkatan"]').value;
    
    if (participants < 1) { e.preventDefault(); alert('Minimum 1 participant required'); return false; }
    if (!date) { e.preventDefault(); alert('Please select departure date'); return false; }
    
    const selectedDate = new Date(date);
    const minDate = new Date();
    minDate.setDate(minDate.getDate() + 3);
    
    if (selectedDate < minDate) { e.preventDefault(); alert('Booking must be at least 3 days from today'); return false; }
});

function showToast(options) {
    const {
        type = 'success',
        title = 'Notifikasi',
        message = '',
        duration = 4000,
        showActions = false,
        actions = []
    } = options;

    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    
    const iconMap = {
        success: '✓',
        error: '✕',
        warning: '⚠'
    };

    toast.innerHTML = `
        <button class="toast-close" onclick="closeToast(this)">×</button>
        <div class="toast-header-custom">
            <div class="toast-icon ${type}">
                ${iconMap[type]}
            </div>
            <div class="toast-title">${title}</div>
        </div>
        <div class="toast-body-custom">
            <div class="toast-message">${message}</div>
            ${showActions ? `
                <div class="toast-actions">
                    ${actions.map(action => `
                        <button class="toast-btn ${action.class}" onclick="${action.onclick}">
                            ${action.icon ? `<i class="bi bi-${action.icon}"></i> ` : ''}${action.text}
                        </button>
                    `).join('')}
                </div>
            ` : ''}
        </div>
        <div class="toast-progress ${type}"></div>
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        closeToast(toast.querySelector('.toast-close'));
    }, duration);

    return toast;
}

function closeToast(button) {
    const toast = button.closest('.toast-notification');
    if (toast) {
        toast.classList.add('hiding');
        setTimeout(() => toast.remove(), 300);
    }
}

function createConfetti() {
    const colors = ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
    for (let i = 0; i < 30; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti-piece';
        confetti.style.left = Math.random() * 100 + '%';
        confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.animationDelay = Math.random() * 0.5 + 's';
        confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
        document.body.appendChild(confetti);
        
        setTimeout(() => confetti.remove(), 3000);
    }
}

function animateCartBadge() {
    const badges = document.querySelectorAll('.badge, .position-absolute.badge');
    badges.forEach(badge => {
        badge.classList.add('cart-badge-animate');
        setTimeout(() => badge.classList.remove('cart-badge-animate'), 500);
    });
}

function showLoading() {
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'loading-overlay';
    loadingOverlay.id = 'cartLoadingOverlay';
    loadingOverlay.innerHTML = '<div class="loading-spinner"></div>';
    document.body.appendChild(loadingOverlay);
    return loadingOverlay;
}

function hideLoading() {
    const loadingOverlay = document.getElementById('cartLoadingOverlay');
    if (loadingOverlay) {
        loadingOverlay.remove();
    }
}

// ===== FORM SUBMISSION HANDLER =====

const addToCartForm = document.getElementById('addToCartForm');
if (addToCartForm) {
    addToCartForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        @guest
            showToast({
                type: 'warning',
                title: 'Login Diperlukan',
                message: 'Silakan login terlebih dahulu untuk menambahkan paket ke keranjang!',
                showActions: true,
                actions: [
                    {
                        text: 'Login Sekarang',
                        class: 'toast-btn-primary',
                        icon: 'box-arrow-in-right',
                        onclick: "window.location.href='{{ route('wisatawan.login') }}'"
                    }
                ],
                duration: 5000
            });
            return false;
        @else
            // Validasi form
            const jumlahPeserta = this.querySelector('input[name="jumlah_peserta"]').value;
            const tanggalKeberangkatan = this.querySelector('input[name="tanggal_keberangkatan"]').value;
            
            if (!jumlahPeserta || jumlahPeserta < 1) {
                showToast({
                    type: 'error',
                    title: 'Validasi Gagal',
                    message: 'Jumlah peserta minimal 1 orang.',
                    duration: 3000
                });
                return false;
            }
            
            if (!tanggalKeberangkatan) {
                showToast({
                    type: 'error',
                    title: 'Validasi Gagal',
                    message: 'Silakan pilih tanggal keberangkatan.',
                    duration: 3000
                });
                return false;
            }
            
            const selectedDate = new Date(tanggalKeberangkatan);
            const minDate = new Date();
            minDate.setDate(minDate.getDate() + 3);
            
            if (selectedDate < minDate) {
                showToast({
                    type: 'error',
                    title: 'Validasi Gagal',
                    message: 'Tanggal keberangkatan minimal 3 hari dari sekarang.',
                    duration: 3000
                });
                return false;
            }

            // Show loading
            showLoading();

            // Submit form
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                if (data.success) {
                    createConfetti();
                    animateCartBadge();
                    
                    const paketName = data.paket_nama || '{{ $paket->nama_paket ?? "Paket Wisata" }}';
                    
                    showToast({
                        type: 'success',
                        title: 'Successfully added!',
                        message: `Package "${paketName}" has been added to your cart.`,
                        showActions: true,
                        actions: [
                            {
                                text: 'View Cart',
                                class: 'toast-btn-primary',
                                icon: 'cart3',
                                onclick: "window.location.href='{{ route('cart.index') }}'"
                            },
                            {
                                text: 'Continue Shopping',
                                class: 'toast-btn-secondary',
                                onclick: 'closeToast(this)'
                            }
                        ],
                        duration: 5000
                    });
                    
                    // Reset form
                    setTimeout(() => {
                        const jumlahInput = this.querySelector('input[name="jumlah_peserta"]');
                        const tanggalInput = this.querySelector('input[name="tanggal_keberangkatan"]');
                        const catatanInput = this.querySelector('textarea[name="catatan"]');
                        
                        if (jumlahInput) jumlahInput.value = 1;
                        if (tanggalInput) tanggalInput.value = '';
                        if (catatanInput) catatanInput.value = '';
                    }, 1000);
                } else {
                    showToast({
                        type: 'error',
                        title: 'Failed to Add',
                        message: data.message || 'An error occurred while adding the package to the cart.',
                        duration: 4000
                    });
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                
                showToast({
                    type: 'error',
                    title: 'An error has occurred',
                    message: 'Failed to add the package to the cart. Please try again.',
                    duration: 4000
                });
            });
        @endguest
    });
}

// ===== DISABLED INPUT HANDLER =====

document.querySelectorAll('input:disabled, textarea:disabled').forEach(function(element) {
    element.addEventListener('click', function() {
        @guest
            showToast({
                type: 'warning',
                title: 'Login Required',
                message: 'Please log in to access the order form.',
                showActions: true,
                actions: [
                    {
                        text: 'Login',
                        class: 'toast-btn-primary',
                        icon: 'box-arrow-in-right',
                        onclick: "window.location.href='{{ route('wisatawan.login') }}'"
                    }
                ],
                duration: 4000
            });
        @endguest
    });
});

// ===== PRICE UPDATE ON CHANGE =====



// ===== FLASH MESSAGES =====

@if(session('success'))
    document.addEventListener('DOMContentLoaded', function() {
        showToast({
            type: 'success',
            title: 'Berhasil!',
            message: '{{ session('success') }}',
            duration: 4000
        });
    });
@endif

@if(session('error'))
    document.addEventListener('DOMContentLoaded', function() {
        showToast({
            type: 'error',
            title: 'Gagal!',
            message: '{{ session('error') }}',
            duration: 4000
        });
    });
@endif
</script>
@endsection