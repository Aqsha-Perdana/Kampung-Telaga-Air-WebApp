@extends('layout.sidebar')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-auto">
            <div class="bg-warning text-white rounded-3 p-3 shadow-sm">
                <i class="fas fa-edit fa-2x"></i>
            </div>
        </div>
        <div class="col">
            <h1 class="h3 mb-1 fw-bold text-gray-800">Edit 360° Footage</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="{{ route('footage360.index') }}" class="text-decoration-none">Footage Library</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Update Content</li>
                </ol>
            </nav>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="card-title mb-0 fw-bold">Update Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('footage360.update', $footage360->id_footage360) }}" method="POST" enctype="multipart/form-data" id="updateForm">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="id_destinasi" class="form-label fw-semibold">Destination <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-map-marked-alt text-muted"></i></span>
                                <select name="id_destinasi" id="id_destinasi" class="form-select border-start-0 bg-light @error('id_destinasi') is-invalid @enderror" required>
                                    <option value="">-- Select Destination --</option>
                                    @foreach($destinasis as $destinasi)
                                        <option value="{{ $destinasi->id_destinasi }}" 
                                                {{ old('id_destinasi', $footage360->id_destinasi) == $destinasi->id_destinasi ? 'selected' : '' }}>
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
                                   value="{{ old('judul', $footage360->judul) }}" 
                                   required>
                            @error('judul')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="deskripsi" class="form-label fw-semibold">Description</label>
                            <textarea class="form-control bg-light @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" 
                                      name="deskripsi" 
                                      rows="3">{{ old('deskripsi', $footage360->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        <div class="mb-4">
                            <label for="file_foto" class="form-label fw-semibold d-flex justify-content-between">
                                <span>Replace 360° Media File</span>
                                <span class="badge rounded-pill bg-info text-dark small">Cloudinary Storage</span>
                            </label>
                            
                            @if($footage360->file_foto)
                            <div class="p-3 mb-3 border rounded-3 bg-light d-flex align-items-center">
                                <img src="{{ $footage360->file_foto }}" class="rounded shadow-sm me-3" style="width: 80px; height: 50px; object-fit: cover;">
                                <div>
                                    <p class="mb-0 small fw-bold">Current Live File</p>
                                    <a href="{{ $footage360->file_foto }}" target="_blank" class="small text-decoration-none text-primary">View Full Resolution <i class="fas fa-external-link-alt ms-1" style="font-size: 0.7rem;"></i></a>
                                </div>
                            </div>
                            @endif

                            <input type="file" class="form-control @error('file_foto') is-invalid @enderror" id="file_foto" name="file_foto">
                            <div class="form-text text-muted small">Leave empty if you don't want to change the current file.</div>
                            
                            <div id="preview_foto" class="mt-3"></div>
                        </div>

                        <div class="mb-4">
                            <label for="file_lrv" class="form-label fw-semibold">Update LRV / Preview File (Optional)</label>
                            <input type="file" class="form-control bg-light @error('file_lrv') is-invalid @enderror" id="file_lrv" name="file_lrv">
                            @if($footage360->file_lrv)
                                <div class="mt-2">
                                    <span class="badge bg-soft-success text-success border border-success-subtle">
                                        <i class="fas fa-check-circle me-1"></i> LRV file is currently active on Cloudinary
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="urutan" class="form-label fw-semibold">Display Order</label>
                                <input type="number" class="form-control bg-light" id="urutan" name="urutan" value="{{ old('urutan', $footage360->urutan) }}" min="0">
                            </div>
                            <div class="col-md-6 d-flex align-items-center mt-3 mt-md-0">
                                <div class="form-check form-switch pt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $footage360->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="is_active">Visibility (Active)</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between border-top pt-4 mt-4">
                            <a href="{{ route('footage360.index') }}" class="btn btn-light border px-4">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>
                            <button type="submit" class="btn btn-warning px-5 shadow-sm text-dark fw-bold">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4 text-center">
                    <h6 class="fw-bold mb-3 text-uppercase small text-muted">Quick Preview</h6>
                    <div class="bg-light rounded-3 p-4 mb-3 border border-dashed">
                        <i class="fas fa-vr-cardboard fa-3x text-muted mb-3"></i>
                        <p class="small text-muted">Check how this footage looks in the 360 viewer.</p>
                        <a href="{{ route('view360.show', $footage360->id_footage360) }}" 
                           class="btn btn-info w-100 shadow-sm" 
                           target="_blank">
                            <i class="fas fa-eye me-1"></i> Launch 360° Player
                        </a>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-cloud text-success me-2"></i> Cloudinary Metadata</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0 small">
                            <tr>
                                <td class="text-muted">Public ID:</td>
                                <td class="text-end fw-bold"><code>{{ Str::limit($footage360->cloudinary_public_id, 15) }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Uploaded:</td>
                                <td class="text-end fw-bold text-dark">{{ $footage360->created_at->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Time:</td>
                                <td class="text-end fw-bold text-dark">{{ $footage360->created_at->format('H:i A') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Last Update:</td>
                                <td class="text-end fw-bold text-dark">{{ $footage360->updated_at->diffForHumans() }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); }
    .form-control:focus, .form-select:focus { box-shadow: none; border-color: #ffc107; background-color: #fff !important; }
    .btn-warning:hover { background-color: #e0a800; border-color: #d39e00; }
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
            <div class="alert alert-warning border-0 shadow-sm py-2">
                <div class="d-flex align-items-center">
                    <i class="fas fa-sync-alt fa-spin me-2 text-dark"></i>
                    <span class="small fw-bold text-dark">NEW FILE SELECTED:</span>
                </div>
                <div class="mt-1 ps-4 small text-dark">
                    ${fileName} (${fileSize} MB)<br>
                    <em>Note: The existing file will be overwritten on Cloudinary.</em>
                </div>
            </div>
        `;
        
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = infoHtml + `<img src="${e.target.result}" class="rounded shadow-sm border mt-2" style="max-height: 180px; width: 100%; object-fit: cover;">`;
            }
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = infoHtml;
        }
    }
});
</script>
@endpush