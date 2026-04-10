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

<!-- About Section -->
<!-- <section class="py-5" style="background: #f8f9fa;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800" 
                     class="img-fluid rounded shadow" alt="About">
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <h2 class="section-title mb-4">About Us</h2>
                <p>Kampung Telaga Air is a traditional Malay fishing village located along the coast of Kuching, Sarawak, well-known for its rich cultural heritage and strong community spirit. Surrounded by mangroves and the South China Sea, the village has long been a center for fishing activities, with many families relying on the sea as their main source of livelihood. The name "Telaga Air" reflects the presence of freshwater wells in the past, which served as a vital resource for the community. Today, the village remains deeply connected to its roots, where fishing, seafood trading, and local markets form an important part of daily life.</p>
                <div class="row mt-4">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle p-3 me-3">
                                <i class="bi bi-award fs-4"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">5+</h5>
                                <small class="text-muted">Tourist Destinations</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-success text-white rounded-circle p-3 me-3">
                                <i class="bi bi-people fs-4"></i>
                            </div>
                            <div>
                                <h5 class="mb-0"></h5>
                                <small class="text-muted"></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section> -->

<!-- Galery 360 Section -->
 <section id="destinasi" class="py-5">
    <div class="container py-5">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="section-title">Gallery 360 Degree</h2>
            <p class="text-muted">See destinations with the 360 camera experience!</p>
        </div>
        <div class="row g-4">
            @foreach($destinasis as $destinasi)
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                @if(($destinasi->footage360_count ?? 0) > 0)
                    {{-- Link ke footage 360 pertama jika ada --}}
                    <a href="{{ route('view360.show', $destinasi->footage360->first()->id_footage360) }}" class="destination-link">
                @else
                    {{-- Link ke detail destinasi jika tidak ada footage 360 --}}
                    <a href="{{ route('landing.detail-destinasi', $destinasi->id_destinasi) }}" class="destination-link">
                @endif
                    <div class="destination-card">
                        @if($destinasi->fotos->count() > 0)
                            <img src="{{ asset('storage/'.$destinasi->fotos->first()->foto) }}" alt="{{ $destinasi->nama }}" loading="lazy" decoding="async">
                            <small class="d-none">{{ asset('storage/'.$destinasi->fotos->first()->foto) }}</small>
                            <!-- DEBUG: {{ $destinasi->fotos->first()->foto }} -->
                        @else
                            <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=600" alt="{{ $destinasi->nama }}" loading="lazy" decoding="async">
                        @endif
                        
                        {{-- Badge 360 jika ada footage --}}
                        @if(($destinasi->footage360_count ?? 0) > 0)
                            <div class="badge-360">
                                <i class="fas fa-street-view"></i> 360&deg;
                            </div>
                        @endif

                        <div class="card-body">
                            <h5 class="card-title">{{ $destinasi->nama }}</h5>
                            @if(($destinasi->footage360_count ?? 0) > 0)
                                <p class="small text-muted">
                                    <i class="fas fa-images"></i> 
                                    {{ $destinasi->footage360_count }} locations available
                                </p>
                            @endif
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>


<!-- Destinasi Section -->
<section id="destinasi" class="py-5 destinasi-section">
    <div class="wave wave-top">
    <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
        <path class="wave-1"
            d="M0,60 C240,100 480,20 720,40 960,60 1200,90 1440,50 L1440,0 L0,0 Z">
        </path>
        <path class="wave-2"
            d="M0,50 C240,80 480,40 720,60 960,80 1200,40 1440,60 L1440,0 L0,0 Z">
        </path>
    </svg>
</div>

    <div class="container py-5">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="section-title">Main Attraction</h2>
            <p class="text-muted">Explore our best travel destinations</p>
        </div>
        
        <div class="row g-4">
            @foreach($destinasis as $destinasi)
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <a href="{{ route('landing.detail-destinasi', $destinasi->id_destinasi) }}" class="destination-link">
                    <div class="destination-card">
                        @if($destinasi->fotos->count() > 0)
                            <img src="{{ asset('storage/'.$destinasi->fotos->first()->foto) }}" alt="{{ $destinasi->nama }}" loading="lazy" decoding="async">
                        @else
                            <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=600" alt="{{ $destinasi->nama }}" loading="lazy" decoding="async">
                        @endif

            <div class="card-body">
                <h5 class="card-title">{{ $destinasi->nama }}</h5>
                <p class="card-text">
                    {{ Str::limit($destinasi->deskripsi, 100) }}
                </p>
            </div>
        </div>
    </a>
</div>
            @endforeach
        </div>
    </div>

    <div class="wave wave-bottom">
    <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
        <path class="wave-1"
            d="M0,40 C240,90 480,70 720,40 960,10 1200,50 1440,20 L1440,120 L0,120 Z">
        </path>
        <path class="wave-2"
            d="M0,60 C240,30 480,50 720,70 960,90 1200,60 1440,80 L1440,120 L0,120 Z">
        </path>
    </svg>
</div>
</section>

<!-- Paket Wisata Section -->
<section id="paket" class="py-5 paket-section">
    <div class="container py-5">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="section-title text-black mb-4">Best Tour Packages</h2>
            <p class="text-muted">Choose a tour package that suits your needs and budget.</p>
        </div>
        
        <div class="row g-4">
            @forelse($paketWisatas as $paket)
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <div class="package-card">
                    <!-- Package Header -->
                    <div class="package-header text-center">
                        <h4 class="package-title">{{ $paket->nama_paket }}</h4>
                        <div class="package-duration">
                            <i class="bi bi-calendar-event"></i> 
                            <span>{{ $paket->durasi_hari }} Day {{ $paket->durasi_hari - 1 }} Night</span>
                        </div>
                    </div>

                    <!-- Package Price -->
                    @php
                        $hasDiscount = ((float) ($paket->diskon_nominal ?? 0) > 0) || ((float) ($paket->diskon_persen ?? 0) > 0);
                    @endphp
                    <div class="package-price-section text-center">
                        @if($hasDiscount)
                            <div class="original-price">
                                <small class="text-decoration-line-through">
                                    {{ format_ringgit($paket->harga_jual) }}
                                </small>
                                <span class="badge bg-danger">
                                    @if($paket->diskon_persen > 0)
                                        -{{ number_format($paket->diskon_persen, 0) }}%
                                    @else
                                        Discount
                                    @endif
                                </span>
                            </div>
                        @else
                            <div class="original-price"></div>
                        @endif
                        <div class="package-price">{{ format_ringgit($paket->harga_final) }}</div>
                        <small class="text-muted">per Package</small>
                    </div>

                    <!-- Package Description -->
                    <div class="package-description">
                        @if($paket->deskripsi)
                            <p class="small">{{ Str::limit($paket->deskripsi, 100) }}</p>
                        @else
                            <p class="small text-muted">No description available</p>
                        @endif
                    </div>

                    <!-- Package Features - ALWAYS SHOW ALL 5 ITEMS -->
                    <div class="package-features">
                        <h6 class="feature-title">
                            <i class="bi bi-patch-check-fill text-primary"></i> What You Get
                        </h6>
                        <ul class="feature-list">
                            <!-- 1. Tourist Destinations -->
                            <li class="{{ ($paket->destinasis_count ?? 0) > 0 ? '' : 'empty-feature' }}">
                                <i class="bi bi-geo-alt-fill text-primary"></i>
                                <div>
                                    @if(($paket->destinasis_count ?? 0) > 0)
                                        <strong>{{ $paket->destinasis_count }} Tourist Destinations</strong>
                                        <small class="d-block">
                                            {{ $paket->destinasis->pluck('nama')->take(2)->join(', ') }}
                                            @if(($paket->destinasis_count ?? 0) > 2)
                                                <span class="text-primary">+{{ $paket->destinasis_count - 2 }} more</span>
                                            @endif
                                        </small>
                                    @else
                                        <strong>0 Tourist Destinations</strong>
                                        <small class="d-block">No destinations included</small>
                                    @endif
                                </div>
                            </li>
                            
                            <!-- 2. Homestay -->
                            <li class="{{ ($paket->homestays_count ?? 0) > 0 ? '' : 'empty-feature' }}">
                                <i class="bi bi-house-heart-fill text-success"></i>
                                <div>
                                    @if(($paket->homestays_count ?? 0) > 0)
                                        <strong>{{ $paket->homestays_count }} Homestay</strong>
                                        <small class="d-block">
                                            @php
                                                $totalMalam = $paket->homestays->sum('pivot.jumlah_malam');
                                            @endphp
                                            {{ $totalMalam }} overnight stay
                                        </small>
                                    @else
                                        <strong>0 Homestay</strong>
                                        <small class="d-block">No accommodation included</small>
                                    @endif
                                </div>
                            </li>
                            
                            <!-- 3. Culinary -->
                            <li class="{{ ($paket->paket_culinaries_count ?? 0) > 0 ? '' : 'empty-feature' }}">
                                <i class="bi bi-cup-hot-fill text-warning"></i>
                                <div>
                                    @if(($paket->paket_culinaries_count ?? 0) > 0)
                                        <strong>{{ $paket->paket_culinaries_count }} Culinary Package</strong>
                                        <small class="d-block">Local specialties</small>
                                    @else
                                        <strong>0 Culinary Package</strong>
                                        <small class="d-block">No meals included</small>
                                    @endif
                                </div>
                            </li>
                            
                            <!-- 4. Boat Transportation -->
                            <li class="{{ ($paket->boats_count ?? 0) > 0 ? '' : 'empty-feature' }}">
                                <i class="bi bi-water text-info"></i>
                                <div>
                                    @if(($paket->boats_count ?? 0) > 0)
                                        <strong>{{ $paket->boats_count }} Boat Transportation</strong>
                                        <small class="d-block">Sea Journey</small>
                                    @else
                                        <strong>0 Boat Transportation</strong>
                                        <small class="d-block">No boat transport included</small>
                                    @endif
                                </div>
                            </li>

                            <!-- 5. Kiosk -->
                            <li class="{{ ($paket->kiosks_count ?? 0) > 0 ? '' : 'empty-feature' }}">
                                <i class="bi bi-shop text-danger"></i>
                                <div>
                                    @if(($paket->kiosks_count ?? 0) > 0)
                                        <strong>{{ $paket->kiosks_count }} Kiosk</strong>
                                        <small class="d-block">Souvenirs and gifts</small>
                                    @else
                                        <strong>0 Kiosk</strong>
                                        <small class="d-block">No souvenir shop included</small>
                                    @endif
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Itinerary Preview - ALWAYS SHOW -->
                    <div class="itinerary-preview">
                        <h6 class="feature-title">
                            <i class="bi bi-calendar-check text-success"></i> Travel Schedule
                        </h6>
                        @if(($paket->itineraries_count ?? 0) > 0)
                            <div class="itinerary-items">
                                @foreach($paket->itineraries->take(3) as $itinerary)
                                    <div class="itinerary-item-preview">
                                        <span class="badge">Day {{ $itinerary->hari_ke }}</span>
                                        <small>{{ Str::limit($itinerary->judul_hari, 30) }}</small>
                                    </div>
                                @endforeach
                                @if(($paket->itineraries_count ?? 0) > 3)
                                    <small class="text-primary">+{{ $paket->itineraries_count - 3 }} other activities</small>
                                @endif
                            </div>
                        @else
                            <div class="empty-itinerary">
                                No travel schedule available yet
                            </div>
                        @endif
                    </div>

                    <!-- Package Status -->
                    <div class="package-status">
                        @if($paket->status == 'aktif')
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Available
                            </span>
                        @else
                            <span class="badge bg-secondary">
                                <i class="bi bi-x-circle"></i> Not Available
                            </span>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="package-footer">
                        <a href="{{ route('landing.detail-paket', $paket->id_paket) }}" 
                           class="btn btn-detail w-100 mb-2">
                            <i class="bi bi-eye"></i> See Details
                        </a>
                        @if($paket->status == 'aktif')
                            <a href="{{ route('landing.detail-paket', $paket->id_paket) }}" 
                               class="btn btn-primary w-100">
                                <i class="bi bi-cart-plus"></i> Order Now
                            </a>
                        @else
                            <button class="btn btn-secondary w-100" disabled>
                                <i class="bi bi-x-circle"></i> Not Available
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="alert alert-info text-center" data-aos="fade-up">
                    <i class="bi bi-info-circle fs-1 mb-3 d-block"></i>
                    <h5>No Tour Packages Yet</h5>
                    <p class="mb-0">Tour packages will be available soon. Please check back later!</p>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Contact Section -->
<div class="elementor-element elementor-element-75a2ce94 elementor-widget elementor-widget-image animated togo-fade-in-up" data-id="75a2ce94" data-element_type="widget" data-settings="{&quot;_animation&quot;:&quot;togo-fade-in-up&quot;}" data-widget_type="image.default">
	<img fetchpriority="low" decoding="async" loading="lazy" width="2160" height="350" src="https://visitkampungtelagaair.com/wp-content/uploads/2025/04/svgexport-1-1-1.png" class="attachment-full size-full wp-image-3469" alt="" srcset="https://visitkampungtelagaair.com/wp-content/uploads/2025/04/svgexport-1-1-1.png 2160w, https://visitkampungtelagaair.com/wp-content/uploads/2025/04/svgexport-1-1-1-600x126.png 600w" sizes="(max-width: 2160px) 100vw, 2160px">
</div>
