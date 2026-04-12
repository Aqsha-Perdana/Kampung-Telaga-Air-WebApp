@php
    $heroDestination = $paket->destinasis->first();
    $heroDestinationPhoto = optional(optional($heroDestination)->fotos->first())->foto;
    $heroImage = $paket->foto_thumbnail
        ? Storage::url($paket->foto_thumbnail)
        : ($heroDestinationPhoto
            ? Storage::url($heroDestinationPhoto)
            : 'https://via.placeholder.com/1200x600?text=' . urlencode($paket->nama_paket));

    $minimumParticipants = max((int) ($paket->minimum_participants ?? 1), 1);
    $maximumParticipants = $paket->maximum_participants ? (int) $paket->maximum_participants : null;
    $defaultParticipants = $minimumParticipants;
    $durationDays = max((int) ($paket->durasi_hari ?? 1), 1);
    $durationNights = max($durationDays - 1, 0);
    $price = (float) ($paket->harga_final ?? $paket->harga_jual ?? 0);
    $minDepartureDate = now()->addDays(3)->format('Y-m-d');
    $participantRange = $paket->participant_range_label ?? ($minimumParticipants . ' participant' . ($minimumParticipants > 1 ? 's' : ''));
    $capacityBadge = $paket->capacity_badge_label ?? ('From ' . $minimumParticipants . ' participant' . ($minimumParticipants > 1 ? 's' : ''));
    $hasDiscount = ((float) ($paket->diskon_nominal ?? 0) > 0) || ((float) ($paket->diskon_persen ?? 0) > 0);
@endphp

<section class="package-breadcrumb py-3 border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('landing.paket-wisata') }}" class="text-decoration-none">Tour Package</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $paket->nama_paket }}</li>
            </ol>
        </nav>
    </div>
</section>

<section class="package-detail-shell py-4 py-lg-5">
    <div class="container">
        <div class="row g-4 g-xl-5">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm package-hero-card overflow-hidden mb-4">
                    <div class="card-body p-4 p-xl-5">
                        <div class="d-flex flex-column flex-xl-row gap-4 justify-content-between align-items-start mb-4">
                            <div class="flex-grow-1">
                                <span class="package-kicker mb-2">Tour Package</span>
                                <h1 class="package-title mb-3">{{ $paket->nama_paket }}</h1>
                                <div class="package-meta mb-3">
                                    <span><i class="bi bi-calendar4-week"></i> {{ $durationDays }} day{{ $durationDays > 1 ? 's' : '' }}{{ $durationNights > 0 ? ' / ' . $durationNights . ' night' . ($durationNights > 1 ? 's' : '') : '' }}</span>
                                    <span><i class="bi bi-people"></i> {{ $participantRange }}</span>
                                    <span><i class="bi bi-patch-check"></i> {{ ucfirst($paket->status) }}</span>
                                </div>
                                <p class="text-muted mb-0">
                                    {{ $paket->deskripsi ?: 'A curated travel experience with destinations, accommodation, and optional add-on resources prepared in one package.' }}
                                </p>
                            </div>

                            <div class="package-price-card text-xl-end">
                                <div class="text-muted small mb-1">Starting from</div>
                                @if($hasDiscount && (float) ($paket->harga_jual ?? 0) > $price)
                                    <div class="package-original-price mb-1">
                                        {{ format_ringgit($paket->harga_jual) }}
                                    </div>
                                @endif
                                <div class="package-price">{{ format_ringgit($price) }}</div>
                                <div class="text-muted small mt-1">per package booking</div>
                            </div>
                        </div>

                        <div class="ratio ratio-21x9 rounded-4 overflow-hidden mb-4">
                            <img src="{{ $heroImage }}" alt="{{ $paket->nama_paket }}" class="w-100 h-100 object-fit-cover">
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6 col-xl-3">
                                <div class="package-stat-card">
                                    <div class="text-muted small mb-1">Destinations</div>
                                    <div class="h4 mb-0">{{ $paket->destinasis->count() }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="package-stat-card">
                                    <div class="text-muted small mb-1">Stay options</div>
                                    <div class="h4 mb-0">{{ $paket->homestays->count() }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="package-stat-card">
                                    <div class="text-muted small mb-1">Food packages</div>
                                    <div class="h4 mb-0">{{ $paket->paketCulinaries->count() }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="package-stat-card">
                                    <div class="text-muted small mb-1">Support resources</div>
                                    <div class="h4 mb-0">{{ $paket->boats->count() + $paket->kiosks->count() }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm package-section-card mb-4">
                    <div class="card-body p-4 p-xl-5">
                        <div class="package-section-header mb-4">
                            <h4 class="mb-2">Trip itinerary</h4>
                            <p class="text-muted mb-0">A day-by-day outline of what guests can expect during the trip.</p>
                        </div>

                        @if($paket->itineraries->isNotEmpty())
                            <div class="d-flex flex-column gap-3">
                                @foreach($paket->itineraries as $itinerary)
                                    <div class="d-flex gap-3 package-timeline-item">
                                        <div class="package-timeline-badge">{{ $itinerary->hari_ke }}</div>
                                        <div class="package-muted-panel flex-grow-1">
                                            <h5 class="mb-2">{{ $itinerary->judul_hari ?: 'Day ' . $itinerary->hari_ke }}</h5>
                                            <p class="text-muted mb-0">{{ $itinerary->deskripsi_kegiatan ?: 'The detailed activity plan for this day will be shared during booking confirmation.' }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="package-muted-panel">
                                <p class="text-muted mb-0">The itinerary details are still being prepared for this package.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card border-0 shadow-sm package-section-card mb-4">
                    <div class="card-body p-4 p-xl-5">
                        <div class="package-section-header mb-4">
                            <h4 class="mb-2">What is included in this package</h4>
                            <p class="text-muted mb-0">These resources are currently linked to the package and help shape the overall experience.</p>
                        </div>

                        <div class="row g-3">
                            @foreach($paket->destinasis as $destinasi)
                                @php
                                    $destinationImage = optional($destinasi->fotos->first())->foto
                                        ? Storage::url(optional($destinasi->fotos->first())->foto)
                                        : 'https://via.placeholder.com/640x360?text=' . urlencode($destinasi->nama);
                                @endphp
                                <div class="col-md-6">
                                    <div class="package-resource-card h-100">
                                        <div class="ratio ratio-16x9">
                                            <img src="{{ $destinationImage }}" alt="{{ $destinasi->nama }}" class="w-100 h-100 object-fit-cover">
                                        </div>
                                        <div class="p-3">
                                            <div class="d-flex align-items-start gap-3">
                                                <span class="package-icon-box bg-primary"><i class="bi bi-geo-alt"></i></span>
                                                <div>
                                                    <div class="fw-semibold text-dark">{{ $destinasi->nama }}</div>
                                                    <div class="text-muted small">{{ $destinasi->lokasi ?: 'Destination included in this package' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @foreach($paket->homestays as $homestay)
                                <div class="col-md-6">
                                    <div class="package-resource-card h-100">
                                        <div class="p-3 h-100">
                                            <div class="d-flex align-items-start gap-3">
                                                <span class="package-icon-box bg-success"><i class="bi bi-house-door"></i></span>
                                                <div>
                                                    <div class="fw-semibold text-dark">{{ $homestay->nama }}</div>
                                                    <div class="text-muted small">
                                                        Capacity {{ $homestay->kapasitas ?? '-' }} guests
                                                        @if(!is_null($homestay->harga_per_malam))
                                                            • {{ format_ringgit($homestay->harga_per_malam) }}/night
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @foreach($paket->paketCulinaries as $culinaryPackage)
                                <div class="col-md-6">
                                    <div class="package-resource-card h-100">
                                        <div class="p-3 h-100">
                                            <div class="d-flex align-items-start gap-3">
                                                <span class="package-icon-box bg-warning text-dark"><i class="bi bi-fork-knife"></i></span>
                                                <div>
                                                    <div class="fw-semibold text-dark">{{ $culinaryPackage->nama_paket }}</div>
                                                    <div class="text-muted small">
                                                        {{ optional($culinaryPackage->culinary)->nama ?: 'Culinary package' }}
                                                        @if(!is_null($culinaryPackage->harga))
                                                            • {{ format_ringgit($culinaryPackage->harga) }}
                                                        @endif
                                                    </div>
                                                    @if($culinaryPackage->deskripsi_paket)
                                                        <div class="text-muted small mt-2">{{ $culinaryPackage->deskripsi_paket }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @foreach($paket->boats as $boat)
                                <div class="col-md-6">
                                    <div class="package-resource-card h-100">
                                        <div class="p-3 h-100">
                                            <div class="d-flex align-items-start gap-3">
                                                <span class="package-icon-box bg-info"><i class="bi bi-water"></i></span>
                                                <div>
                                                    <div class="fw-semibold text-dark">{{ $boat->nama }}</div>
                                                    <div class="text-muted small">
                                                        Capacity {{ $boat->kapasitas ?? '-' }} guests
                                                        @if(!is_null($boat->harga_sewa))
                                                            • {{ format_ringgit($boat->harga_sewa) }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @foreach($paket->kiosks as $kiosk)
                                <div class="col-md-6">
                                    <div class="package-resource-card h-100">
                                        <div class="p-3 h-100">
                                            <div class="d-flex align-items-start gap-3">
                                                <span class="package-icon-box bg-dark"><i class="bi bi-shop"></i></span>
                                                <div>
                                                    <div class="fw-semibold text-dark">{{ $kiosk->nama }}</div>
                                                    <div class="text-muted small">
                                                        Capacity {{ $kiosk->kapasitas ?? '-' }} guests
                                                        @if(!is_null($kiosk->harga_per_paket))
                                                            • {{ format_ringgit($kiosk->harga_per_paket) }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if(
                            $paket->destinasis->isEmpty() &&
                            $paket->homestays->isEmpty() &&
                            $paket->paketCulinaries->isEmpty() &&
                            $paket->boats->isEmpty() &&
                            $paket->kiosks->isEmpty()
                        )
                            <div class="package-muted-panel mt-3">
                                <p class="text-muted mb-0">The package resource list is not available yet.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm package-section-card h-100">
                            <div class="card-body p-4">
                                <h5 class="mb-3">Good to know before booking</h5>
                                <ul class="list-unstyled package-checklist text-muted mb-0">
                                    <li><i class="bi bi-check-circle-fill text-success"></i><span>Departure date should be at least 3 days from today.</span></li>
                                    <li><i class="bi bi-check-circle-fill text-success"></i><span>Booking availability follows the participant range of the package.</span></li>
                                    <li><i class="bi bi-check-circle-fill text-success"></i><span>You can still review your booking again in the cart before payment.</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm package-section-card h-100">
                            <div class="card-body p-4">
                                <h5 class="mb-3">Recommended for</h5>
                                <ul class="list-unstyled package-checklist text-muted mb-0">
                                    <li><i class="bi bi-dot text-primary"></i><span>Travelers looking for a ready-made itinerary.</span></li>
                                    <li><i class="bi bi-dot text-primary"></i><span>Family or group trips with clearer cost planning.</span></li>
                                    <li><i class="bi bi-dot text-primary"></i><span>Guests who prefer a smoother booking flow in one package.</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="position-sticky" style="top: 100px;">
                    <div class="card border-0 shadow-sm package-booking-card mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <div>
                                    <span class="package-kicker mb-2">Ready to book?</span>
                                    <h4 class="mb-1">Plan your trip</h4>
                                    <p class="text-muted small mb-0">Choose your date and participants, then continue to cart.</p>
                                </div>
                                <span class="package-booking-badge">{{ $capacityBadge }}</span>
                            </div>

                            <div class="package-total-box mb-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted small">Package price</div>
                                        @if($hasDiscount && (float) ($paket->harga_jual ?? 0) > $price)
                                            <div class="package-original-price mb-1">
                                                {{ format_ringgit($paket->harga_jual) }}
                                            </div>
                                        @endif
                                        <strong class="d-block fs-4 text-dark">{{ format_ringgit($price) }}</strong>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-muted small">Charged in</div>
                                        <strong class="text-primary">MYR</strong>
                                    </div>
                                </div>
                            </div>

                            <form id="addToCartForm" action="{{ route('cart.add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id_paket" value="{{ $paket->id_paket }}">

                                <div class="mb-3">
                                    <label for="jumlah_peserta" class="form-label fw-semibold">Participants</label>
                                    <input
                                        type="number"
                                        class="form-control form-control-lg"
                                        id="jumlah_peserta"
                                        name="jumlah_peserta"
                                        value="{{ $defaultParticipants }}"
                                        min="{{ $minimumParticipants }}"
                                        @if($maximumParticipants) max="{{ $maximumParticipants }}" @endif
                                        data-min-participants="{{ $minimumParticipants }}"
                                        @if($maximumParticipants) data-max-participants="{{ $maximumParticipants }}" @endif
                                        @guest disabled @endguest
                                    >
                                    <div class="form-text">
                                        {{ $participantRange }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="tanggal_keberangkatan" class="form-label fw-semibold">Departure date</label>
                                    <input
                                        type="date"
                                        class="form-control form-control-lg"
                                        id="tanggal_keberangkatan"
                                        name="tanggal_keberangkatan"
                                        min="{{ $minDepartureDate }}"
                                        @guest disabled @endguest
                                    >
                                </div>

                                <div class="mb-4">
                                    <label for="catatan" class="form-label fw-semibold">Special notes</label>
                                    <textarea
                                        class="form-control"
                                        id="catatan"
                                        name="catatan"
                                        rows="4"
                                        placeholder="Share any request or important note for your booking."
                                        @guest disabled @endguest
                                    ></textarea>
                                </div>

                                @guest
                                    <a href="{{ route('wisatawan.login') }}" class="btn btn-primary btn-lg w-100 package-primary-action mb-2">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>Log In to Book Package
                                    </a>
                                @else
                                    <button type="submit" class="btn btn-primary btn-lg w-100 package-primary-action mb-2">
                                        <i class="bi bi-cart-plus me-2"></i>Add to Cart
                                    </button>
                                @endguest

                                <a href="{{ route('cart.index') }}" class="btn btn-outline-primary btn-lg w-100 package-primary-action mb-2">
                                    <i class="bi bi-cart3 me-2"></i>View Cart
                                </a>

                                <a
                                    href="https://wa.me/6281234567890?text={{ urlencode('Hello, I am interested in the package ' . $paket->nama_paket . '. Can I get more details?') }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn btn-outline-secondary btn-lg w-100 package-primary-action"
                                >
                                    <i class="bi bi-whatsapp me-2"></i>Ask via WhatsApp
                                </a>
                            </form>

                            <div class="package-trust-note pt-3 mt-4">
                                <div class="d-flex gap-2 text-muted small mb-2">
                                    <i class="bi bi-shield-check text-success"></i>
                                    <span>Your booking details stay editable in the cart before checkout.</span>
                                </div>
                                <div class="d-flex gap-2 text-muted small mb-0">
                                    <i class="bi bi-credit-card text-primary"></i>
                                    <span>Payments are completed securely in the next step.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm package-note-card mb-4">
                        <div class="card-body p-4">
                            <h5 class="mb-3">Need help deciding?</h5>
                            <p class="text-muted mb-3">
                                This package is ideal if you want a clearer itinerary, simpler booking, and a single package price for your trip.
                            </p>
                            <ul class="list-unstyled package-checklist text-muted mb-0">
                                <li><i class="bi bi-check-circle-fill text-success"></i><span>Clear participant requirement</span></li>
                                <li><i class="bi bi-check-circle-fill text-success"></i><span>Booking summary before payment</span></li>
                                <li><i class="bi bi-check-circle-fill text-success"></i><span>Stripe and Xendit payment support</span></li>
                            </ul>
                        </div>
                    </div>

                    @guest
                        <div class="card border-warning shadow-sm package-note-card">
                            <div class="card-body p-4">
                                <h5 class="mb-2">Guest browsing mode</h5>
                                <p class="text-muted mb-3">
                                    You can explore the package details first. Log in when you are ready to save the booking to your cart.
                                </p>
                                <a href="{{ route('wisatawan.login') }}" class="btn btn-warning w-100">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Log In Now
                                </a>
                            </div>
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</section>
