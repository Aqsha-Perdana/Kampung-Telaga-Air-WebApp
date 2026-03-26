<div class="container-fluid">
    <!-- Header dengan Background Image -->
    <div class="row mb-4">
        <div class="col-md-12 px-0">
            <div class="position-relative" style="height: 200px; border-radius: 10px; overflow: hidden;">
                <!-- Background Image -->
                <img src="{{ asset('assets/images/backgrounds/bg-package.png') }}" 
                     alt="Background" 
                     class="w-100 h-100" 
                     style="object-fit: cover; filter: brightness(0.6);">
                
                <!-- Overlay Text -->
                <div class="position-absolute top-50 start-50 translate-middle text-center text-white" style="z-index: 2;">
                    <h1 class="display-4 fw-bold mb-2" style="color: white !important; text-shadow: 2px 2px 8px rgba(0,0,0,0.8);">Edit Tour Package</h1>
                    <p class="lead mb-0">Update destinations, accommodations, and services for your tour package</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('paket-wisata.update', $paketWisata->id_paket) }}" method="POST" id="paketForm">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- LEFT SIDEBAR - Form Input -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h5 class="text-white mb-0"><i class="bi bi-info-circle-fill"></i> Package Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <small><strong>Package ID:</strong> {{ $paketWisata->id_paket }}</small>
                        </div>

                        <div class="mb-3">
                            <label for="nama_paket" class="form-label fw-bold">Package Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nama_paket') is-invalid @enderror" 
                                   id="nama_paket" 
                                   name="nama_paket" 
                                   value="{{ old('nama_paket', $paketWisata->nama_paket) }}" 
                                   placeholder="Honeymoon Package 3D2N"
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
                                               value="{{ old('durasi_hari', $paketWisata->durasi_hari) }}" 
                                               min="1"
                                               required>
                                        <span class="input-group-text">Days</span>
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
                                        <option value="aktif" {{ old('status', $paketWisata->status) == 'aktif' ? 'selected' : '' }}>Active</option>
                                        <option value="nonaktif" {{ old('status', $paketWisata->status) == 'nonaktif' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="minimum_participants" class="form-label fw-bold">Minimum Participants <span class="text-danger">*</span></label>
                                    <input type="number"
                                           class="form-control @error('minimum_participants') is-invalid @enderror"
                                           id="minimum_participants"
                                           name="minimum_participants"
                                           value="{{ old('minimum_participants', $paketWisata->minimum_participants ?? 1) }}"
                                           min="1"
                                           max="500"
                                           required>
                                    @error('minimum_participants')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="maximum_participants" class="form-label fw-bold">Maximum Participants</label>
                                    <input type="number"
                                           class="form-control @error('maximum_participants') is-invalid @enderror"
                                           id="maximum_participants"
                                           name="maximum_participants"
                                           value="{{ old('maximum_participants', $paketWisata->maximum_participants) }}"
                                           min="1"
                                           max="500"
                                           placeholder="Optional">
                                    @error('maximum_participants')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label fw-bold">Description</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" 
                                      name="deskripsi" 
                                      rows="4"
                                      placeholder="Describe the uniqueness of this tour package...">{{ old('deskripsi', $paketWisata->deskripsi) }}</textarea>
                        </div>

                        <div class="alert alert-warning mb-0">
                            <small><i class="bi bi-exclamation-triangle-fill"></i> <strong>Note:</strong> Changes will affect existing bookings!</small>
                        </div>
                    </div>
                    
                    <!-- Price Information Card -->
                    <div class="card-body border-top">
                        <h6 class="fw-bold mb-3"><i class="bi bi-currency-dollar"></i> Price Information</h6>
                        <div class="alert alert-info small mb-3">
                            <i class="bi bi-info-circle-fill"></i> Package pricing is charged per booking, not per participant. Participant rules only control booking eligibility and capacity.
                        </div>
                        
                        <!-- Cost Price (Auto Calculate) -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Cost Price</label>
                            <div class="input-group">
                                <span class="input-group-text">RM</span>
                                <input type="text" 
                                       class="form-control bg-white" 
                                       id="display_harga_modal" 
                                       value="{{ format_ringgit($paketWisata->harga_modal) }}" 
                                       readonly>
                            </div>
                            <input type="hidden" name="harga_modal" id="harga_modal" value="{{ $paketWisata->harga_modal }}">
                            <small class="text-muted">Automatically calculated from selected items</small>
                        </div>

                        <!-- Selling Price (Manual Input) -->
                        <div class="mb-3">
                            <label for="harga_jual" class="form-label fw-bold">Selling Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">RM</span>
                                <input type="number" 
                                       class="form-control @error('harga_jual') is-invalid @enderror" 
                                       id="harga_jual" 
                                       name="harga_jual" 
                                       value="{{ old('harga_jual', $paketWisata->harga_jual) }}" 
                                       min="0"
                                       step="0.01"
                                       required
                                       onchange="calculatePricing()">
                            </div>
                            @error('harga_jual')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Discount Type -->
                        <div class="mb-3">
                            <label for="tipe_diskon" class="form-label fw-bold">Discount Type</label>
                            <select class="form-select" id="tipe_diskon" name="tipe_diskon" onchange="toggleDiscount()">
                                <option value="none" {{ old('tipe_diskon', $paketWisata->tipe_diskon) == 'none' ? 'selected' : '' }}>No Discount</option>
                                <option value="nominal" {{ old('tipe_diskon', $paketWisata->tipe_diskon) == 'nominal' ? 'selected' : '' }}>Nominal (RM)</option>
                                <option value="persen" {{ old('tipe_diskon', $paketWisata->tipe_diskon) == 'persen' ? 'selected' : '' }}>Percentage (%)</option>
                            </select>
                        </div>

                        <!-- Discount Nominal -->
                        <div class="mb-3" id="discount_nominal_group" style="display: {{ old('tipe_diskon', $paketWisata->tipe_diskon) == 'nominal' ? 'block' : 'none' }};">
                            <label for="diskon_nominal" class="form-label fw-bold">Discount Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">RM</span>
                                <input type="number" 
                                       class="form-control" 
                                       id="diskon_nominal" 
                                       name="diskon_nominal" 
                                       value="{{ old('diskon_nominal', $paketWisata->diskon_nominal) }}" 
                                       min="0"
                                       step="0.01"
                                       onchange="calculatePricing()">
                            </div>
                        </div>

                        <!-- Discount Percentage -->
                        <div class="mb-3" id="discount_persen_group" style="display: {{ old('tipe_diskon', $paketWisata->tipe_diskon) == 'persen' ? 'block' : 'none' }};">
                            <label for="diskon_persen" class="form-label fw-bold">Discount Percentage</label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control" 
                                       id="diskon_persen" 
                                       name="diskon_persen" 
                                       value="{{ old('diskon_persen', $paketWisata->diskon_persen) }}" 
                                       min="0"
                                       max="100"
                                       step="0.01"
                                       onchange="calculatePricing()">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>

                        <!-- Final Price -->
                        <div class="alert alert-success mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Final Price:</strong>
                                <h4 class="mb-0" id="display_harga_final">{{ format_ringgit($paketWisata->harga_final) }}</h4>
                            </div>
                        </div>

                        <!-- Profit Info -->
                        @php
                            $profit = $paketWisata->harga_final - $paketWisata->harga_modal;
                            $profitPersen = $paketWisata->harga_modal > 0 ? (($profit / $paketWisata->harga_modal) * 100) : 0;
                        @endphp
                        <div class="alert alert-info mb-0">
                            <small>
                                <strong>Estimated Profit:</strong> 
                                <span id="display_profit">{{ format_ringgit($profit) }}</span>
                                (<span id="display_profit_persen">{{ number_format($profitPersen, 2) }}%</span>)
                            </small>
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
                            <i class="bi bi-geo-alt-fill"></i> Destination <span class="badge bg-danger ms-1" id="count-destinasi">0</span>
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
                            @php
                                $selectedDestinasi = $paketWisata->destinasis->pluck('id_destinasi')->toArray();
                            @endphp
                            @forelse($destinasis as $destinasi)
                            @php
                                $isChecked = in_array($destinasi->id_destinasi, $selectedDestinasi);
                                $hariKe = $isChecked ? $paketWisata->destinasis->where('id_destinasi', $destinasi->id_destinasi)->first()->pivot->hari_ke : 1;
                            @endphp
                            <div class="col-md-6">
                                <div class="card h-100 item-card {{ $isChecked ? 'checked' : '' }}" data-type="destinasi">
                                    <div class="card-body">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input item-checkbox" 
                                                   type="checkbox" 
                                                   name="destinasi_ids[]" 
                                                   value="{{ $destinasi->id_destinasi }}" 
                                                   id="dest{{ $destinasi->id_destinasi }}"
                                                   {{ $isChecked ? 'checked' : '' }}
                                                   onchange="updateCounter('destinasi')">
                                            <label class="form-check-label fw-bold" for="dest{{ $destinasi->id_destinasi }}">
                                                {{ $destinasi->nama }}
                                            </label>
                                        </div>
                                        <p class="text-muted small mb-2"><i class="bi bi-geo-alt"></i> {{ $destinasi->lokasi }}</p>
                                        @if($destinasi->fotos && $destinasi->fotos->count() > 0)
                                        <img src="{{ asset('storage/'.$destinasi->fotos->first()->foto) }}" 
                                             class="img-fluid rounded mb-2" 
                                             style="height: 120px; width: 100%; object-fit: cover;">
                                        @endif
                                        <div class="mt-2">
                                            <label class="form-label small">Day:</label>
                                            <input type="number" 
                                                   class="form-control form-control-sm" 
                                                   name="destinasi_hari[]" 
                                                   min="1" 
                                                   value="{{ $hariKe }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i> No destination data available yet
                                </div>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- HOMESTAY TAB -->
                    <div class="tab-pane fade" id="pills-homestay">
                        <div class="row g-3">
                            @php
                                $selectedHomestay = $paketWisata->homestays->pluck('id_homestay')->toArray();
                            @endphp
                            @forelse($homestays as $homestay)
                            @php
                                $isChecked = in_array($homestay->id_homestay, $selectedHomestay);
                                $jumlahMalam = $isChecked ? $paketWisata->homestays->where('id_homestay', $homestay->id_homestay)->first()->pivot->jumlah_malam : 1;
                            @endphp
                            <div class="col-md-6">
                                <div class="card h-100 item-card {{ $isChecked ? 'checked' : '' }}" data-type="homestay">
                                    <div class="card-body">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input item-checkbox homestay-checkbox"
                                                   type="checkbox" 
                                                   name="homestay_ids[]" 
                                                   value="{{ $homestay->id_homestay }}" 
                                                   id="home{{ $homestay->id_homestay }}"
                                                   {{ $isChecked ? 'checked' : '' }}
                                                   data-price="{{ $homestay->harga_per_malam }}"
                                                   onchange="updateCounter('homestay'); calculatePricing();">
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
                                            <input type="number" 
                                                   class="form-control form-control-sm homestay-malam" 
                                                   name="homestay_malam[]" 
                                                   min="1" 
                                                   value="{{ $jumlahMalam }}"
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
                            @php
                                $selectedCulinary = $paketWisata->paketCulinaries->pluck('id')->toArray();
                            @endphp
                            @forelse($culinaries as $culinary)
                                @foreach($culinary->pakets as $paket)
                                @php
                                    $isChecked = in_array($paket->id, $selectedCulinary);
                                    $hariKe = $isChecked ? $paketWisata->paketCulinaries->where('id', $paket->id)->first()->pivot->hari_ke : 1;
                                @endphp
                                <div class="col-md-6">
                                    <div class="card h-100 item-card {{ $isChecked ? 'checked' : '' }}" data-type="kuliner">
                                        <div class="card-body">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input item-checkbox culinary-checkbox"
                                                       type="checkbox" 
                                                       name="culinary_paket_ids[]" 
                                                       value="{{ $paket->id }}" 
                                                       id="cul{{ $paket->id }}"
                                                       {{ $isChecked ? 'checked' : '' }}
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
                                                       class="form-control form-control-sm" 
                                                       name="culinary_hari[]" 
                                                       min="1" 
                                                       value="{{ $hariKe }}">
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
                            @php
                                $selectedBoat = $paketWisata->boats->pluck('id')->toArray();
                            @endphp
                            @forelse($boats as $boat)
                            @php
                                $isChecked = in_array($boat->id, $selectedBoat);
                                $hariKe = $isChecked ? $paketWisata->boats->where('id', $boat->id)->first()->pivot->hari_ke : 1;
                            @endphp
                            <div class="col-md-6">
                                <div class="card h-100 item-card {{ $isChecked ? 'checked' : '' }}" data-type="boat">
                                    <div class="card-body">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input item-checkbox boat-checkbox"
                                                   type="checkbox" 
                                                   name="boat_ids[]" 
                                                   value="{{ $boat->id }}" 
                                                   id="boat{{ $boat->id }}"
                                                   {{ $isChecked ? 'checked' : '' }}
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
                                                   class="form-control form-control-sm" 
                                                   name="boat_hari[]" 
                                                   min="1" 
                                                   value="{{ $hariKe }}">
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
                            @php
                                $selectedKiosk = $paketWisata->kiosks->pluck('id_kiosk')->toArray();
                            @endphp
                            @forelse($kiosks as $kiosk)
                            @php
                                $isChecked = in_array($kiosk->id_kiosk, $selectedKiosk);
                                $hariKe = $isChecked ? $paketWisata->kiosks->where('id_kiosk', $kiosk->id_kiosk)->first()->pivot->hari_ke : 1;
                            @endphp
                            <div class="col-md-6">
                                <div class="card h-100 item-card {{ $isChecked ? 'checked' : '' }}" data-type="kiosk">
                                    <div class="card-body">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input item-checkbox kiosk-checkbox" 
                                                   type="checkbox" 
                                                   name="kiosk_ids[]" 
                                                   value="{{ $kiosk->id_kiosk }}" 
                                                   id="kiosk{{ $kiosk->id_kiosk }}"
                                                   {{ $isChecked ? 'checked' : '' }}
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
                                                   class="form-control form-control-sm" 
                                                   name="kiosk_hari[]" 
                                                   min="1" 
                                                   value="{{ $hariKe }}">
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
                            @forelse($paketWisata->itineraries as $itinerary)
                            <div class="itinerary-item card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <h6 class="mb-0">Day {{ $itinerary->hari_ke }}</h6>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="removeItinerary(this)">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label class="form-label fw-bold">Day:</label>
                                            <input type="number" class="form-control" name="itinerary_hari[]" value="{{ $itinerary->hari_ke }}" min="1">
                                        </div>
                                        <div class="col-md-10">
                                            <label class="form-label fw-bold">Title:</label>
                                            <input type="text" class="form-control mb-2" name="itinerary_judul[]" value="{{ $itinerary->judul_hari }}" placeholder="Example: Arrival & Check-in">
                                            <label class="form-label fw-bold">Description:</label>
                                            <textarea class="form-control" name="itinerary_deskripsi[]" rows="2" placeholder="Describe activities for this day...">{{ $itinerary->deskripsi_kegiatan }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="itinerary-item card mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label class="form-label fw-bold">Day:</label>
                                            <input type="number" class="form-control" name="itinerary_hari[]" value="1" min="1">
                                        </div>
                                        <div class="col-md-10">
                                            <label class="form-label fw-bold">Title:</label>
                                            <input type="text" class="form-control mb-2" name="itinerary_judul[]" placeholder="Example: Arrival & Check-in">
                                            <label class="form-label fw-bold">Description:</label>
                                            <textarea class="form-control" name="itinerary_deskripsi[]" rows="2" placeholder="Describe activities for this day..."></textarea>
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

        <!-- Floating Action Button -->
        <div class="fixed-bottom p-3" style="z-index: 1000;">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('paket-wisata.index') }}" class="btn btn-secondary btn-lg shadow">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-lg shadow">
                    <i class="bi bi-save"></i> Update Tour Package
                </button>
            </div>
        </div>
    </form>
</div>

