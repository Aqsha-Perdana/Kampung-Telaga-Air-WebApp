@extends('layout.sidebar')
@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="bi bi-suitcase-lg"></i> Add New Tour Package</h2>
            <p class="text-muted">Select destinations, accommodations, and services to create attractive tour packages.</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('paket-wisata.store') }}" method="POST" id="paketForm">
        @csrf
        
        <div class="row">
            <!-- LEFT SIDEBAR - Form Input -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #77c3d5ff 100%);">
                        <h5 class="text-black mb-0"><i class="bi bi-info-circle-fill"></i> Detail Package</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="nama_paket" class="form-label fw-bold">Package Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nama_paket') is-invalid @enderror" 
                                   id="nama_paket" 
                                   name="nama_paket" 
                                   value="{{ old('nama_paket') }}" 
                                   placeholder="Paket Adalah Pokoknya"
                                   required>
                            @error('nama_paket')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="durasi_hari" class="form-label fw-bold">Duration <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" 
                                               class="form-control @error('durasi_hari') is-invalid @enderror" 
                                               id="durasi_hari" 
                                               name="durasi_hari" 
                                               value="{{ old('durasi_hari', 3) }}" 
                                               min="1"
                                               onchange="updateDurationInfo()"
                                               required>
                                        <span class="input-group-text">Day</span>
                                    </div>
                                    @error('durasi_hari')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" 
                                            name="status" 
                                            required>
                                        <option value="aktif" selected>Active</option>
                                        <option value="nonaktif">Unactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Duration Info Alert -->
                        <div class="alert alert-info mb-3" id="duration-alert">
                            <small>
                                <i class="bi bi-info-circle"></i> 
                                <strong>Package Duration: <span id="package-duration-display">3</span> Days</strong>
                                <br>Select items and specify on which day they will be used.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label fw-bold">Description</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" 
                                      name="deskripsi" 
                                      rows="4"
                                      placeholder="Jelaskan keunikan paket wisata ini...">{{ old('deskripsi') }}</textarea>
                        </div>

                        <!-- COST BREAKDOWN SECTION -->
                        <div class="card bg-light mb-3">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="bi bi-calculator"></i> Cost Breakdown</h6>
                            </div>
                            <div class="card-body">
                                <div id="cost-breakdown" class="mb-2">
                                    <small class="text-muted">No items selected yet</small>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <strong>Total Cost (Modal)</strong>
                                    <strong class="text-primary" id="breakdown-total">RM 0.00</strong>
                                </div>
                            </div>
                        </div>

                        <!-- PRICING SECTION -->
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="bi bi-currency-dollar"></i> Pricing Information</h6>
                                
                                <!-- Harga Modal (Auto Calculate) -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Cost Price (Modal)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">RM</span>
                                        <input type="text" 
                                               class="form-control bg-white" 
                                               id="display_harga_modal" 
                                               value="0.00" 
                                               readonly>
                                    </div>
                                    <input type="hidden" name="harga_modal" id="harga_modal" value="0">
                                    <small class="text-muted">Auto-calculated from selected items</small>
                                </div>

                                <!-- Harga Jual (Manual Input) -->
                                <div class="mb-3">
                                    <label for="harga_jual" class="form-label fw-bold">Selling Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">RM</span>
                                        <input type="number" 
                                               class="form-control @error('harga_jual') is-invalid @enderror" 
                                               id="harga_jual" 
                                               name="harga_jual" 
                                               value="{{ old('harga_jual', 0) }}" 
                                               min="0"
                                               step="0.01"
                                               required>
                                    </div>
                                    @error('harga_jual')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Discount Type -->
                                <div class="mb-3">
                                    <label for="tipe_diskon" class="form-label fw-bold">Discount Type</label>
                                    <select class="form-select" id="tipe_diskon" name="tipe_diskon" onchange="toggleDiscount()">
                                        <option value="none" selected>No Discount</option>
                                        <option value="nominal">Nominal (RM)</option>
                                        <option value="persen">Percentage (%)</option>
                                    </select>
                                </div>

                                <!-- Discount Nominal -->
                                <div class="mb-3" id="discount_nominal_group" style="display: none;">
                                    <label for="diskon_nominal" class="form-label fw-bold">Discount Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">RM</span>
                                        <input type="number" 
                                               class="form-control" 
                                               id="diskon_nominal" 
                                               name="diskon_nominal" 
                                               value="0" 
                                               min="0"
                                               step="0.01">
                                    </div>
                                </div>

                                <!-- Discount Percentage -->
                                <div class="mb-3" id="discount_persen_group" style="display: none;">
                                    <label for="diskon_persen" class="form-label fw-bold">Discount Percentage</label>
                                    <div class="input-group">
                                        <input type="number" 
                                               class="form-control" 
                                               id="diskon_persen" 
                                               name="diskon_persen" 
                                               value="0" 
                                               min="0"
                                               max="100"
                                               step="0.01">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>

                                <!-- Harga Final -->
                                <div class="alert alert-success mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>Final Price:</strong>
                                        <h4 class="mb-0" id="display_harga_final">RM 0.00</h4>
                                    </div>
                                </div>

                                <!-- Company Profit Info -->
                                <div class="card border-primary mb-3">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="bi bi-cash-stack"></i> Company Profit Analysis</h6>
                                    </div>
                                    <div class="card-body" id="profit-alert">
                                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                                            <span class="text-muted">Final Price</span>
                                            <span class="fw-bold" id="display_final_price_profit">RM 0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                                            <span class="text-muted">Cost Price</span>
                                            <span class="fw-bold text-danger" id="display_cost_price_profit">RM 0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                                            <span class="fw-bold">Gross Profit</span>
                                            <span class="fw-bold text-success" id="display_profit">RM 0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-bold">Profit Margin</span>
                                            <span class="fw-bold text-primary" id="display_profit_persen">0.00%</span>
                                        </div>
                                        
                                        <!-- Profit Status Badge -->
                                        <div class="mt-3 text-center">
                                            <span class="badge bg-secondary py-2 px-3" id="profit-status-badge">
                                                <i class="bi bi-info-circle"></i> Set selling price to see profit
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning mb-0">
                            <small><i class="bi bi-lightbulb-fill"></i> <strong>Tips:</strong> Select items on the right to create your travel package. Prices will be calculated automatically!</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT CONTENT - Selection Items -->
            <div class="col-lg-8">
                <!-- Tab Navigation -->
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
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

                <!-- Tab Content -->
                <div class="tab-content" id="pills-tabContent">
                    <!-- DESTINASI TAB -->
                    <div class="tab-pane fade show active" id="pills-destinasi">
                        <div class="row g-3">
                            @forelse($destinasis as $destinasi)
                            <div class="col-md-6">
                                <div class="card h-100 item-card" data-type="destinasi">
                                    <div class="card-body">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input item-checkbox" 
                                                   type="checkbox" 
                                                   name="destinasi_ids[]" 
                                                   value="{{ $destinasi->id_destinasi }}" 
                                                   id="dest{{ $destinasi->id_destinasi }}"
                                                   onchange="updateCounter('destinasi'); calculatePricing();">
                                            <label class="form-check-label fw-bold" for="dest{{ $destinasi->id_destinasi }}">
                                                {{ $destinasi->nama }}
                                            </label>
                                        </div>
                                        <p class="text-muted small mb-2"><i class="bi bi-geo-alt"></i> {{ $destinasi->lokasi }}</p>
                                        @if($destinasi->fotos->count() > 0)
                                        <img src="{{ asset('storage/'.$destinasi->fotos->first()->foto) }}" 
                                             class="img-fluid rounded mb-2" 
                                             style="height: 120px; width: 100%; object-fit: cover;">
                                        @endif
                                        <div class="mt-2">
                                            <label class="form-label small">Day:</label>
                                            <input type="number" 
                                                   class="form-control form-control-sm day-input" 
                                                   name="destinasi_hari[]" 
                                                   min="1" 
                                                   value="1"
                                                   onchange="validateDayInput(this)">
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

                    <!-- HOMESTAY TAB -->
                    <div class="tab-pane fade" id="pills-homestay">
                        <div class="row g-3">
                            @forelse($homestays as $homestay)
                            <div class="col-md-6">
                                <div class="card h-100 item-card" data-type="homestay" data-price="{{ $homestay->harga_per_malam }}" data-name="{{ $homestay->nama }}">
                                    <div class="card-body">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input item-checkbox homestay-checkbox" 
                                                   type="checkbox" 
                                                   name="homestay_ids[]" 
                                                   value="{{ $homestay->id_homestay }}" 
                                                   id="home{{ $homestay->id }}"
                                                   data-price="{{ $homestay->harga_per_malam }}"
                                                   onchange="updateCounter('homestay'); calculatePricing();">
                                            <label class="form-check-label fw-bold" for="home{{ $homestay->id }}">
                                                {{ $homestay->nama }}
                                            </label>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="badge bg-success">{{ format_ringgit($homestay->harga_per_malam) }}/night</span>
                                            <span class="badge bg-info">{{ $homestay->kapasitas }} people</span>
                                        </div>
                                        <div class="mt-2">
                                            <label class="form-label small">Number of Nights:</label>
                                            <input type="number" 
                                                   class="form-control form-control-sm homestay-malam" 
                                                   name="homestay_malam[]" 
                                                   min="1" 
                                                   value="1"
                                                   onchange="calculatePricing();">
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

                    <!-- KULINER TAB -->
                    <div class="tab-pane fade" id="pills-kuliner">
                        <div class="row g-3">
                            @forelse($culinaries as $culinary)
                                @foreach($culinary->pakets as $paket)
                                <div class="col-md-6">
                                    <div class="card h-100 item-card" data-type="kuliner" data-price="{{ $paket->harga }}" data-name="{{ $culinary->nama }} - {{ $paket->nama_paket }}">
                                        <div class="card-body">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input item-checkbox culinary-checkbox" 
                                                       type="checkbox" 
                                                       name="culinary_paket_ids[]" 
                                                       value="{{ $paket->id }}" 
                                                       id="cul{{ $paket->id }}"
                                                       data-price="{{ $paket->harga }}"
                                                       onchange="updateCounter('kuliner'); calculatePricing();">
                                                <label class="form-check-label fw-bold" for="cul{{ $paket->id }}">
                                                    {{ $culinary->nama }}
                                                </label>
                                            </div>
                                            <span class="badge bg-warning text-dark mb-2">{{ $paket->nama_paket }}</span>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="badge bg-success">{{ format_ringgit($paket->harga) }}</span>
                                                <span class="badge bg-info">{{ $paket->kapasitas }} people</span>
                                            </div>
                                            @if($paket->deskripsi_paket)
                                                <p class="small text-muted mb-2">{{ Str::limit($paket->deskripsi_paket, 60) }}</p>
                                            @endif
                                            <div class="mt-2">
                                                <label class="form-label small">Day:</label>
                                                <input type="number" 
                                                       class="form-control form-control-sm day-input" 
                                                       name="culinary_hari[]" 
                                                       min="1" 
                                                       value="1"
                                                       onchange="validateDayInput(this); calculatePricing();">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @empty
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i> No culinary data available yet
                                </div>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- BOAT TAB -->
                    <div class="tab-pane fade" id="pills-boat">
                        <div class="row g-3">
                            @forelse($boats as $boat)
                            <div class="col-md-6">
                                <div class="card h-100 item-card" data-type="boat" data-price="{{ $boat->harga_sewa }}" data-name="{{ $boat->nama }}">
                                    <div class="card-body">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input item-checkbox boat-checkbox" 
                                                   type="checkbox" 
                                                   name="boat_ids[]" 
                                                   value="{{ $boat->id }}" 
                                                   id="boat{{ $boat->id }}"
                                                   data-price="{{ $boat->harga_sewa }}"
                                                   onchange="updateCounter('boat'); calculatePricing();">
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
                                            <input type="number" 
                                                   class="form-control form-control-sm day-input" 
                                                   name="boat_hari[]" 
                                                   min="1" 
                                                   value="1"
                                                   onchange="validateDayInput(this); calculatePricing();">
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

                    <!-- KIOSK TAB -->
                    <div class="tab-pane fade" id="pills-kiosk">
                        <div class="row g-3">
                            @forelse($kiosks as $kiosk)
                            <div class="col-md-6">
                                <div class="card h-100 item-card" data-type="kiosk" data-price="{{ $kiosk->harga_per_paket }}" data-name="{{ $kiosk->nama }}">
                                    <div class="card-body">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input item-checkbox kiosk-checkbox" 
                                                   type="checkbox" 
                                                   name="kiosk_ids[]" 
                                                   value="{{ $kiosk->id_kiosk }}" 
                                                   id="kiosk{{ $kiosk->id_kiosk }}"
                                                   data-price="{{ $kiosk->harga_per_paket }}"
                                                   onchange="updateCounter('kiosk'); calculatePricing();">
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
                                            <input type="number" 
                                                   class="form-control form-control-sm day-input" 
                                                   name="kiosk_hari[]" 
                                                   min="1" 
                                                   value="1"
                                                   onchange="validateDayInput(this); calculatePricing();">
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

                    <!-- ITINERARY TAB -->
                    <div class="tab-pane fade" id="pills-itinerary">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5><i class="bi bi-calendar-check"></i> Daily Schedule</h5>
                            <button type="button" class="btn btn-success btn-sm" onclick="addItinerary()">
                                <i class="bi bi-plus-circle"></i> Add Day
                            </button>
                        </div>
                        
                        <div id="itinerary-container">
                            <div class="itinerary-item card mb-3">
                                <div class="card-body">
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
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating Action Button -->
        <div class="fixed-bottom p-3" style="z-index: 1000;">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('paket-wisata.index') }}" class="btn btn-secondary btn-lg shadow">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-lg shadow">
                    <i class="bi bi-check-circle"></i> Save Package
                </button>
            </div>
        </div>
    </form>
</div>

<style>
.item-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.item-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.item-card:has(.item-checkbox:checked) {
    border-color: #0d6efd;
    background-color: #f8f9ff;
}

.nav-pills .nav-link {
    color: #6c757d;
    border-radius: 10px;
}

.nav-pills .nav-link.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.nav-pills .nav-link:hover:not(.active) {
    background-color: #f8f9fa;
}

.bg-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

#cost-breakdown small {
    display: block;
    line-height: 1.8;
}

/* Profit Analysis Card Animation */
#profit-alert {
    transition: all 0.3s ease;
}

#profit-status-badge {
    font-size: 0.85rem;
    transition: all 0.3s ease;
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Highlight positive profit */
.text-success {
    font-weight: 700;
}

/* Company Profit Card Enhancement */
.card.border-primary {
    box-shadow: 0 4px 10px rgba(13, 110, 253, 0.15);
}

.card.border-success {
    box-shadow: 0 4px 10px rgba(25, 135, 84, 0.15);
}

.card.border-danger {
    box-shadow: 0 4px 10px rgba(220, 53, 69, 0.15);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        box-shadow: 0 4px 10px rgba(220, 53, 69, 0.15);
    }
    50% {
        box-shadow: 0 4px 20px rgba(220, 53, 69, 0.3);
    }
}
</style>

<script>
let itineraryCount = 1;

// Update duration info display
function updateDurationInfo() {
    const duration = parseInt(document.getElementById('durasi_hari').value) || 1;
    document.getElementById('package-duration-display').textContent = duration;
}

// Validate day input doesn't exceed package duration
function validateDayInput(input) {
    const duration = parseInt(document.getElementById('durasi_hari').value) || 999;
    const day = parseInt(input.value);
    
    if (day > duration) {
        alert(`Day ${day} exceeds package duration (${duration} days). Adjusted to day ${duration}.`);
        input.value = duration;
    }
    
    if (day < 1) {
        input.value = 1;
    }
}

function updateCounter(type) {
    const checked = document.querySelectorAll(`.item-card[data-type="${type}"] .item-checkbox:checked`).length;
    const badge = document.getElementById(`count-${type}`);
    if (badge) {
        badge.textContent = checked;
        badge.style.display = checked > 0 ? 'inline-block' : 'none';
    }
}

function addItinerary() {
    itineraryCount++;
    const container = document.getElementById('itinerary-container');
    const newItinerary = `
        <div class="itinerary-item card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="mb-0">Day ${itineraryCount}</h6>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeItinerary(this)">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Day:</label>
                        <input type="number" class="form-control day-input" name="itinerary_hari[]" value="${itineraryCount}" min="1" onchange="validateDayInput(this)">
                    </div>
                    <div class="col-md-10">
                        <label class="form-label fw-bold">Title:</label>
                        <input type="text" class="form-control mb-2" name="itinerary_judul[]">
                        <label class="form-label fw-bold">Description:</label>
                        <textarea class="form-control" name="itinerary_deskripsi[]" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', newItinerary);
}

function removeItinerary(btn) {
    btn.closest('.itinerary-item').remove();
}

function toggleDiscount() {
    const tipeDiskon = document.getElementById('tipe_diskon').value;
    const nominalGroup = document.getElementById('discount_nominal_group');
    const persenGroup = document.getElementById('discount_persen_group');
    
    nominalGroup.style.display = tipeDiskon === 'nominal' ? 'block' : 'none';
    persenGroup.style.display = tipeDiskon === 'persen' ? 'block' : 'none';
    
    if (tipeDiskon !== 'nominal') document.getElementById('diskon_nominal').value = 0;
    if (tipeDiskon !== 'persen') document.getElementById('diskon_persen').value = 0;
    
    calculatePricing();
}

function calculatePricing() {
    let totalModal = 0;
    let breakdownHTML = '';
    let itemCount = 0;
    
    console.log('=== Starting Price Calculation ===');
    
    // Calculate Homestay (price x nights)
    const homestayCheckboxes = document.querySelectorAll('.homestay-checkbox:checked');
    homestayCheckboxes.forEach((checkbox) => {
        const card = checkbox.closest('.item-card');
        const price = parseFloat(checkbox.dataset.price) || 0;
        const malamInput = card.querySelector('.homestay-malam');
        const malam = parseInt(malamInput?.value) || 1;
        const name = card.dataset.name || 'Homestay';
        
        const subtotal = price * malam;
        totalModal += subtotal;
        itemCount++;
        
        breakdownHTML += `
            <div class="d-flex justify-content-between align-items-start mb-2">
                <small class="text-truncate" style="max-width: 60%;">
                    <i class="bi bi-house"></i> ${name}
                    <br><span class="text-muted">${formatRinggit(price)} × ${malam} night${malam > 1 ? 's' : ''}</span>
                </small>
                <small class="fw-bold text-end">RM ${formatRinggit(subtotal)}</small>
            </div>
        `;
        
        console.log(`Homestay: ${price} x ${malam} nights = ${subtotal}`);
    });
    
    // Calculate Culinary (price x days)
    const culinaryCheckboxes = document.querySelectorAll('.culinary-checkbox:checked');
    culinaryCheckboxes.forEach(checkbox => {
        const card = checkbox.closest('.item-card');
        const price = parseFloat(checkbox.dataset.price) || 0;
        const hariInput = card.querySelector('input[name="culinary_hari[]"]');
        const hari = parseInt(hariInput?.value) || 1;
        const name = card.dataset.name || 'Culinary';
        
        const subtotal = price * hari;
        totalModal += subtotal;
        itemCount++;
        
        breakdownHTML += `
            <div class="d-flex justify-content-between align-items-start mb-2">
                <small class="text-truncate" style="max-width: 60%;">
                    <i class="bi bi-cup-hot"></i> ${name}
                    <br><span class="text-muted">${formatRinggit(price)} × ${hari} day${hari > 1 ? 's' : ''}</span>
                </small>
                <small class="fw-bold text-end">RM ${formatRinggit(subtotal)}</small>
            </div>
        `;
        
        console.log(`Culinary: ${price} x ${hari} days = ${subtotal}`);
    });
    
    // Calculate Boat (price x days)
    const boatCheckboxes = document.querySelectorAll('.boat-checkbox:checked');
    boatCheckboxes.forEach(checkbox => {
        const card = checkbox.closest('.item-card');
        const price = parseFloat(checkbox.dataset.price) || 0;
        const hariInput = card.querySelector('input[name="boat_hari[]"]');
        const hari = parseInt(hariInput?.value) || 1;
        const name = card.dataset.name || 'Boat';
        
        const subtotal = price * hari;
        totalModal += subtotal;
        itemCount++;
        
        breakdownHTML += `
            <div class="d-flex justify-content-between align-items-start mb-2">
                <small class="text-truncate" style="max-width: 60%;">
                    <i class="bi bi-water"></i> ${name}
                    <br><span class="text-muted">${formatRinggit(price)} × ${hari} day${hari > 1 ? 's' : ''}</span>
                </small>
                <small class="fw-bold text-end">RM ${formatRinggit(subtotal)}</small>
            </div>
        `;
        
        console.log(`Boat: ${price} x ${hari} days = ${subtotal}`);
    });
    
    // Calculate Kiosk (price x days)
    const kioskCheckboxes = document.querySelectorAll('.kiosk-checkbox:checked');
    kioskCheckboxes.forEach(checkbox => {
        const card = checkbox.closest('.item-card');
        const price = parseFloat(checkbox.dataset.price) || 0;
        const hariInput = card.querySelector('input[name="kiosk_hari[]"]');
        const hari = parseInt(hariInput?.value) || 1;
        const name = card.dataset.name || 'Kiosk';
        
        const subtotal = price * hari;
        totalModal += subtotal;
        itemCount++;
        
        breakdownHTML += `
            <div class="d-flex justify-content-between align-items-start mb-2">
                <small class="text-truncate" style="max-width: 60%;">
                    <i class="bi bi-shop"></i> ${name}
                    <br><span class="text-muted">${formatRinggit(price)} × ${hari} day${hari > 1 ? 's' : ''}</span>
                </small>
                <small class="fw-bold text-end">RM ${formatRinggit(subtotal)}</small>
            </div>
        `;
        
        console.log(`Kiosk: ${price} x ${hari} days = ${subtotal}`);
    });
    
    console.log(`Total Modal: ${totalModal}`);
    
    // Update cost breakdown display
    const costBreakdownDiv = document.getElementById('cost-breakdown');
    if (itemCount === 0) {
        costBreakdownDiv.innerHTML = '<small class="text-muted">No items selected yet</small>';
    } else {
        costBreakdownDiv.innerHTML = breakdownHTML;
    }
    
    // Update displays
    document.getElementById('harga_modal').value = totalModal;
    document.getElementById('display_harga_modal').value = formatRinggit(totalModal);
    document.getElementById('breakdown-total').textContent = 'RM ' + formatRinggit(totalModal);
    
    // Calculate Final Price
    const hargaJual = parseFloat(document.getElementById('harga_jual').value) || 0;
    const tipeDiskon = document.getElementById('tipe_diskon').value;
    const diskonNominal = parseFloat(document.getElementById('diskon_nominal').value) || 0;
    const diskonPersen = parseFloat(document.getElementById('diskon_persen').value) || 0;
    
    let hargaFinal = hargaJual;
    
    if (tipeDiskon === 'nominal') {
        hargaFinal = Math.max(0, hargaJual - diskonNominal);
    } else if (tipeDiskon === 'persen') {
        hargaFinal = Math.max(0, hargaJual - (hargaJual * diskonPersen / 100));
    }
    
    document.getElementById('display_harga_final').textContent = 'RM ' + formatRinggit(hargaFinal);
    
    // Calculate profit
    const profit = hargaFinal - totalModal;
    const profitPersen = totalModal > 0 ? ((profit / totalModal) * 100) : 0;
    
    // Update all profit displays
    document.getElementById('display_final_price_profit').textContent = 'RM ' + formatRinggit(hargaFinal);
    document.getElementById('display_cost_price_profit').textContent = 'RM ' + formatRinggit(totalModal);
    document.getElementById('display_profit').textContent = 'RM ' + formatRinggit(profit);
    document.getElementById('display_profit_persen').textContent = profitPersen.toFixed(2) + '%';
    
    // Update profit status badge
    const profitBadge = document.getElementById('profit-status-badge');
    const profitAlert = document.getElementById('profit-alert').closest('.card');
    
    if (hargaFinal === 0) {
        profitBadge.innerHTML = '<i class="bi bi-info-circle"></i> Set selling price to see profit';
        profitBadge.className = 'badge bg-secondary py-2 px-3';
        profitAlert.className = 'card border-secondary mb-3';
    } else if (profit < 0) {
        profitBadge.innerHTML = '<i class="bi bi-exclamation-triangle"></i> LOSS! Price below cost';
        profitBadge.className = 'badge bg-danger py-2 px-3';
        profitAlert.className = 'card border-danger mb-3';
    } else if (profit === 0) {
        profitBadge.innerHTML = '<i class="bi bi-dash-circle"></i> Break Even - No Profit';
        profitBadge.className = 'badge bg-warning py-2 px-3';
        profitAlert.className = 'card border-warning mb-3';
    } else if (profitPersen < 20) {
        profitBadge.innerHTML = '<i class="bi bi-graph-up"></i> Low Margin - Consider increasing price';
        profitBadge.className = 'badge bg-info py-2 px-3';
        profitAlert.className = 'card border-info mb-3';
    } else if (profitPersen >= 20 && profitPersen < 50) {
        profitBadge.innerHTML = '<i class="bi bi-check-circle"></i> Good Profit Margin';
        profitBadge.className = 'badge bg-success py-2 px-3';
        profitAlert.className = 'card border-success mb-3';
    } else {
        profitBadge.innerHTML = '<i class="bi bi-star-fill"></i> Excellent Profit Margin!';
        profitBadge.className = 'badge bg-primary py-2 px-3';
        profitAlert.className = 'card border-primary mb-3';
    }
}

function formatRinggit(amount) {
    return parseFloat(amount).toFixed(2);
}

// Event listeners for real-time calculation
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, initializing...');
    
    // Initialize counters
    ['destinasi', 'homestay', 'kuliner', 'boat', 'kiosk'].forEach(type => {
        updateCounter(type);
    });
    
    // Initialize duration display
    updateDurationInfo();
    
    // Add event listeners for pricing inputs
    const hargaJualInput = document.getElementById('harga_jual');
    const diskonNominalInput = document.getElementById('diskon_nominal');
    const diskonPersenInput = document.getElementById('diskon_persen');
    
    if (hargaJualInput) hargaJualInput.addEventListener('input', calculatePricing);
    if (diskonNominalInput) diskonNominalInput.addEventListener('input', calculatePricing);
    if (diskonPersenInput) diskonPersenInput.addEventListener('input', calculatePricing);
    
    // Add event listeners for quantity changes (days/nights)
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('homestay-malam') ||
            e.target.name === 'culinary_hari[]' ||
            e.target.name === 'boat_hari[]' ||
            e.target.name === 'kiosk_hari[]') {
            calculatePricing();
        }
    });
    
    // Also trigger on input for immediate feedback
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('homestay-malam') ||
            e.target.name === 'culinary_hari[]' ||
            e.target.name === 'boat_hari[]' ||
            e.target.name === 'kiosk_hari[]') {
            calculatePricing();
        }
    });
    
    // Initial calculation
    calculatePricing();
    
    console.log('Initialization complete');
});
</script>
@endsection