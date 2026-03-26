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
                                <div class="small mt-1 text-white-50"><i class="bi bi-people-fill"></i> {{ $paket->capacity_badge_label }}</div>
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
                            <div class="col-6 col-md-3">
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
                                            <div class="col-12 col-sm-5">
                                                <img src="{{ $destinasi->fotos->count() > 0 ? asset('storage/'.$destinasi->fotos->first()->foto) : 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400' }}" class="img-fluid h-100 object-fit-cover" alt="{{ $destinasi->nama }}" loading="lazy">
                                            </div>
                                            <div class="col-12 col-sm-7">
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
                                    <li>Full payment is required during checkout</li>
                                    <li>Payment confirmation is completed online via Stripe</li>
                                    <li>Approved refund requests are subject to a fixed 10% refund fee</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="small text-muted">
                                    <li>Net refund amount is 90% of the paid ticket value</li>
                                    <li>Prices may change during holiday season</li>
                                    <li>{{ $paket->participant_range_label }}</li>
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
                               min="{{ max((int) ($paket->minimum_participants ?? 1), 1) }}"
                               @if($paket->maximum_participants) max="{{ $paket->maximum_participants }}" @endif
                               data-min-participants="{{ max((int) ($paket->minimum_participants ?? 1), 1) }}"
                               data-max-participants="{{ $paket->maximum_participants }}"
                               value="{{ max((int) ($paket->minimum_participants ?? 1), 1) }}" 
                               {{ Auth::guest() ? 'disabled' : 'required' }}>
                        <small class="text-muted">{{ $paket->participant_range_label }}. Participant count is used for planning and capacity only.</small>
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
                            <span class="fw-bold">{{ format_ringgit($paket->harga_final) }} per package</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Booking Rule:</span>
                            <span class="fw-semibold">{{ $paket->participant_range_label }}</span>
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
                                <i class="bi bi-lock-fill"></i> Log In to Book Package
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
                <p class="small text-muted mb-3">Please log in to book this package and enjoy exclusive member benefits.</p>
                <div class="d-grid gap-2">
                    <a href="{{ route('wisatawan.login') }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-box-arrow-in-right"></i> Log In Now
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

