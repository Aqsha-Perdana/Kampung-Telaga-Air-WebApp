<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="bi bi-suitcase-lg"></i> Add New Tour Package</h2>
            <p class="text-muted">Select destinations, accommodations, and services to create attractive tour packages.</p>
        </div>
    </div>

    {{-- Smart Recommendations Section --}}
    @if(isset($recommendationStats) && $recommendationStats['total_recommendations'] > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="bi bi-lightbulb-fill text-warning"></i> 
                            Smart Recommendations
                            <span class="badge bg-primary ms-2">{{ $recommendationStats['total_recommendations'] }} suggestions</span>
                        </h5>
                        <small class="text-muted">AI-powered suggestions based on unused and low-performing resources</small>
                    </div>
                    <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#recommendationsPanel">
                        <i class="bi bi-chevron-down"></i> Show/Hide
                    </button>
                </div>
                <div class="collapse show" id="recommendationsPanel">
                    <div class="card-body pt-0">
                        {{-- Stats Overview --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="bg-danger bg-opacity-10 rounded p-3 text-center">
                                    <h4 class="text-danger mb-0">{{ $recommendationStats['total_unused'] }}</h4>
                                    <small class="text-muted">Never Used</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-warning bg-opacity-10 rounded p-3 text-center">
                                    <h4 class="text-warning mb-0">{{ $recommendationStats['total_never_sold'] }}</h4>
                                    <small class="text-muted">Never Sold</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-info bg-opacity-10 rounded p-3 text-center">
                                    <h4 class="text-info mb-0">{{ $recommendationStats['total_low_performing'] }}</h4>
                                    <small class="text-muted">Low Performing</small>
                                </div>
                            </div>
                        </div>

                        {{-- Suggested Combo --}}
                        @if($recommendations['suggested_combo']['has_suggestions'])
                        <div class="alert alert-success mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong><i class="bi bi-magic"></i> Suggested Package Combination</strong>
                                <button type="button" class="btn btn-success btn-sm" onclick="applySuggestedCombo()">
                                    <i class="bi bi-check-all"></i> Apply All Suggestions
                                </button>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($recommendations['suggested_combo']['items'] as $type => $item)
                                <span class="badge bg-white text-dark border">
                                    <i class="bi bi-{{ $type == 'boat' ? 'water' : ($type == 'homestay' ? 'house' : 'geo-alt') }}"></i>
                                    {{ $item['name'] }}
                                    <small class="text-muted">({{ $item['formatted_price'] }})</small>
                                </span>
                                @endforeach
                            </div>
                            @if($recommendations['suggested_combo']['estimated_cost'] > 0)
                            <small class="text-muted mt-2 d-block">Estimated Cost: RM {{ number_format($recommendations['suggested_combo']['estimated_cost'], 2) }}</small>
                            @endif
                        </div>
                        @endif

                        {{-- Recommendation Tabs --}}
                        <ul class="nav nav-tabs nav-fill" id="recTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#rec-boats">
                                    <i class="bi bi-water"></i> Boats
                                    <span class="badge bg-secondary">{{ count($recommendations['boats']['never_used']) + count($recommendations['boats']['never_sold']) }}</span>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#rec-homestays">
                                    <i class="bi bi-house"></i> Homestays
                                    <span class="badge bg-secondary">{{ count($recommendations['homestays']['never_used']) + count($recommendations['homestays']['never_sold']) }}</span>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#rec-destinations">
                                    <i class="bi bi-geo-alt"></i> Destinations
                                    <span class="badge bg-secondary">{{ count($recommendations['destinations']['never_used']) + count($recommendations['destinations']['never_sold']) }}</span>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#rec-culinaries">
                                    <i class="bi bi-cup-hot"></i> Culinaries
                                    <span class="badge bg-secondary">{{ count($recommendations['culinaries']['never_used']) + count($recommendations['culinaries']['never_sold']) }}</span>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#rec-kiosks">
                                    <i class="bi bi-shop"></i> Kiosks
                                    <span class="badge bg-secondary">{{ count($recommendations['kiosks']['never_used']) + count($recommendations['kiosks']['never_sold']) }}</span>
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content p-3 bg-light rounded-bottom">
                            {{-- Boats Tab --}}
                            <div class="tab-pane fade show active" id="rec-boats">
                                @if(count($recommendations['boats']['never_used']) > 0 || count($recommendations['boats']['never_sold']) > 0)
                                <div class="row g-2">
                                    @foreach($recommendations['boats']['never_used'] as $item)
                                    <div class="col-md-4">
                                        <div class="card h-100 border-danger">
                                            <div class="card-body p-2">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong class="small">{{ $item['name'] }}</strong>
                                                        <br><span class="badge bg-danger">{{ $item['reason'] }}</span>
                                                        <br><small class="text-success">{{ $item['formatted_price'] }}</small>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRecommendedItem('boat', {{ $item['id'] }})">
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    @foreach($recommendations['boats']['never_sold'] as $item)
                                    <div class="col-md-4">
                                        <div class="card h-100 border-warning">
                                            <div class="card-body p-2">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong class="small">{{ $item['name'] }}</strong>
                                                        <br><span class="badge bg-warning text-dark">{{ $item['reason'] }}</span>
                                                        <br><small class="text-success">{{ $item['formatted_price'] }}</small>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRecommendedItem('boat', {{ $item['id'] }})">
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <p class="text-muted text-center mb-0"><i class="bi bi-check-circle text-success"></i> All boats are being utilized well!</p>
                                @endif
                            </div>

                            {{-- Homestays Tab --}}
                            <div class="tab-pane fade" id="rec-homestays">
                                @if(count($recommendations['homestays']['never_used']) > 0 || count($recommendations['homestays']['never_sold']) > 0)
                                <div class="row g-2">
                                    @foreach($recommendations['homestays']['never_used'] as $item)
                                    <div class="col-md-4">
                                        <div class="card h-100 border-danger">
                                            <div class="card-body p-2">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong class="small">{{ $item['name'] }}</strong>
                                                        <br><span class="badge bg-danger">{{ $item['reason'] }}</span>
                                                        <br><small class="text-success">{{ $item['formatted_price'] }}/night</small>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRecommendedItem('homestay', {{ $item['id'] }})">
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    @foreach($recommendations['homestays']['never_sold'] as $item)
                                    <div class="col-md-4">
                                        <div class="card h-100 border-warning">
                                            <div class="card-body p-2">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong class="small">{{ $item['name'] }}</strong>
                                                        <br><span class="badge bg-warning text-dark">{{ $item['reason'] }}</span>
                                                        <br><small class="text-success">{{ $item['formatted_price'] }}/night</small>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRecommendedItem('homestay', {{ $item['id'] }})">
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <p class="text-muted text-center mb-0"><i class="bi bi-check-circle text-success"></i> All homestays are being utilized well!</p>
                                @endif
                            </div>

                            {{-- Destinations Tab --}}
                            <div class="tab-pane fade" id="rec-destinations">
                                @if(count($recommendations['destinations']['never_used']) > 0 || count($recommendations['destinations']['never_sold']) > 0)
                                <div class="row g-2">
                                    @foreach($recommendations['destinations']['never_used'] as $item)
                                    <div class="col-md-4">
                                        <div class="card h-100 border-danger">
                                            <div class="card-body p-2">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong class="small">{{ $item['name'] }}</strong>
                                                        <br><span class="badge bg-danger">{{ $item['reason'] }}</span>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRecommendedItem('destination', {{ $item['id'] }})">
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    @foreach($recommendations['destinations']['never_sold'] as $item)
                                    <div class="col-md-4">
                                        <div class="card h-100 border-warning">
                                            <div class="card-body p-2">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong class="small">{{ $item['name'] }}</strong>
                                                        <br><span class="badge bg-warning text-dark">{{ $item['reason'] }}</span>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRecommendedItem('destination', {{ $item['id'] }})">
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <p class="text-muted text-center mb-0"><i class="bi bi-check-circle text-success"></i> All destinations are being utilized well!</p>
                                @endif
                            </div>

                            {{-- Culinaries Tab --}}
                            <div class="tab-pane fade" id="rec-culinaries">
                                @if(count($recommendations['culinaries']['never_used']) > 0 || count($recommendations['culinaries']['never_sold']) > 0)
                                <div class="row g-2">
                                    @foreach($recommendations['culinaries']['never_used'] as $item)
                                    <div class="col-md-4">
                                        <div class="card h-100 border-danger">
                                            <div class="card-body p-2">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong class="small">{{ $item['name'] }}</strong>
                                                        <br><span class="badge bg-danger">{{ $item['reason'] }}</span>
                                                        <br><small class="text-success">{{ $item['formatted_price'] }}</small>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRecommendedItem('culinary', {{ $item['id'] }})">
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    @foreach($recommendations['culinaries']['never_sold'] as $item)
                                    <div class="col-md-4">
                                        <div class="card h-100 border-warning">
                                            <div class="card-body p-2">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong class="small">{{ $item['name'] }}</strong>
                                                        <br><span class="badge bg-warning text-dark">{{ $item['reason'] }}</span>
                                                        <br><small class="text-success">{{ $item['formatted_price'] }}</small>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRecommendedItem('culinary', {{ $item['id'] }})">
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <p class="text-muted text-center mb-0"><i class="bi bi-check-circle text-success"></i> All culinaries are being utilized well!</p>
                                @endif
                            </div>

                            {{-- Kiosks Tab --}}
                            <div class="tab-pane fade" id="rec-kiosks">
                                @if(count($recommendations['kiosks']['never_used']) > 0 || count($recommendations['kiosks']['never_sold']) > 0)
                                <div class="row g-2">
                                    @foreach($recommendations['kiosks']['never_used'] as $item)
                                    <div class="col-md-4">
                                        <div class="card h-100 border-danger">
                                            <div class="card-body p-2">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong class="small">{{ $item['name'] }}</strong>
                                                        <br><span class="badge bg-danger">{{ $item['reason'] }}</span>
                                                        <br><small class="text-success">{{ $item['formatted_price'] }}</small>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRecommendedItem('kiosk', {{ $item['id'] }})">
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    @foreach($recommendations['kiosks']['never_sold'] as $item)
                                    <div class="col-md-4">
                                        <div class="card h-100 border-warning">
                                            <div class="card-body p-2">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong class="small">{{ $item['name'] }}</strong>
                                                        <br><span class="badge bg-warning text-dark">{{ $item['reason'] }}</span>
                                                        <br><small class="text-success">{{ $item['formatted_price'] }}</small>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRecommendedItem('kiosk', {{ $item['id'] }})">
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <p class="text-muted text-center mb-0"><i class="bi bi-check-circle text-success"></i> All kiosks are being utilized well!</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

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
                                        <option value="nonaktif">Inactive</option>
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
                                           value="{{ old('minimum_participants', 1) }}"
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
                                           value="{{ old('maximum_participants') }}"
                                           min="1"
                                           max="500"
                                           placeholder="Optional">
                                    @error('maximum_participants')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Duration Info Alert -->
                        <div class="alert alert-info mb-3" id="duration-alert">
                            <small>
                                <i class="bi bi-info-circle"></i> 
                                <strong>Package Duration: <span id="package-duration-display">3</span> Days</strong>
                                <br>Select items and specify on which day they will be used.
                                <br>Package pricing is charged per booking, while participant rules control booking eligibility.
                            </small>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label for="deskripsi" class="form-label fw-bold">Description</label>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="generateContent('description')">
                                    <i class="bi bi-stars"></i> AI Generate
                                </button>
                            </div>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" 
                                      name="deskripsi" 
                                      rows="4" 
                                      required>{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                <div class="alert alert-info small mb-3">
                                    <i class="bi bi-info-circle-fill"></i> Package pricing is charged per booking, not per participant. Participant rules only control booking eligibility and capacity.
                                </div>
                                
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
                
                {{-- Global Generate Button --}}
                <div class="mb-3 text-end">
                     <button type="button" class="btn btn-primary btn-sm" onclick="generateContent()">
                        <i class="bi bi-stars"></i> Auto-Generate Content
                    </button>
                </div>

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
                                                   id="home{{ $homestay->id_homestay }}"
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
                        <div class="accordion" id="accordionCulinary">
                            @forelse($culinaries as $index => $culinary)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingCul{{ $culinary->id }}">
                                    <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCul{{ $culinary->id }}">
                                        {{ $culinary->nama }}
                                        <span class="badge bg-secondary ms-2">{{ $culinary->pakets->count() }} Packages</span>
                                    </button>
                                </h2>
                                <div id="collapseCul{{ $culinary->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#accordionCulinary">
                                    <div class="accordion-body">
                                        @if($culinary->deskripsi)
                                            <p class="small text-muted mb-3">{{ $culinary->deskripsi }}</p>
                                        @endif
                                        
                                        <div class="row g-3">
                                            @forelse($culinary->pakets as $paket)
                                            <div class="col-md-12">
                                                <div class="card h-100 item-card" data-type="kuliner" data-price="{{ $paket->harga }}" data-name="{{ $culinary->nama }} - {{ $paket->nama_paket }}">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div class="form-check">
                                                                <input class="form-check-input item-checkbox culinary-checkbox" 
                                                                       type="checkbox" 
                                                                       name="culinary_paket_ids[]" 
                                                                       value="{{ $paket->id }}" 
                                                                       id="cul{{ $paket->id }}"
                                                                       data-price="{{ $paket->harga }}"
                                                                       onchange="updateCounter('kuliner'); calculatePricing();">
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

