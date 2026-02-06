@extends('layout.sidebar')
@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12 px-0">
            <div class="position-relative" style="height: 200px; border-radius: 10px; overflow: hidden;">
                <!-- Background Image -->
                <img src="{{ asset('assets/images/backgrounds/bg-culinary.jpg') }}" 
                     alt="Background" 
                     class="w-100 h-100" 
                     style="object-fit: cover; filter: brightness(0.6);">
                
                <!-- Overlay Text -->
                <div class="position-absolute top-50 start-50 translate-middle text-center text-white" style="z-index: 2;">
                    <h1 class="display-4 fw-bold mb-2" style="color: white !important; text-shadow: 2px 2px 8px rgba(0,0,0,0.8);">Add Culinary Data</h1>
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
            <h5 class="mb-0">Add Culinary Form</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('culinaries.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Culinary Name <span class="text-danger">*</span></label>
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

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="lokasi" class="form-label">Location <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('lokasi') is-invalid @enderror" 
                                   id="lokasi" 
                                   name="lokasi" 
                                   value="{{ old('lokasi') }}" 
                                   required>
                            @error('lokasi')
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

                <hr class="my-4">
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Culinary Package <span class="text-danger">*</span></h6>
                        <button type="button" class="btn btn-sm btn-success" onclick="addPaket()">
                            <i class="bi bi-plus"></i> Add Culinary Package
                        </button>
                    </div>
                    
                    <div id="paket-container">
                        <div class="paket-item card mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Package Name <span class="text-danger">*</span></label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   name="paket_nama[]" 
                                                   placeholder="Example: Paket Telaga"
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">Capacity (People) <span class="text-danger">*</span></label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   name="paket_kapasitas[]" 
                                                   min="1"
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">Price (RM) <span class="text-danger">*</span></label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   name="paket_harga[]" 
                                                   min="0"
                                                   step="0.01"
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Package Description</label>
                                            <textarea class="form-control" 
                                                      name="paket_deskripsi[]" 
                                                      rows="2"
                                                      placeholder="Example: Nasi, Ayam Goreng, Sayur, Kerupuk, Es Teh"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="mb-3">
                    <label for="fotos" class="form-label">Culinary Photo (Can be more than 1)</label>
                    <input type="file" 
                           class="form-control @error('fotos.*') is-invalid @enderror" 
                           id="fotos" 
                           name="fotos[]" 
                           accept="image/*"
                           multiple
                           onchange="previewImages()">
                    <small class="text-muted">Format: JPG, JPEG, PNG, GIF. Max 2MB per Photo</small>
                    @error('fotos.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    
                    <div id="preview-container" class="mt-3 d-flex flex-wrap gap-2"></div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save
                    </button>
                    <a href="{{ route('culinaries.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let paketCount = 1;

function addPaket() {
    paketCount++;
    const container = document.getElementById('paket-container');
    const newPaket = `
        <div class="paket-item card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Paket ${paketCount}</h6>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removePaket(this)">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nama Paket <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="paket_nama[]" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Kapasitas (orang) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="paket_kapasitas[]" min="1" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="paket_harga[]" min="0" step="0.01" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Deskripsi Paket</label>
                            <textarea class="form-control" name="paket_deskripsi[]" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', newPaket);
}

function removePaket(btn) {
    btn.closest('.paket-item').remove();
}

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