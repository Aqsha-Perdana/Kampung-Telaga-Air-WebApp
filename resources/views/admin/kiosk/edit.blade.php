@extends('layout.sidebar')
@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Edit Kiosk</h2>
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
            <h5 class="mb-0">Edit Kiosk Form - {{ $kiosk->id_kiosk }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('kiosks.update', $kiosk->id_kiosk) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Kiosk Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nama') is-invalid @enderror" 
                                   id="nama" 
                                   name="nama" 
                                   value="{{ old('nama', $kiosk->nama) }}" 
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
                                   value="{{ old('kapasitas', $kiosk->kapasitas) }}" 
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
                                   value="{{ old('harga_per_paket', $kiosk->harga_per_paket) }}" 
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
                              rows="4">{{ old('deskripsi', $kiosk->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Current Photos</label>
                    @if($kiosk->fotos->count() > 0)
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            @foreach($kiosk->fotos as $foto)
                                <div class="position-relative">
                                    <img src="{{ asset('storage/'.$foto->foto) }}" 
                                         alt="{{ $kiosk->nama }}" 
                                         class="img-thumbnail" 
                                         style="width: 150px; height: 150px; object-fit: cover;">
                                    <span class="badge bg-primary position-absolute top-0 start-0 m-1">{{ $foto->urutan }}</span>
                                    <div class="form-check position-absolute bottom-0 start-0 m-1 bg-white rounded px-2">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="hapus_foto[]" 
                                               value="{{ $foto->id }}" 
                                               id="hapus{{ $foto->id }}">
                                        <label class="form-check-label small" for="hapus{{ $foto->id }}">
                                            Delete
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted">Check the photos you want to delete</small>
                    @else
                        <p class="text-muted">No photos yet</p>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="fotos" class="form-label">Add New Photos</label>
                    <input type="file" 
                           class="form-control @error('fotos.*') is-invalid @enderror" 
                           id="fotos" 
                           name="fotos[]" 
                           accept="image/*"
                           multiple
                           onchange="previewImages()">
                    <small class="text-muted">Format: JPG, JPEG, PNG, GIF. Max 2MB per photo. You can select multiple photos at once</small>
                    @error('fotos.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    
                    <div id="preview-container" class="mt-3 d-flex flex-wrap gap-2"></div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update
                    </button>
                    <a href="{{ route('kiosks.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back
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
                    <span class="badge bg-success position-absolute top-0 start-0 m-1">New ${index + 1}</span>
                `;
                preview.appendChild(div);
            }
            
            reader.readAsDataURL(file);
        });
    }
}
</script>
@endsection