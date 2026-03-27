@php
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
    <div class="sticky-top bg-white pt-3 pb-2" style="top: 70px; z-index: 1020; margin-top: -1rem; border-bottom: 1px solid #eee; margin-bottom: 1rem;">
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

    <div class="mb-3 text-end">
        <button type="button" class="btn btn-primary btn-sm" onclick="generateContent()">
            <i class="bi bi-stars"></i> Auto-Generate Content
        </button>
    </div>

    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-destinasi">
            <div class="row g-3">
                @forelse($destinasis as $destinasi)
                @php
                    $isSelected = in_array((string) $destinasi->id_destinasi, $selectedDestinasiIds, true);
                    $hariKe = $destinasiHariMap[(string) $destinasi->id_destinasi] ?? 1;
                @endphp
                <div class="col-md-6">
                    <div class="card h-100 item-card {{ $isSelected ? 'checked border-primary shadow-sm' : '' }}" data-type="destinasi">
                        <div class="card-body">
                            <div class="form-check mb-2">
                                <input class="form-check-input item-checkbox" type="checkbox" name="destinasi_ids[]" value="{{ $destinasi->id_destinasi }}" id="dest{{ $destinasi->id_destinasi }}" {{ $isSelected ? 'checked' : '' }} onchange="toggleItemInputs(this); updateCounter('destinasi'); calculatePricing();">
                                <label class="form-check-label fw-bold" for="dest{{ $destinasi->id_destinasi }}">
                                    {{ $destinasi->nama }}
                                </label>
                            </div>
                            <p class="text-muted small mb-2"><i class="bi bi-geo-alt"></i> {{ $destinasi->lokasi }}</p>
                            @if($destinasi->fotos->count() > 0)
                            <img src="{{ asset('storage/'.$destinasi->fotos->first()->foto) }}" class="img-fluid rounded mb-2" style="height: 120px; width: 100%; object-fit: cover;">
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
            <div class="row g-3">
                @forelse($homestays as $homestay)
                @php
                    $isSelected = in_array((string) $homestay->id_homestay, $selectedHomestayIds, true);
                    $jumlahMalam = $homestayMalamMap[(string) $homestay->id_homestay] ?? 1;
                @endphp
                <div class="col-md-6">
                    <div class="card h-100 item-card {{ $isSelected ? 'checked border-primary shadow-sm' : '' }}" data-type="homestay" data-price="{{ $homestay->harga_per_malam }}" data-name="{{ $homestay->nama }}">
                        <div class="card-body">
                            <div class="form-check mb-2">
                                <input class="form-check-input item-checkbox homestay-checkbox" type="checkbox" name="homestay_ids[]" value="{{ $homestay->id_homestay }}" id="home{{ $homestay->id_homestay }}" data-price="{{ $homestay->harga_per_malam }}" {{ $isSelected ? 'checked' : '' }} onchange="toggleItemInputs(this); updateCounter('homestay'); calculatePricing();">
                                <label class="form-check-label fw-bold" for="home{{ $homestay->id_homestay }}">
                                    {{ $homestay->nama }}
                                </label>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
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
                                    <div class="card h-100 item-card {{ $isSelected ? 'checked border-primary shadow-sm' : '' }}" data-type="kuliner" data-price="{{ $paket->harga }}" data-name="{{ $culinary->nama }} - {{ $paket->nama_paket }}">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="form-check">
                                                    <input class="form-check-input item-checkbox culinary-checkbox" type="checkbox" name="culinary_paket_ids[]" value="{{ $paket->id }}" id="cul{{ $paket->id }}" data-price="{{ $paket->harga }}" {{ $isSelected ? 'checked' : '' }} onchange="toggleItemInputs(this); updateCounter('kuliner'); calculatePricing();">
                                                    <label class="form-check-label fw-bold" for="cul{{ $paket->id }}">
                                                        {{ $paket->nama_paket }}
                                                    </label>
                                                    <div class="mt-1">
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
            <div class="row g-3">
                @forelse($boats as $boat)
                @php
                    $isSelected = in_array((string) $boat->id, $selectedBoatIds, true);
                    $hariKe = $boatHariMap[(string) $boat->id] ?? 1;
                @endphp
                <div class="col-md-6">
                    <div class="card h-100 item-card {{ $isSelected ? 'checked border-primary shadow-sm' : '' }}" data-type="boat" data-price="{{ $boat->harga_sewa }}" data-name="{{ $boat->nama }}">
                        <div class="card-body">
                            <div class="form-check mb-2">
                                <input class="form-check-input item-checkbox boat-checkbox" type="checkbox" name="boat_ids[]" value="{{ $boat->id }}" id="boat{{ $boat->id }}" data-price="{{ $boat->harga_sewa }}" {{ $isSelected ? 'checked' : '' }} onchange="toggleItemInputs(this); updateCounter('boat'); calculatePricing();">
                                <label class="form-check-label fw-bold" for="boat{{ $boat->id }}">
                                    {{ $boat->nama }}
                                </label>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
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
            <div class="row g-3">
                @forelse($kiosks as $kiosk)
                @php
                    $isSelected = in_array((string) $kiosk->id_kiosk, $selectedKioskIds, true);
                    $hariKe = $kioskHariMap[(string) $kiosk->id_kiosk] ?? 1;
                @endphp
                <div class="col-md-6">
                    <div class="card h-100 item-card {{ $isSelected ? 'checked border-primary shadow-sm' : '' }}" data-type="kiosk" data-price="{{ $kiosk->harga_per_paket }}" data-name="{{ $kiosk->nama }}">
                        <div class="card-body">
                            <div class="form-check mb-2">
                                <input class="form-check-input item-checkbox kiosk-checkbox" type="checkbox" name="kiosk_ids[]" value="{{ $kiosk->id_kiosk }}" id="kiosk{{ $kiosk->id_kiosk }}" data-price="{{ $kiosk->harga_per_paket }}" {{ $isSelected ? 'checked' : '' }} onchange="toggleItemInputs(this); updateCounter('kiosk'); calculatePricing();">
                                <label class="form-check-label fw-bold" for="kiosk{{ $kiosk->id_kiosk }}">
                                    {{ $kiosk->nama }}
                                </label>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
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
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="bi bi-calendar-check"></i> Daily Schedule</h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="generateContent('itinerary', this)">
                        <i class="bi bi-stars"></i> AI Generate Itinerary
                    </button>
                    <button type="button" class="btn btn-success btn-sm" onclick="addItinerary()">
                        <i class="bi bi-plus-circle"></i> Add Day
                    </button>
                </div>
            </div>

            <div id="itinerary-container">
                @forelse($itineraryItems as $index => $itinerary)
                <div class="itinerary-item card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <h6 class="mb-0">Day {{ $itinerary['hari_ke'] }}</h6>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeItinerary(this)">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Day:</label>
                                <input type="number" class="form-control day-input" name="itinerary_hari[]" value="{{ $itinerary['hari_ke'] }}" min="1" onchange="validateDayInput(this)">
                            </div>
                            <div class="col-md-10">
                                <label class="form-label fw-bold">Title:</label>
                                <input type="text" class="form-control mb-2" name="itinerary_judul[]" value="{{ $itinerary['judul_hari'] }}" placeholder="Contoh: Kedatangan & Check-in">
                                <label class="form-label fw-bold">Description:</label>
                                <textarea class="form-control" name="itinerary_deskripsi[]" rows="2" placeholder="Jelaskan aktivitas di hari ini...">{{ $itinerary['deskripsi_kegiatan'] }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="itinerary-item card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <h6 class="mb-0">Day 1</h6>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeItinerary(this)">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Day:</label>
                                <input type="number" class="form-control day-input" name="itinerary_hari[]" value="1" min="1" onchange="validateDayInput(this)">
                            </div>
                            <div class="col-md-10">
                                <label class="form-label fw-bold">Title:</label>
                                <input type="text" class="form-control mb-2" name="itinerary_judul[]" placeholder="Contoh: Kedatangan & Check-in">
                                <label class="form-label fw-bold">Description:</label>
                                <textarea class="form-control" name="itinerary_deskripsi[]" rows="2" placeholder="Jelaskan aktivitas di hari ini..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
