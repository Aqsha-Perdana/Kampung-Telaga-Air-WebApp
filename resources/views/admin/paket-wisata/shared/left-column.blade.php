@php
    $currentPaketWisata = $paketWisata ?? null;
@endphp

<div class="card" style="position: sticky; top: 80px; max-height: calc(100vh - 100px); display: flex; flex-direction: column;">
    <div class="card-header bg-gradient flex-shrink-0" style="background: linear-gradient(135deg, #667eea 0%, #77c3d5ff 100%);">
        <h5 class="text-black mb-0"><i class="bi bi-info-circle-fill"></i> Detail Package</h5>
    </div>
    <div class="card-body" style="overflow-y: auto; flex: 1;">
        <div class="mb-3">
            <label for="nama_paket" class="form-label fw-bold">Package Name <span class="text-danger">*</span></label>
                   <input type="text"
                           class="form-control @error('nama_paket') is-invalid @enderror"
                           id="nama_paket"
                           name="nama_paket"
                           value="{{ old('nama_paket', $currentPaketWisata?->nama_paket ?? '') }}"
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
                               value="{{ old('durasi_hari', $currentPaketWisata?->durasi_hari ?? 3) }}"
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
                        <option value="aktif" {{ old('status', $currentPaketWisata?->status ?? 'aktif') === 'aktif' ? 'selected' : '' }}>Active</option>
                        <option value="nonaktif" {{ old('status', $currentPaketWisata?->status ?? 'aktif') === 'nonaktif' ? 'selected' : '' }}>Inactive</option>
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
                           value="{{ old('minimum_participants', $currentPaketWisata?->minimum_participants ?? 1) }}"
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
                           value="{{ old('maximum_participants', $currentPaketWisata?->maximum_participants) }}"
                           min="1"
                           max="500"
                           placeholder="Optional">
                    @error('maximum_participants')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="alert alert-info mb-3" id="duration-alert">
            <small>
                <i class="bi bi-info-circle"></i>
                <strong>Package Duration: <span id="package-duration-display">{{ old('durasi_hari', $currentPaketWisata?->durasi_hari ?? 3) }}</span> Days</strong>
                <br>Select items and specify on which day they will be used.
                <br>Package pricing is charged per booking, while participant rules control booking eligibility.
            </small>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label for="deskripsi" class="form-label fw-bold">Description</label>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="generateContent('description', this)">
                    <i class="bi bi-stars"></i> AI Generate
                </button>
            </div>
            <textarea class="form-control @error('deskripsi') is-invalid @enderror"
                      id="deskripsi"
                      name="deskripsi"
                      rows="4"
                      placeholder="Describe the uniqueness of this tour package...">{{ old('deskripsi', $currentPaketWisata?->deskripsi ?? '') }}</textarea>
            @error('deskripsi')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="foto_thumbnail" class="form-label fw-bold">Package Thumbnail Photo</label>
            <input type="file"
                   class="form-control @error('foto_thumbnail') is-invalid @enderror"
                   id="foto_thumbnail"
                   name="foto_thumbnail"
                   accept="image/jpg,image/jpeg,image/png,image/webp"
                   onchange="previewFoto(this)">
            @error('foto_thumbnail')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="text-muted">JPG, PNG, WebP | Max 2MB</small>
            <div id="foto-preview" class="mt-2" style="display: {{ !empty($currentPaketWisata?->foto_thumbnail) ? 'block' : 'none' }};">
                <img id="foto-preview-img" src="{{ !empty($currentPaketWisata?->foto_thumbnail) ? asset('storage/' . $currentPaketWisata->foto_thumbnail) : '' }}" alt="Preview"
                     class="img-thumbnail" style="max-height: 180px; object-fit: cover;">
                @if(!empty($currentPaketWisata?->foto_thumbnail))
                <small class="d-block text-muted mt-2">Current thumbnail preview</small>
                @endif
            </div>
        </div>

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
                    <strong class="text-primary" id="breakdown-total">RM {{ format_ringgit($modalAwal ?? 0) }}</strong>
                </div>
            </div>
        </div>

        <div class="card bg-light mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-currency-dollar"></i> Pricing Information</h6>
                <div class="alert alert-info small mb-3">
                    <i class="bi bi-info-circle-fill"></i> Package pricing is charged per booking, not per participant. Participant rules only control booking eligibility and capacity.
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Cost Price (Modal)</label>
                    <div class="input-group">
                        <span class="input-group-text">RM</span>
                        <input type="text"
                               class="form-control bg-white"
                               id="display_harga_modal"
                               value="{{ format_ringgit($modalAwal ?? 0) }}"
                               readonly>
                    </div>
                    <small class="text-muted">Auto-calculated from selected items</small>
                </div>

                <div class="mb-3">
                    <label for="target_profit" class="form-label fw-bold">
                        <i class="bi bi-bullseye text-success"></i> Target Profit (Bersih)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">RM</span>
                        <input type="number"
                               class="form-control"
                               id="target_profit"
                               name="target_profit"
                               value="0"
                               min="0"
                               step="0.01"
                               oninput="calculateRecommendedPrice()">
                    </div>
                    <small class="text-muted"><i class="bi bi-info-circle"></i> Keuntungan bersih yang diinginkan setelah ditambahkan buffer pembayaran {{ package_fee_buffer_label() }}</small>
                </div>

                <div class="card border-success mb-3" id="recommendation-card" style="display: none;">
                    <div class="card-header bg-success text-white py-2">
                        <h6 class="mb-0"><i class="bi bi-stars"></i> Harga Jual Optimal</h6>
                    </div>
                    <div class="card-body py-2 px-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted">Recommended Price</small>
                            <span class="fw-bold text-success fs-5" id="rec_selling_price">RM 0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted"><i class="bi bi-shield-check"></i> Est. Payment Buffer</small>
                            <small class="text-danger fw-bold" id="rec_estimated_fee">RM 0</small>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted"><i class="bi bi-wallet2"></i> Est. Profit After Buffer</small>
                            <small class="text-success fw-bold" id="rec_net_profit">RM 0</small>
                        </div>
                        <button type="button" class="btn btn-success btn-sm w-100" onclick="applyRecommendedPrice()">
                            <i class="bi bi-check-circle"></i> Gunakan Harga Ini
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="harga_jual" class="form-label fw-bold">Selling Price <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">RM</span>
                        <input type="number"
                               class="form-control @error('harga_jual') is-invalid @enderror"
                               id="harga_jual"
                               name="harga_jual"
                               value="{{ old('harga_jual', $currentPaketWisata?->harga_jual ?? 0) }}"
                               min="0"
                               step="0.01"
                               onchange="calculatePricing()"
                               required>
                    </div>
                    @error('harga_jual')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="tipe_diskon" class="form-label fw-bold">Discount Type</label>
                    <select class="form-select" id="tipe_diskon" name="tipe_diskon" onchange="toggleDiscount()">
                        <option value="none" {{ old('tipe_diskon', $currentPaketWisata?->tipe_diskon ?? 'none') === 'none' ? 'selected' : '' }}>No Discount</option>
                        <option value="nominal" {{ old('tipe_diskon', $currentPaketWisata?->tipe_diskon ?? 'none') === 'nominal' ? 'selected' : '' }}>Nominal (RM)</option>
                        <option value="persen" {{ old('tipe_diskon', $currentPaketWisata?->tipe_diskon ?? 'none') === 'persen' ? 'selected' : '' }}>Percentage (%)</option>
                    </select>
                </div>

                <div class="mb-3" id="discount_nominal_group" style="display: {{ old('tipe_diskon', $currentPaketWisata?->tipe_diskon ?? 'none') === 'nominal' ? 'block' : 'none' }};">
                    <label for="diskon_nominal" class="form-label fw-bold">Discount Amount</label>
                    <div class="input-group">
                        <span class="input-group-text">RM</span>
                        <input type="number"
                               class="form-control"
                               id="diskon_nominal"
                               name="diskon_nominal"
                               value="{{ old('diskon_nominal', $currentPaketWisata?->diskon_nominal ?? 0) }}"
                               min="0"
                               step="0.01">
                    </div>
                </div>

                <div class="mb-3" id="discount_persen_group" style="display: {{ old('tipe_diskon', $currentPaketWisata?->tipe_diskon ?? 'none') === 'persen' ? 'block' : 'none' }};">
                    <label for="diskon_persen" class="form-label fw-bold">Discount Percentage</label>
                    <div class="input-group">
                        <input type="number"
                               class="form-control"
                               id="diskon_persen"
                               name="diskon_persen"
                               value="{{ old('diskon_persen', $currentPaketWisata?->diskon_persen ?? 0) }}"
                               min="0"
                               max="100"
                               step="0.01">
                        <span class="input-group-text">%</span>
                    </div>
                </div>

                <div class="alert alert-success mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>Final Price:</strong>
                        <h4 class="mb-0" id="display_harga_final">RM {{ format_ringgit($hargaFinalAwal ?? 0) }}</h4>
                    </div>
                </div>

                <div class="card border-primary mb-3">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bi bi-cash-stack"></i> Company Profit Analysis</h6>
                    </div>
                    <div class="card-body" id="profit-alert">
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <span class="text-muted">Final Price</span>
                            <span class="fw-bold" id="display_final_price_profit">RM {{ format_ringgit($hargaFinalAwal ?? 0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <span class="text-muted">Cost Price</span>
                            <span class="fw-bold text-danger" id="display_cost_price_profit">RM {{ format_ringgit($modalAwal ?? 0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <small class="text-muted"><i class="bi bi-shield-check"></i> Pricing Buffer ({{ package_fee_buffer_label() }})</small>
                            <small class="text-warning fw-bold" id="display_stripe_fee">RM {{ format_ringgit($pricingBufferAwal ?? 0) }}</small>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <span class="fw-bold">Gross Profit</span>
                            <span class="fw-bold text-success" id="display_profit">RM {{ format_ringgit($profitAwal ?? 0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <span class="text-muted">Profit After Buffer</span>
                            <span class="fw-bold text-success" id="display_net_profit_after_fee">RM {{ format_ringgit($netProfitAwal ?? 0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Profit Margin</span>
                            <span class="fw-bold text-primary" id="display_profit_persen">{{ number_format($profitPersenAwal ?? 0, 2) }}%</span>
                        </div>
                        <div class="mt-3">
                            <div class="badge bg-secondary py-2 px-3 w-100 text-wrap lh-base" id="profit-status-badge">
                                <i class="bi bi-info-circle"></i> Set selling price to see profit
                            </div>
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
