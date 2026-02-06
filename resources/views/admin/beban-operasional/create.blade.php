@extends('layout.sidebar')
@section('content')
<div class="container-fluid px-0 vh-100 d-flex flex-column" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <!-- Header -->
    <div class="text-center text-white py-5">
        <div class="container">
            <div class="mb-3">
                <i class="bi bi-receipt-cutoff display-1 opacity-75"></i>
            </div>
            <h1 class="text-white display-4 fw-bold mb-2">Record Operating Expense</h1>
            <p class="lead mb-0 opacity-75">Keep track of your business expenses professionally</p>
        </div>
    </div>

    <!-- Form Section -->
    <div class="flex-grow-1 pb-5" style="background-color: #f8f9fa; border-radius: 50px 50px 0 0;">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <form action="{{ route('beban-operasional.store') }}" method="POST" enctype="multipart/form-data" id="expenseForm">
                        @csrf
                        
                        <div class="row g-4">
                            <!-- Left Column -->
                            <div class="col-lg-6">
                                <!-- Date & Category Card -->
                                <div class="card border-0 shadow-lg mb-4 overflow-hidden">
                                    <div class="card-header bg-gradient text-white py-3">
                                        <h5 class="mb-0 fw-bold">
                                            <i class="bi bi-calendar-check me-2"></i>Transaction Details
                                        </h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="mb-4">
                                            <label for="tanggal" class="form-label fw-bold text-dark">
                                                <i class="bi bi-calendar-event text-primary me-2"></i>Transaction Date
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" 
                                                   name="tanggal" 
                                                   id="tanggal" 
                                                   class="form-control form-control-lg shadow-sm @error('tanggal') is-invalid @enderror" 
                                                   value="{{ old('tanggal', date('Y-m-d')) }}" 
                                                   required>
                                            @error('tanggal')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted d-block mt-2">
                                                <i class="bi bi-info-circle me-1"></i>When did this expense occur?
                                            </small>
                                        </div>

                                        <div class="mb-0">
                                            <label for="kategori" class="form-label fw-bold text-dark">
                                                <i class="bi bi-tag text-primary me-2"></i>Expense Category
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select name="kategori" 
                                                    id="kategori" 
                                                    class="form-select form-select-lg shadow-sm @error('kategori') is-invalid @enderror" 
                                                    required>
                                                <option value="">-- Select Category --</option>
                                                @foreach($kategoriList as $kat)
                                                    <option value="{{ $kat }}" {{ old('kategori') == $kat ? 'selected' : '' }}>
                                                        {{ $kat }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('kategori')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted d-block mt-2">
                                                <i class="bi bi-info-circle me-1"></i>Choose the appropriate expense category
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Amount & Payment Card -->
                                <div class="card border-0 shadow-lg mb-4 overflow-hidden">
                                    <div class="card-header bg-gradient text-white py-3">
                                        <h5 class="mb-0 fw-bold">
                                            <i class="bi bi-cash-stack me-2"></i>Payment Information
                                        </h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="mb-4">
                                            <label for="jumlah" class="form-label fw-bold text-dark">
                                                <i class="bi bi-currency-dollar text-success me-2"></i>Amount (RM)
                                                <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group input-group-lg shadow-sm">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <strong>RM</strong>
                                                </span>
                                                <input type="number" 
                                                       name="jumlah" 
                                                       id="jumlah" 
                                                       class="form-control border-start-0 @error('jumlah') is-invalid @enderror" 
                                                       value="{{ old('jumlah') }}"
                                                       step="0.01"
                                                       min="0"
                                                       placeholder="0.00"
                                                       required>
                                                @error('jumlah')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="form-text text-muted d-block mt-2">
                                                <i class="bi bi-info-circle me-1"></i>Enter the total expense amount
                                            </small>
                                        </div>

                                        <div class="mb-4">
                                            <label for="metode_pembayaran" class="form-label fw-bold text-dark">
                                                <i class="bi bi-wallet2 text-primary me-2"></i>Payment Method
                                                <span class="text-danger">*</span>
                                            </label>
                                            <div class="row g-3">
                                                @foreach($metodePembayaran as $metode)
                                                    <div class="col-6">
                                                        <input type="radio" 
                                                               class="btn-check" 
                                                               name="metode_pembayaran" 
                                                               id="metode_{{ $loop->index }}" 
                                                               value="{{ $metode }}"
                                                               {{ old('metode_pembayaran') == $metode ? 'checked' : '' }}
                                                               required>
                                                        <label class="btn btn-outline-primary btn-lg w-100 shadow-sm payment-method-btn" for="metode_{{ $loop->index }}">
                                                            <i class="bi bi-{{ $metode == 'Cash' ? 'cash-coin' : 'bank' }} fs-4 d-block mb-2"></i>
                                                            <strong>{{ $metode }}</strong>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @error('metode_pembayaran')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-0">
                                            <label for="nomor_referensi" class="form-label fw-bold text-dark">
                                                <i class="bi bi-hash text-primary me-2"></i>Reference Number
                                            </label>
                                            <input type="text" 
                                                   name="nomor_referensi" 
                                                   id="nomor_referensi" 
                                                   class="form-control form-control-lg shadow-sm @error('nomor_referensi') is-invalid @enderror" 
                                                   value="{{ old('nomor_referensi') }}"
                                                   placeholder="e.g., INV-2024-001, TXN-12345">
                                            @error('nomor_referensi')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted d-block mt-2">
                                                <i class="bi bi-info-circle me-1"></i>Invoice number or transaction reference (Optional)
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-lg-6">
                                <!-- Description Card -->
                                <div class="card border-0 shadow-lg mb-4 overflow-hidden">
                                    <div class="card-header bg-gradient text-white py-3">
                                        <h5 class="mb-0 fw-bold">
                                            <i class="bi bi-file-text me-2"></i>Expense Description
                                        </h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="mb-4">
                                            <label for="deskripsi" class="form-label fw-bold text-dark">
                                                <i class="bi bi-pencil text-primary me-2"></i>Description
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   name="deskripsi" 
                                                   id="deskripsi" 
                                                   class="form-control form-control-lg shadow-sm @error('deskripsi') is-invalid @enderror" 
                                                   value="{{ old('deskripsi') }}"
                                                   placeholder="e.g., Monthly office electricity bill"
                                                   required>
                                            @error('deskripsi')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted d-block mt-2">
                                                <i class="bi bi-info-circle me-1"></i>Brief description of the expense
                                            </small>
                                        </div>

                                        <div class="mb-0">
                                            <label for="keterangan" class="form-label fw-bold text-dark">
                                                <i class="bi bi-card-text text-primary me-2"></i>Additional Notes
                                            </label>
                                            <textarea name="keterangan" 
                                                      id="keterangan" 
                                                      class="form-control shadow-sm @error('keterangan') is-invalid @enderror" 
                                                      rows="6"
                                                      placeholder="Add any additional information or remarks about this expense...">{{ old('keterangan') }}</textarea>
                                            @error('keterangan')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted d-block mt-2">
                                                <i class="bi bi-info-circle me-1"></i>Optional detailed notes for future reference
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Upload Card -->
                                <div class="card border-0 shadow-lg mb-4 overflow-hidden">
                                    <div class="card-header bg-gradient text-white py-3">
                                        <h5 class="mb-0 fw-bold">
                                            <i class="bi bi-paperclip me-2"></i>Supporting Documents
                                        </h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <label for="bukti_pembayaran" class="form-label fw-bold text-dark">
                                            <i class="bi bi-cloud-upload text-primary me-2"></i>Proof of Payment
                                        </label>
                                        
                                        <div class="upload-area border-3 border-dashed rounded-3 p-4 text-center position-relative" id="uploadArea">
                                            <input type="file" 
                                                   name="bukti_pembayaran" 
                                                   id="bukti_pembayaran" 
                                                   class="d-none @error('bukti_pembayaran') is-invalid @enderror"
                                                   accept=".jpg,.jpeg,.png,.pdf"
                                                   onchange="previewFile(this)">
                                            
                                            <div id="uploadPlaceholder">
                                                <i class="bi bi-cloud-arrow-up display-1 text-primary mb-3"></i>
                                                <h5 class="fw-bold mb-2">Click to Upload</h5>
                                                <p class="text-muted mb-3">or drag and drop your file here</p>
                                                <button type="button" class="btn btn-primary btn-lg" onclick="document.getElementById('bukti_pembayaran').click()">
                                                    <i class="bi bi-folder2-open me-2"></i>Browse Files
                                                </button>
                                                <p class="text-muted small mt-3 mb-0">
                                                    <i class="bi bi-info-circle me-1"></i>Supported: JPG, PNG, PDF (Max: 2MB)
                                                </p>
                                            </div>
                                            
                                            <div id="filePreview" class="d-none">
                                                <div class="alert alert-success mb-0">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-file-earmark-check fs-2 me-3"></i>
                                                            <div class="text-start">
                                                                <strong>File Selected</strong>
                                                                <p class="mb-0 small" id="fileName"></p>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearFile()">
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
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-0 shadow-lg">
                                    <div class="card-body p-4">
                                        <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center">
                                            <div class="text-muted">
                                                <i class="bi bi-shield-check text-success me-2"></i>
                                                All information is securely stored
                                            </div>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('beban-operasional.index') }}" class="btn btn-outline-secondary btn-lg px-5">
                                                    <i class="bi bi-x-circle me-2"></i>Cancel
                                                </a>
                                                <button type="reset" class="btn btn-outline-warning btn-lg px-5" onclick="resetForm()">
                                                    <i class="bi bi-arrow-clockwise me-2"></i>Reset
                                                </button>
                                                <button type="submit" class="btn btn-success btn-lg px-5 shadow" id="submitBtn">
                                                    <i class="bi bi-check-circle me-2"></i>Save Expense
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tips Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-0 bg-info bg-opacity-10">
                                    <div class="card-body p-4">
                                        <h6 class="fw-bold text-info mb-3">
                                            <i class="bi bi-lightbulb-fill me-2"></i>Best Practices for Recording Expenses
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                <small>Choose the correct category for accurate reporting</small>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                <small>Always include supporting documents when possible</small>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                <small>Record expenses promptly for accurate bookkeeping</small>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                <small>Add detailed notes for future reference and audits</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.form-control,
.form-select {
    border-radius: 0.5rem;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.form-control:focus,
.form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
    transform: translateY(-2px);
}

.form-control-lg {
    padding: 0.75rem 1rem;
    font-size: 1rem;
}

.card {
    border-radius: 1rem;
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}

.payment-method-btn {
    border-radius: 0.75rem;
    padding: 1.5rem 1rem;
    transition: all 0.3s ease;
    border-width: 2px;
}

.payment-method-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 0.5rem 1rem rgba(102, 126, 234, 0.3);
}

.btn-check:checked + .payment-method-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: #667eea;
    color: white;
}

.upload-area {
    background-color: #f8f9fa;
    border-color: #667eea !important;
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-area:hover {
    background-color: #e9ecef;
    border-color: #764ba2 !important;
}

.btn {
    border-radius: 0.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
}

.shadow-lg {
    box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
}

/* Animation for form sections */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: slideIn 0.5s ease;
}

/* Drag and drop styles */
.upload-area.dragover {
    background-color: #e9ecef;
    border-color: #28a745 !important;
}
</style>

<script>
function previewFile(input) {
    const preview = document.getElementById('filePreview');
    const placeholder = document.getElementById('uploadPlaceholder');
    const fileName = document.getElementById('fileName');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        fileName.textContent = file.name + ' (' + (file.size / 1024).toFixed(2) + ' KB)';
        placeholder.classList.add('d-none');
        preview.classList.remove('d-none');
    }
}

function clearFile() {
    const input = document.getElementById('bukti_pembayaran');
    const preview = document.getElementById('filePreview');
    const placeholder = document.getElementById('uploadPlaceholder');
    
    input.value = '';
    preview.classList.add('d-none');
    placeholder.classList.remove('d-none');
}

function resetForm() {
    clearFile();
    document.querySelectorAll('.btn-check').forEach(radio => radio.checked = false);
}

// Drag and drop functionality
const uploadArea = document.getElementById('uploadArea');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    uploadArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    uploadArea.addEventListener(eventName, () => uploadArea.classList.add('dragover'), false);
});

['dragleave', 'drop'].forEach(eventName => {
    uploadArea.addEventListener(eventName, () => uploadArea.classList.remove('dragover'), false);
});

uploadArea.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    document.getElementById('bukti_pembayaran').files = files;
    previewFile(document.getElementById('bukti_pembayaran'));
}

// Click to upload
uploadArea.addEventListener('click', function(e) {
    if (e.target.id !== 'bukti_pembayaran' && !e.target.closest('button')) {
        document.getElementById('bukti_pembayaran').click();
    }
});

// Form validation before submit
document.getElementById('expenseForm').addEventListener('submit', function(e) {
    const amount = parseFloat(document.getElementById('jumlah').value);
    const submitBtn = document.getElementById('submitBtn');
    
    if (amount <= 0) {
        e.preventDefault();
        alert('Amount must be greater than 0');
        return false;
    }
    
    // Disable submit button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
});

// Real-time amount formatting
document.getElementById('jumlah').addEventListener('input', function(e) {
    const value = parseFloat(e.target.value);
    if (value > 0) {
        e.target.classList.remove('is-invalid');
        e.target.classList.add('is-valid');
    }
});
</script>
@endsection