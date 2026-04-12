@php
    $isEdit = isset($bebanOperasional);
    $selectedPaymentMethod = old('metode_pembayaran', $bebanOperasional->metode_pembayaran ?? '');
    $existingProofUrl = $isEdit && $bebanOperasional->bukti_pembayaran
        ? Storage::url($bebanOperasional->bukti_pembayaran)
        : null;
@endphp

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm expense-form-card h-100">
            <div class="card-header bg-white border-0 pb-0">
                <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                    <div>
                        <p class="text-primary text-uppercase small fw-semibold mb-1">Expense Form</p>
                        <h4 class="mb-1 fw-bold">{{ $isEdit ? 'Edit operational expense' : 'Record a new operational expense' }}</h4>
                        <p class="text-muted mb-0">Fill in the transaction details, payment information, and supporting notes in one place.</p>
                    </div>
                    @if($isEdit)
                    <span class="badge rounded-pill text-bg-light border px-3 py-2">
                        Code: {{ $bebanOperasional->kode_transaksi }}
                    </span>
                    @endif
                </div>
            </div>
            <div class="card-body pt-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="tanggal" class="form-label fw-semibold">Transaction Date <span class="text-danger">*</span></label>
                        <input
                            type="date"
                            name="tanggal"
                            id="tanggal"
                            class="form-control @error('tanggal') is-invalid @enderror"
                            value="{{ old('tanggal', $isEdit ? $bebanOperasional->tanggal->format('Y-m-d') : date('Y-m-d')) }}"
                            required>
                        @error('tanggal')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="kategori" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select
                            name="kategori"
                            id="kategori"
                            class="form-select @error('kategori') is-invalid @enderror"
                            required>
                            <option value="">Select category</option>
                            @foreach($kategoriList as $kat)
                            <option value="{{ $kat }}" {{ old('kategori', $bebanOperasional->kategori ?? '') == $kat ? 'selected' : '' }}>
                                {{ $kat }}
                            </option>
                            @endforeach
                        </select>
                        @error('kategori')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-7">
                        <label for="deskripsi" class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="deskripsi"
                            id="deskripsi"
                            class="form-control @error('deskripsi') is-invalid @enderror"
                            value="{{ old('deskripsi', $bebanOperasional->deskripsi ?? '') }}"
                            placeholder="Example: Monthly office electricity bill"
                            required>
                        @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-5">
                        <label for="jumlah" class="form-label fw-semibold">Amount (MYR) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input
                                type="number"
                                name="jumlah"
                                id="jumlah"
                                class="form-control @error('jumlah') is-invalid @enderror"
                                value="{{ old('jumlah', $bebanOperasional->jumlah ?? '') }}"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                required>
                            @error('jumlah')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="metode_pembayaran" class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                        <select
                            name="metode_pembayaran"
                            id="metode_pembayaran"
                            class="form-select @error('metode_pembayaran') is-invalid @enderror"
                            required>
                            <option value="">Select payment method</option>
                            @foreach($metodePembayaran as $metode)
                            <option value="{{ $metode }}" {{ $selectedPaymentMethod === $metode ? 'selected' : '' }}>
                                {{ $metode }}
                            </option>
                            @endforeach
                        </select>
                        @error('metode_pembayaran')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="nomor_referensi" class="form-label fw-semibold">Reference Number</label>
                        <input
                            type="text"
                            name="nomor_referensi"
                            id="nomor_referensi"
                            class="form-control @error('nomor_referensi') is-invalid @enderror"
                            value="{{ old('nomor_referensi', $bebanOperasional->nomor_referensi ?? '') }}"
                            placeholder="Invoice or transfer reference">
                        @error('nomor_referensi')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="keterangan" class="form-label fw-semibold">Additional Notes</label>
                        <textarea
                            name="keterangan"
                            id="keterangan"
                            rows="5"
                            class="form-control @error('keterangan') is-invalid @enderror"
                            placeholder="Add optional notes for audit trail, context, or follow-up details...">{{ old('keterangan', $bebanOperasional->keterangan ?? '') }}</textarea>
                        @error('keterangan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card border-0 shadow-sm expense-form-card mb-4">
            <div class="card-header bg-white border-0 pb-0">
                <h5 class="mb-1 fw-bold">Supporting File</h5>
                <p class="text-muted small mb-0">Upload receipt, invoice, or transfer proof if available.</p>
            </div>
            <div class="card-body pt-4">
                @if($existingProofUrl)
                <div class="alert alert-light border d-flex justify-content-between align-items-center gap-3 mb-3">
                    <div>
                        <small class="text-muted d-block">Current file</small>
                        <span class="fw-semibold">Existing proof attached</span>
                    </div>
                    <a href="{{ $existingProofUrl }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                </div>
                @endif

                <div class="expense-upload-shell" id="uploadArea">
                    <input
                        type="file"
                        name="bukti_pembayaran"
                        id="bukti_pembayaran"
                        class="d-none @error('bukti_pembayaran') is-invalid @enderror"
                        accept=".jpg,.jpeg,.png,.pdf">

                    <div id="uploadEmptyState">
                        <div class="expense-upload-icon">
                            <i class="bi bi-cloud-arrow-up"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Upload proof of payment</h6>
                        <p class="text-muted small mb-3">Drag and drop a file here, or browse from your device.</p>
                        <button type="button" class="btn btn-outline-primary" id="uploadBrowseButton">Browse file</button>
                        <small class="text-muted d-block mt-3">Accepted: JPG, PNG, PDF up to 2MB.</small>
                    </div>

                    <div id="uploadFilledState" class="d-none">
                        <div class="alert alert-success mb-0">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <small class="text-muted d-block">Selected file</small>
                                    <div class="fw-semibold" id="fileName">-</div>
                                    <small class="text-muted" id="fileMeta"></small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="clearUploadButton">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @error('bukti_pembayaran')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="card border-0 shadow-sm expense-form-card mb-4">
            <div class="card-header bg-white border-0 pb-0">
                <h5 class="mb-1 fw-bold">Quick Guidance</h5>
                <p class="text-muted small mb-0">Use this checklist to keep reporting neat and consistent.</p>
            </div>
            <div class="card-body pt-4">
                <div class="expense-guidance-list">
                    <div class="expense-guidance-item">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>Use the closest category so the expense appears in the right financial report bucket.</span>
                    </div>
                    <div class="expense-guidance-item">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>Fill the reference number when payment comes from transfer, invoice, or receipt.</span>
                    </div>
                    <div class="expense-guidance-item">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>Add proof of payment whenever possible for easier verification later.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm expense-form-card">
            <div class="card-header bg-white border-0 pb-0">
                <h5 class="mb-1 fw-bold">Actions</h5>
                <p class="text-muted small mb-0">Review the details, then save the transaction.</p>
            </div>
            <div class="card-body pt-4">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        {{ $submitLabel ?? 'Save Expense' }}
                    </button>
                    <button type="reset" class="btn btn-light border" id="resetExpenseForm">
                        Reset Form
                    </button>
                    <a href="{{ route('beban-operasional.index') }}" class="btn btn-outline-secondary">
                        Back to Expense List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
