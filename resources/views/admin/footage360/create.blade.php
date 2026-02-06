@extends('layout.sidebar')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-auto">
            <div class="bg-primary text-white rounded-3 p-3 shadow-sm">
                <i class="fas fa-vr-cardboard fa-2x"></i>
            </div>
        </div>
        <div class="col">
            <h1 class="h3 mb-1 fw-bold text-gray-800">Add 360° Footage</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="{{ route('footage360.index') }}" class="text-decoration-none">Footage Library</a></li>
                    <li class="breadcrumb-item active" aria-current="page">New Upload</li>
                </ol>
            </nav>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="card-title mb-0 fw-bold">General Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('footage360.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf

                        <div class="mb-4">
                            <label for="id_destinasi" class="form-label fw-semibold">Target Destination <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                <select name="id_destinasi" id="id_destinasi" class="form-select border-start-0 bg-light @error('id_destinasi') is-invalid @enderror" required>
                                    <option value="">-- Select Destination --</option>
                                    @foreach($destinasis as $destinasi)
                                        <option value="{{ $destinasi->id_destinasi }}" {{ old('id_destinasi') == $destinasi->id_destinasi ? 'selected' : '' }}>
                                            {{ $destinasi->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('id_destinasi')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="judul" class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control bg-light @error('judul') is-invalid @enderror" 
                                   id="judul" 
                                   name="judul" 
                                   value="{{ old('judul') }}" 
                                   required
                                   placeholder="e.g. Main Entrance Gate - North Wing">
                            @error('judul')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="deskripsi" class="form-label fw-semibold">Description</label>
                            <textarea class="form-control bg-light @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" 
                                      name="deskripsi" 
                                      rows="3"
                                      placeholder="Briefly describe this location..."></textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        <div class="mb-4">
                            <label for="file_foto" class="form-label fw-semibold d-flex justify-content-between">
                                <span>360° Media File <span class="text-danger">*</span></span>
                                <span class="badge rounded-pill bg-info text-dark">Cloudinary Enabled</span>
                            </label>
                            <div class="upload-zone p-4 border border-2 border-dashed rounded-3 text-center bg-light transition" id="dropzone">
                                <input type="file" class="form-control mb-2 @error('file_foto') is-invalid @enderror" id="file_foto" name="file_foto" required>
                                <p class="text-muted small mb-0"><i class="fas fa-info-circle me-1"></i> High-resolution 360 panorama or video supported.</p>
                            </div>
                            
                            <div id="preview_foto" class="mt-3"></div>

                            <div id="uploadProgress" class="mt-3" style="display: none;">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-primary fw-bold small">Uploading to Cloudinary...</span>
                                    <span id="progressPercent" class="small fw-bold text-primary">0%</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                         role="progressbar" 
                                         style="width: 0%"
                                         id="progressBar"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="file_lrv" class="form-label fw-semibold">
                                LRV / Preview File (Optional)
                            </label>
                            <input type="file" class="form-control bg-light @error('file_lrv') is-invalid @enderror" id="file_lrv" name="file_lrv">
                            <div class="form-text text-muted small">Low-resolution version for faster mobile previews.</div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="urutan" class="form-label fw-semibold">Display Order</label>
                                <input type="number" class="form-control bg-light" id="urutan" name="urutan" value="{{ old('urutan', 0) }}" min="0">
                                <small class="text-muted">Lower numbers appear first.</small>
                            </div>
                            <div class="col-md-6 d-flex align-items-center mt-3 mt-md-0">
                                <div class="form-check form-switch pt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="is_active">Publish immediately</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between border-top pt-4 mt-4">
                            <a href="{{ route('footage360.index') }}" class="btn btn-light border px-4">
                                <i class="fas fa-chevron-left me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary px-5 shadow" id="submitBtn">
                                <i class="fas fa-cloud-upload-alt me-1"></i> Upload to Cloudinary
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 bg-dark text-white shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title d-flex align-items-center">
                        <i class="fas fa-cloud-meatball text-info me-2"></i> Cloudinary Storage
                    </h5>
                    <p class="text-white-50 small">Your media is optimized and served via a global CDN.</p>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2"><i class="fas fa-check-circle text-info me-2"></i> <strong>Unlimited</strong> file size upload</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-info me-2"></i> Automatic 360° projection support</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-info me-2"></i> Fast loading for global users</li>
                        <li><i class="fas fa-check-circle text-info me-2"></i> Offloads bandwidth from your server</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-lightbulb text-warning me-2"></i> Best Practices</h6>
                    <ul class="small text-muted ps-3">
                        <li class="mb-2">Use high-resolution <strong>Equirectangular</strong> images (2:1 aspect ratio).</li>
                        <li class="mb-2">Ensure your internet connection is stable before starting large uploads.</li>
                        <li>Do not close this tab until the "Success" message appears.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .transition { transition: all 0.3s ease; }
    .upload-zone:hover { border-color: #0d6efd !important; background-color: #f1f7ff !important; }
    .form-control:focus, .form-select:focus { box-shadow: none; border-color: #0d6efd; background-color: #fff !important; }
    .input-group-text { border-color: transparent; }
</style>
@endsection

@push('scripts')
<script>
document.getElementById('file_foto').addEventListener('change', function(e) {
    const preview = document.getElementById('preview_foto');
    const file = e.target.files[0];
    
    if (file) {
        const fileSize = (file.size / (1024 * 1024)).toFixed(2);
        const fileName = file.name;
        
        let infoHtml = `
            <div class="card border-0 bg-light">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-file-alt fa-2x text-primary"></i>
                    </div>
                    <div>
                        <p class="mb-0 fw-bold small text-truncate" style="max-width: 250px;">${fileName}</p>
                        <p class="mb-0 text-muted small">${fileSize} MB • Ready for Cloudinary</p>
                    </div>
                </div>
            </div>
        `;
        
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `
                    <div class="position-relative d-inline-block">
                        <img src="${e.target.result}" class="rounded-3 shadow-sm border" style="max-height: 200px; width: 100%; object-fit: cover;">
                        <div class="mt-2">${infoHtml}</div>
                    </div>
                `;
            }
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = infoHtml;
        }
    }
});

document.getElementById('uploadForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    const progressDiv = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    progressDiv.style.display = 'block';
    
    let progress = 0;
    const interval = setInterval(function() {
        progress += Math.random() * 12;
        if (progress > 95) {
            progress = 95;
            clearInterval(interval);
        }
        progressBar.style.width = progress + '%';
        progressPercent.textContent = Math.round(progress) + '%';
    }, 600);
});
</script>
@endpush