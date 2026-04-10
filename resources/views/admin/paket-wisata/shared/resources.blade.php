@php
    $currentPaketWisata = $paketWisata ?? null;
    $selectedDestinasiIds = $selectedDestinasiIds ?? [];
    $selectedHomestayIds = $selectedHomestayIds ?? [];
    $selectedCulinaryIds = $selectedCulinaryIds ?? [];
    $selectedBoatIds = $selectedBoatIds ?? [];
    $selectedKioskIds = $selectedKioskIds ?? [];
    $destinasiHariMap = $destinasiHariMap ?? [];
    $homestayMalamMap = $homestayMalamMap ?? [];
    $culinaryHariMap = $culinaryHariMap ?? [];
    $boatHariMap = $boatHariMap ?? [];
    $kioskHariMap = $kioskHariMap ?? [];
    $itineraryItems = $itineraryItems ?? [];
@endphp

<div class="col-lg-8">
    <div class="package-workspace card border-0">
        <div class="card-body p-3 p-xl-4">
            <div class="package-workspace-top">
                <div>
                    <span class="package-hero-badge mb-3">
                        <i class="bi bi-box-seam"></i>
                        Resource Workspace
                    </span>
                    <h4 class="fw-bold mb-1">Build the package contents</h4>
                    <p class="text-muted mb-0">Choose what is included, assign the day of use, and shape the itinerary with live pricing feedback.</p>
                </div>
                <div class="d-flex gap-2 align-items-start flex-wrap">
                    <button type="button" class="btn btn-outline-primary" onclick="generateContent('all', this)">
                        <i class="bi bi-stars"></i> Auto-Generate Content
                    </button>
                </div>
            </div>

            <div class="selection-overview row g-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="overview-card">
                        <span class="label">Selected Resources</span>
                        <span class="value" id="summary-selected-count">0</span>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="overview-card">
                        <span class="label">Duration</span>
                        <span class="value" id="summary-duration">{{ old('durasi_hari', $currentPaketWisata?->durasi_hari ?? 3) }} days</span>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="overview-card">
                        <span class="label">Itinerary Days</span>
                        <span class="value" id="summary-itinerary-count">{{ max(count($itineraryItems), 1) }}</span>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="overview-card">
                        <span class="label">Current Cost</span>
                        <span class="value" id="summary-cost-preview">{{ format_ringgit($modalAwal ?? 0) }}</span>
                    </div>
                </div>
            </div>

            <div class="resource-toolbar">
                <div>
                    <h6 class="fw-bold mb-1">Resource Selector</h6>
                    <small class="text-muted">Use tabs to move between categories. Search helps when the list starts getting long.</small>
                </div>
                <div class="resource-search input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="resource-search-input" placeholder="Search within the active tab...">
                </div>
            </div>

            <div class="resource-tabs-shell">
                <ul class="nav nav-pills" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pills-destinasi-tab" data-bs-toggle="pill" data-bs-target="#pills-destinasi" type="button">
                    <i class="bi bi-geo-alt-fill"></i> Destinasi <span class="badge bg-danger ms-1" id="count-destinasi">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-homestay-tab" data-bs-toggle="pill" data-bs-target="#pills-homestay" type="button">
                    <i class="bi bi-house-fill"></i> Homestay <span class="badge bg-danger ms-1" id="count-homestay">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-kuliner-tab" data-bs-toggle="pill" data-bs-target="#pills-kuliner" type="button">
                    <i class="bi bi-cup-hot-fill"></i> Culinary <span class="badge bg-danger ms-1" id="count-kuliner">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-boat-tab" data-bs-toggle="pill" data-bs-target="#pills-boat" type="button">
                    <i class="bi bi-water"></i> Boat <span class="badge bg-danger ms-1" id="count-boat">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-kiosk-tab" data-bs-toggle="pill" data-bs-target="#pills-kiosk" type="button">
                    <i class="bi bi-shop"></i> Kiosk <span class="badge bg-danger ms-1" id="count-kiosk">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-itinerary-tab" data-bs-toggle="pill" data-bs-target="#pills-itinerary" type="button">
                    <i class="bi bi-calendar-check-fill"></i> Itinerary
                </button>
            </li>
        </ul>
            </div>

    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-destinasi">
            <div class="resource-pane-intro">
                <p>Pick destinations to define the flow of the trip. Destinations shape the itinerary but do not add direct package cost.</p>
            </div>
            <div class="row g-3">
                @forelse($destinasis as $destinasi)
                @php
                    $isSelected = in_array((string) $destinasi->id_destinasi, $selectedDestinasiIds, true);
                    $hariKe = $destinasiHariMap[(string) $destinasi->id_destinasi] ?? 1;
                @endphp
                <div class="col-md-6">
                    <div class="card h-100 item-card resource-searchable {{ $isSelected ? 'checked border-primary shadow-sm' : '' }}" data-type="destinasi" data-search="{{ strtolower($destinasi->nama . ' ' . $destinasi->lokasi) }}">
                        <div class="card-body">
                            <div class="form-check mb-2">
                                <input class="form-check-input item-checkbox" type="checkbox" name="destinasi_ids[]" value="{{ $destinasi->id_destinasi }}" id="dest{{ $destinasi->id_destinasi }}" {{ $isSelected ? 'checked' : '' }} onchange="toggleItemInputs(this); updateCounter('destinasi'); calculatePricing();">
                                <label class="form-check-label fw-bold" for="dest{{ $destinasi->id_destinasi }}">
                                    {{ $destinasi->nama }}
                                </label>
                            </div>
                            <p class="text-muted small mb-2"><i class="bi bi-geo-alt"></i> {{ $destinasi->lokasi }}</p>
                            @if($destinasi->fotos->count() > 0)
                            <img src="{{ asset('storage/'.$destinasi->fotos->first()->foto) }}" class="item-card-visual" alt="{{ $destinasi->nama }}">
                            @endif
                            <div class="mt-2">
                                <label class="form-label small">Day:</label>
                                <input type="number" class="form-control form-control-sm day-input" name="destinasi_hari[]" min="1" value="{{ $hariKe }}" {{ $isSelected ? '' : 'disabled' }} onchange="validateDayInput(this)">
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> No destination data available
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        <div class="tab-pane fade" id="pills-homestay">
            <div class="resource-pane-intro">
                <p>Choose stays and define how many nights are included. These selections directly affect package cost.</p>
            </div>
            <div class="row g-3">
                @forelse($homestays as $homestay)
                @php
                    $isSelected = in_array((string) $homestay->id_homestay, $selectedHomestayIds, true);
                    $jumlahMalam = $homestayMalamMap[(string) $homestay->id_homestay] ?? 1;
                @endphp
                <div class="col-md-6">
                    <div class="card h-100 item-card resource-searchable {{ $isSelected ? 'checked border-primary shadow-sm' : '' }}" data-type="homestay" data-price="{{ $homestay->harga_per_malam }}" data-name="{{ $homestay->nama }}" data-search="{{ strtolower($homestay->nama . ' ' . ($homestay->alamat ?? '')) }}">
                        <div class="card-body">
                            <div class="form-check mb-2">
                                <input class="form-check-input item-checkbox homestay-checkbox" type="checkbox" name="homestay_ids[]" value="{{ $homestay->id_homestay }}" id="home{{ $homestay->id_homestay }}" data-price="{{ $homestay->harga_per_malam }}" {{ $isSelected ? 'checked' : '' }} onchange="toggleItemInputs(this); updateCounter('homestay'); calculatePricing();">
                                <label class="form-check-label fw-bold" for="home{{ $homestay->id_homestay }}">
                                    {{ $homestay->nama }}
                                </label>
                            </div>
                            <div class="item-meta-badges">
                                <span class="badge bg-success">{{ format_ringgit($homestay->harga_per_malam) }}/night</span>
                                <span class="badge bg-info">{{ $homestay->kapasitas }} people</span>
                            </div>
                            <div class="mt-2">
                                <label class="form-label small">Number of Nights:</label>
                                <input type="number" class="form-control form-control-sm homestay-malam" name="homestay_malam[]" min="1" value="{{ $jumlahMalam }}" {{ $isSelected ? '' : 'disabled' }} onchange="calculatePricing();">
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> No homestay data available yet
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        <div class="tab-pane fade" id="pills-kuliner">
            <div class="resource-pane-intro">
                <p>Select culinary packages for the days they should appear in the trip. Variants carry their own pricing.</p>
            </div>
            <div class="accordion" id="accordionCulinary">
                @forelse($culinaries as $index => $culinary)
                @php
                    $hasSelectedPackage = $culinary->pakets->contains(fn($paket) => in_array((string) $paket->id, $selectedCulinaryIds, true));
                    $showAccordion = $hasSelectedPackage || (empty($selectedCulinaryIds) && $index === 0);
                @endphp
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingCul{{ $culinary->id }}">
                        <button class="accordion-button {{ $showAccordion ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCul{{ $culinary->id }}">
                            {{ $culinary->nama }}
                            <span class="badge bg-secondary ms-2">{{ $culinary->pakets->count() }} Packages</span>
                        </button>
                    </h2>
                    <div id="collapseCul{{ $culinary->id }}" class="accordion-collapse collapse {{ $showAccordion ? 'show' : '' }}" data-bs-parent="#accordionCulinary">
                        <div class="accordion-body">
                            @if($culinary->deskripsi)
                                <p class="small text-muted mb-3">{{ $culinary->deskripsi }}</p>
                            @endif
                            <div class="row g-3">
                                @forelse($culinary->pakets as $paket)
                                @php
                                    $isSelected = in_array((string) $paket->id, $selectedCulinaryIds, true);
                                    $hariKe = $culinaryHariMap[(string) $paket->id] ?? 1;
                                @endphp
                                <div class="col-md-12">
                                    <div class="card h-100 item-card resource-searchable {{ $isSelected ? 'checked border-primary shadow-sm' : '' }}" data-type="kuliner" data-price="{{ $paket->harga }}" data-name="{{ $culinary->nama }} - {{ $paket->nama_paket }}" data-search="{{ strtolower($culinary->nama . ' ' . $paket->nama_paket . ' ' . ($paket->deskripsi_paket ?? '')) }}">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="form-check">
                                                    <input class="form-check-input item-checkbox culinary-checkbox" type="checkbox" name="culinary_paket_ids[]" value="{{ $paket->id }}" id="cul{{ $paket->id }}" data-price="{{ $paket->harga }}" {{ $isSelected ? 'checked' : '' }} onchange="toggleItemInputs(this); updateCounter('kuliner'); calculatePricing();">
                                                    <label class="form-check-label fw-bold" for="cul{{ $paket->id }}">
                                                        {{ $paket->nama_paket }}
                                                    </label>
                                                    <div class="item-meta-badges mt-2">
                                                        <span class="badge bg-success">{{ format_ringgit($paket->harga) }}</span>
                                                        <span class="badge bg-info">{{ $paket->kapasitas }} people</span>
                                                    </div>
                                                    @if($paket->deskripsi_paket)
                                                        <p class="small text-muted mt-2 mb-0">{{ $paket->deskripsi_paket }}</p>
                                                    @endif
                                                </div>
                                                <div style="width: 80px;">
                                                    <label class="form-label small mb-1">Day:</label>
                                                    <input type="number" class="form-control form-control-sm day-input" name="culinary_hari[]" min="1" value="{{ $hariKe }}" {{ $isSelected ? '' : 'disabled' }} onchange="validateDayInput(this); calculatePricing();">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="col-12">
                                    <div class="alert alert-info py-2 small">No packages available for this culinary spot.</div>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> No culinary data available yet
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        <div class="tab-pane fade" id="pills-boat">
            <div class="resource-pane-intro">
                <p>Add boat services to the package and place them on the right day. Boat cost is charged per selected day.</p>
            </div>
            <div class="row g-3">
                @forelse($boats as $boat)
                @php
                    $isSelected = in_array((string) $boat->id, $selectedBoatIds, true);
                    $hariKe = $boatHariMap[(string) $boat->id] ?? 1;
                @endphp
                <div class="col-md-6">
                    <div class="card h-100 item-card resource-searchable {{ $isSelected ? 'checked border-primary shadow-sm' : '' }}" data-type="boat" data-price="{{ $boat->harga_sewa }}" data-name="{{ $boat->nama }}" data-search="{{ strtolower($boat->nama . ' ' . ($boat->lokasi ?? '')) }}">
                        <div class="card-body">
                            <div class="form-check mb-2">
                                <input class="form-check-input item-checkbox boat-checkbox" type="checkbox" name="boat_ids[]" value="{{ $boat->id }}" id="boat{{ $boat->id }}" data-price="{{ $boat->harga_sewa }}" {{ $isSelected ? 'checked' : '' }} onchange="toggleItemInputs(this); updateCounter('boat'); calculatePricing();">
                                <label class="form-check-label fw-bold" for="boat{{ $boat->id }}">
                                    {{ $boat->nama }}
                                </label>
                            </div>
                            <div class="item-meta-badges">
                                <span class="badge bg-success">{{ format_ringgit($boat->harga_sewa) }}</span>
                                <span class="badge bg-info">{{ $boat->kapasitas }} people</span>
                            </div>
                            <div class="mt-2">
                                <label class="form-label small">Day:</label>
                                <input type="number" class="form-control form-control-sm day-input" name="boat_hari[]" min="1" value="{{ $hariKe }}" {{ $isSelected ? '' : 'disabled' }} onchange="validateDayInput(this); calculatePricing();">
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> No boat data available yet
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        <div class="tab-pane fade" id="pills-kiosk">
            <div class="resource-pane-intro">
                <p>Include souvenir or kiosk bundles that belong to certain days of the package.</p>
            </div>
            <div class="row g-3">
                @forelse($kiosks as $kiosk)
                @php
                    $isSelected = in_array((string) $kiosk->id_kiosk, $selectedKioskIds, true);
                    $hariKe = $kioskHariMap[(string) $kiosk->id_kiosk] ?? 1;
                @endphp
                <div class="col-md-6">
                    <div class="card h-100 item-card resource-searchable {{ $isSelected ? 'checked border-primary shadow-sm' : '' }}" data-type="kiosk" data-price="{{ $kiosk->harga_per_paket }}" data-name="{{ $kiosk->nama }}" data-search="{{ strtolower($kiosk->nama . ' ' . ($kiosk->lokasi ?? '')) }}">
                        <div class="card-body">
                            <div class="form-check mb-2">
                                <input class="form-check-input item-checkbox kiosk-checkbox" type="checkbox" name="kiosk_ids[]" value="{{ $kiosk->id_kiosk }}" id="kiosk{{ $kiosk->id_kiosk }}" data-price="{{ $kiosk->harga_per_paket }}" {{ $isSelected ? 'checked' : '' }} onchange="toggleItemInputs(this); updateCounter('kiosk'); calculatePricing();">
                                <label class="form-check-label fw-bold" for="kiosk{{ $kiosk->id_kiosk }}">
                                    {{ $kiosk->nama }}
                                </label>
                            </div>
                            <div class="item-meta-badges">
                                <span class="badge bg-success">{{ format_ringgit($kiosk->harga_per_paket) }}</span>
                                <span class="badge bg-info">{{ $kiosk->kapasitas }} people</span>
                            </div>
                            <div class="mt-2">
                                <label class="form-label small">Day:</label>
                                <input type="number" class="form-control form-control-sm day-input" name="kiosk_hari[]" min="1" value="{{ $hariKe }}" {{ $isSelected ? '' : 'disabled' }} onchange="validateDayInput(this); calculatePricing();">
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> No kiosk data available yet
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        <div class="tab-pane fade" id="pills-itinerary">
            <div class="itinerary-header-card">
                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div>
                        <h5 class="mb-1"><i class="bi bi-calendar-check"></i> Daily Schedule Builder</h5>
                        <p class="mb-0 text-muted small">Use this area to turn the selected resources into a readable day-by-day experience.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="generateContent('itinerary', this)">
                            <i class="bi bi-stars"></i> AI Generate Itinerary
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="addItinerary()">
                            <i class="bi bi-plus-circle"></i> Add Day
                        </button>
                    </div>
                </div>
            </div>

            <div id="itinerary-container">
                @forelse($itineraryItems as $index => $itinerary)
                <div class="itinerary-item card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="itinerary-day-chip"><i class="bi bi-calendar-event"></i> <span data-itinerary-title>Day {{ $itinerary['hari_ke'] }}</span></span>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeItinerary(this)">
                                <i class="bi bi-trash"></i> Remove
                            </button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Day</label>
                                <input type="number" class="form-control day-input" name="itinerary_hari[]" value="{{ $itinerary['hari_ke'] }}" min="1" onchange="validateDayInput(this); refreshItineraryLabels();">
                            </div>
                            <div class="col-md-10">
                                <label class="form-label fw-bold">Title</label>
                                <input type="text" class="form-control mb-2" name="itinerary_judul[]" value="{{ $itinerary['judul_hari'] }}" placeholder="Example: Arrival & Check-in">
                                <label class="form-label fw-bold">Description</label>
                                <textarea class="form-control" name="itinerary_deskripsi[]" rows="3" placeholder="Describe the activities for this day...">{{ $itinerary['deskripsi_kegiatan'] }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="itinerary-item card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="itinerary-day-chip"><i class="bi bi-calendar-event"></i> <span data-itinerary-title>Day 1</span></span>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeItinerary(this)">
                                <i class="bi bi-trash"></i> Remove
                            </button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Day</label>
                                <input type="number" class="form-control day-input" name="itinerary_hari[]" value="1" min="1" onchange="validateDayInput(this); refreshItineraryLabels();">
                            </div>
                            <div class="col-md-10">
                                <label class="form-label fw-bold">Title</label>
                                <input type="text" class="form-control mb-2" name="itinerary_judul[]" placeholder="Example: Arrival & Check-in">
                                <label class="form-label fw-bold">Description</label>
                                <textarea class="form-control" name="itinerary_deskripsi[]" rows="3" placeholder="Describe the activities for this day..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
</div>
</div>
