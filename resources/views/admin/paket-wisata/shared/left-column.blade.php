@php
    $currentPaketWisata = $paketWisata ?? null;
@endphp

<div class="col-lg-4">
    <div class="package-sidebar">
        <div class="package-panel">
            <div class="package-panel-header">
                <h5 class="mb-1 fw-bold"><i class="bi bi-ui-checks-grid"></i> Package Overview</h5>
                <p class="text-muted small mb-0">Define the core identity, availability, and participant rules before selecting resources.</p>
            </div>
            <div class="package-panel-body">
                <div class="mb-3">
                    <label for="nama_paket" class="form-label fw-bold">Package Name <span class="text-danger">*</span></label>
                    <input type="text"
                        class="form-control @error('nama_paket') is-invalid @enderror"
                        id="nama_paket"
                        name="nama_paket"
                        value="{{ old('nama_paket', $currentPaketWisata?->nama_paket ?? '') }}"
                        placeholder="Example: Chihuy Island Escape"
                        required>
                    @error('nama_paket')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="package-meta-grid mb-3">
                    <div>
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

                    <div>
                        <label for="status" class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="aktif" {{ old('status', $currentPaketWisata?->status ?? 'aktif') === 'aktif' ? 'selected' : '' }}>Active</option>
                            <option value="nonaktif" {{ old('status', $currentPaketWisata?->status ?? 'aktif') === 'nonaktif' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div>
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

                    <div>
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

                <div class="package-helper">
                    <strong>Current duration:</strong> <span id="package-duration-display">{{ old('durasi_hari', $currentPaketWisata?->durasi_hari ?? 3) }}</span> day(s)<br>
                    Choose resources and assign when they are used. The package price is still charged per booking, not per participant.
                </div>
            </div>
        </div>

        <div class="package-panel">
            <div class="package-panel-header">
                <h5 class="mb-1 fw-bold"><i class="bi bi-card-text"></i> Story & Media</h5>
                <p class="text-muted small mb-0">Shape how the package will be understood before you publish it.</p>
            </div>
            <div class="package-panel-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label for="deskripsi" class="form-label fw-bold mb-0">Description</label>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="generateContent('description', this)">
                            <i class="bi bi-stars"></i> AI Draft
                        </button>
                    </div>
                    <textarea class="form-control @error('deskripsi') is-invalid @enderror"
                        id="deskripsi"
                        name="deskripsi"
                        rows="5"
                        placeholder="Explain why this package is worth choosing, what makes it special, and who it is best for.">{{ old('deskripsi', $currentPaketWisata?->deskripsi ?? '') }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-2">
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
                    <small class="text-muted">Accepted: JPG, PNG, WebP up to 2MB.</small>
                </div>

                <div id="foto-preview" class="mt-3" style="display: {{ !empty($currentPaketWisata?->foto_thumbnail) ? 'block' : 'none' }};">
                    <img id="foto-preview-img"
                        src="{{ !empty($currentPaketWisata?->foto_thumbnail) ? asset('storage/' . $currentPaketWisata->foto_thumbnail) : '' }}"
                        alt="Preview"
                        class="img-fluid rounded-4 border"
                        style="max-height: 220px; width: 100%; object-fit: cover;">
                    @if(!empty($currentPaketWisata?->foto_thumbnail))
                        <small class="d-block text-muted mt-2">Current thumbnail preview</small>
                    @endif
                </div>
            </div>
        </div>

        <div class="package-panel">
            <div class="package-panel-header">
                <h5 class="mb-1 fw-bold"><i class="bi bi-cash-coin"></i> Pricing Studio</h5>
                <p class="text-muted small mb-0">Review live cost, suggested selling price, discount strategy, and profitability.</p>
            </div>
            <div class="package-panel-body">
                <div class="card bg-light mb-3 border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1 fw-bold">Cost Breakdown</h6>
                                <small class="text-muted">Built automatically from the selected billable resources.</small>
                            </div>
                            <div class="package-mini-stat text-end">
                                <span class="label">Current Cost</span>
                                <span class="value" id="breakdown-total">{{ format_ringgit($modalAwal ?? 0) }}</span>
                            </div>
                        </div>
                        <div id="cost-breakdown">
                            <small class="text-muted">No billable resources selected yet.</small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Cost Price</label>
                    <div class="input-group">
                        <span class="input-group-text">RM</span>
                        <input type="text"
                            class="form-control bg-white"
                            id="display_harga_modal"
                            value="{{ number_format((float) ($modalAwal ?? 0), 2, '.', ',') }}"
                            readonly>
                    </div>
                    <small class="text-muted">This is the total estimated vendor cost per booking.</small>
                </div>

                <div class="mb-3">
                    <label for="target_profit" class="form-label fw-bold">
                        <i class="bi bi-bullseye text-success"></i> Target Net Profit
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">RM</span>
                        <input type="number"
                            class="form-control"
                            id="target_profit"
                            name="target_profit"
                            value="{{ old('target_profit', 0) }}"
                            min="0"
                            step="0.01"
                            oninput="calculateRecommendedPrice()">
                    </div>
                    <small class="text-muted">Target profit after applying the package pricing buffer {{ package_fee_buffer_label() }}.</small>
                </div>

                <div class="card border-success mb-3" id="recommendation-card" style="display: none;">
                    <div class="card-header bg-success text-white py-2">
                        <h6 class="mb-0"><i class="bi bi-stars"></i> Suggested Selling Price</h6>
                    </div>
                    <div class="card-body py-3 px-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted">Recommended price</small>
                            <span class="fw-bold text-success fs-5" id="rec_selling_price">RM 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted"><i class="bi bi-shield-check"></i> Est. pricing buffer</small>
                            <small class="text-danger fw-bold" id="rec_estimated_fee">RM 0.00</small>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <small class="text-muted"><i class="bi bi-wallet2"></i> Est. profit after buffer</small>
                            <small class="text-success fw-bold" id="rec_net_profit">RM 0.00</small>
                        </div>
                        <button type="button" class="btn btn-success btn-sm w-100" onclick="applyRecommendedPrice()">
                            <i class="bi bi-check-circle"></i> Apply Suggested Price
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

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <label for="tipe_diskon" class="form-label fw-bold">Discount Type</label>
                        <select class="form-select" id="tipe_diskon" name="tipe_diskon" onchange="toggleDiscount()">
                            <option value="none" {{ old('tipe_diskon', $currentPaketWisata?->tipe_diskon ?? 'none') === 'none' ? 'selected' : '' }}>No Discount</option>
                            <option value="nominal" {{ old('tipe_diskon', $currentPaketWisata?->tipe_diskon ?? 'none') === 'nominal' ? 'selected' : '' }}>Fixed Amount (RM)</option>
                            <option value="persen" {{ old('tipe_diskon', $currentPaketWisata?->tipe_diskon ?? 'none') === 'persen' ? 'selected' : '' }}>Percentage (%)</option>
                        </select>
                    </div>

                    <div class="col-md-6" id="discount_nominal_group" style="display: {{ old('tipe_diskon', $currentPaketWisata?->tipe_diskon ?? 'none') === 'nominal' ? 'block' : 'none' }};">
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

                    <div class="col-md-6" id="discount_persen_group" style="display: {{ old('tipe_diskon', $currentPaketWisata?->tipe_diskon ?? 'none') === 'persen' ? 'block' : 'none' }};">
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
                </div>

                <div class="alert alert-success mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>Final Package Price</strong>
                        <h4 class="mb-0" id="display_harga_final">{{ format_ringgit($hargaFinalAwal ?? 0) }}</h4>
                    </div>
                </div>

                <div class="card border-primary mb-0" id="profit-analysis-card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bi bi-graph-up-arrow"></i> Profit Analysis</h6>
                    </div>
                    <div class="card-body" id="profit-alert">
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <span class="text-muted">Final price</span>
                            <span class="fw-bold" id="display_final_price_profit">{{ format_ringgit($hargaFinalAwal ?? 0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <span class="text-muted">Cost price</span>
                            <span class="fw-bold text-danger" id="display_cost_price_profit">{{ format_ringgit($modalAwal ?? 0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <small class="text-muted"><i class="bi bi-shield-check"></i> Pricing buffer ({{ package_fee_buffer_label() }})</small>
                            <small class="text-warning fw-bold" id="display_pricing_buffer">{{ format_ringgit($pricingBufferAwal ?? 0) }}</small>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <span class="fw-bold">Gross profit</span>
                            <span class="fw-bold text-success" id="display_profit">{{ format_ringgit($profitAwal ?? 0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <span class="text-muted">Profit after buffer</span>
                            <span class="fw-bold text-success" id="display_net_profit_after_fee">{{ format_ringgit($netProfitAwal ?? 0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Profit margin</span>
                            <span class="fw-bold text-primary" id="display_profit_persen">{{ number_format($profitPersenAwal ?? 0, 2) }}%</span>
                        </div>
                        <div class="mt-3">
                            <div class="badge bg-secondary py-2 px-3 w-100 text-wrap lh-base" id="profit-status-badge">
                                <i class="bi bi-info-circle"></i> Set a selling price to preview the margin.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
