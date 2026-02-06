@extends('layout.sidebar')
@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12 px-0">
            <div class="position-relative" style="height: 200px; border-radius: 10px; overflow: hidden;">
                <!-- Background Image -->
                <img src="{{ asset('assets/images/backgrounds/bg-kiosk.jpg') }}" 
                     alt="Background" 
                     class="w-100 h-100" 
                     style="object-fit: cover; filter: brightness(0.6);">
                
                <!-- Overlay Text -->
                <div class="position-absolute top-50 start-50 translate-middle text-center text-white" style="z-index: 2;">
                    <h1 class="display-4 fw-bold mb-2" style="color: white !important; text-shadow: 2px 2px 8px rgba(0,0,0,0.8);">Add Kiosk Data</h1>
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

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Add Kiosk Form</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('kiosks.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Kiosk Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nama') is-invalid @enderror" 
                                   id="nama" 
                                   name="nama" 
                                   value="{{ old('nama') }}" 
                                   required>
                            @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="kapasitas" class="form-label">Capacity (people) <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('kapasitas') is-invalid @enderror" 
                                   id="kapasitas" 
                                   name="kapasitas" 
                                   value="{{ old('kapasitas') }}" 
                                   min="1" 
                                   required>
                            @error('kapasitas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="harga_per_paket" class="form-label">Price/Package (RM) <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('harga_per_paket') is-invalid @enderror" 
                                   id="harga_per_paket" 
                                   name="harga_per_paket" 
                                   value="{{ old('harga_per_paket') }}" 
                                   min="0" 
                                   step="0.01" 
                                   required>
                            @error('harga_per_paket')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Description</label>
                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                              id="deskripsi" 
                              name="deskripsi" 
                              rows="4">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="fotos" class="form-label">Kiosk Photo (Can be more than 1)</label>
                    <input type="file" 
                           class="form-control @error('fotos.*') is-invalid @enderror" 
                           id="fotos" 
                           name="fotos[]" 
                           accept="image/*"
                           multiple
                           onchange="previewImages()">
                    <small class="text-muted">Format: JPG, JPEG, PNG, GIF. Max 2MB per photo.</small>
                    @error('fotos.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    
                    <div id="preview-container" class="mt-3 d-flex flex-wrap gap-2"></div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save
                    </button>
                    <a href="{{ route('kiosks.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewImages() {
    const preview = document.getElementById('preview-container');
    const files = document.getElementById('fotos').files;
    
    preview.innerHTML = '';
    
    if (files) {
        [...files].forEach((file, index) => {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'position-relative';
                div.innerHTML = `
                    <img src="${e.target.result}" class="img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;">
                    <span class="badge bg-primary position-absolute top-0 start-0 m-1">${index + 1}</span>
                `;
                preview.appendChild(div);
            }
            
            reader.readAsDataURL(file);
        });
    }
}
</script>
@endsection