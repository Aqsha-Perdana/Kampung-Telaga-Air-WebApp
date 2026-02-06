@extends('layout.sidebar')
@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Destination Edit</h2>
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
            <h5 class="mb-0">Destination Edit Form - {{ $destinasi->id_destinasi }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('destinasis.update', $destinasi->id_destinasi) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="nama" class="form-label">Destination Name <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('nama') is-invalid @enderror" 
                           id="nama" 
                           name="nama" 
                           value="{{ old('nama', $destinasi->nama) }}" 
                           required>
                    @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="lokasi" class="form-label">Location <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('lokasi') is-invalid @enderror" 
                           id="lokasi" 
                           name="lokasi" 
                           value="{{ old('lokasi', $destinasi->lokasi) }}" 
                           required>
                    @error('lokasi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                              id="deskripsi" 
                              name="deskripsi" 
                              rows="4" 
                              required>{{ old('deskripsi', $destinasi->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Photo Recently</label>
                    @if($destinasi->fotos->count() > 0)
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            @foreach($destinasi->fotos as $foto)
                                <div class="position-relative">
                                    <img src="{{ asset('storage/'.$foto->foto) }}" 
                                         alt="{{ $destinasi->nama }}" 
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
                        <p class="text-muted">No photo available yet</p>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="fotos" class="form-label">Add New Photo</label>
                    <input type="file" 
                           class="form-control @error('fotos.*') is-invalid @enderror" 
                           id="fotos" 
                           name="fotos[]" 
                           accept="image/*"
                           multiple
                           onchange="previewImages()">
                    <small class="text-muted">Format: JPG, JPEG, PNG, GIF. Max 2MB per foto</small>
                    @error('fotos.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    
                    <div id="preview-container" class="mt-3 d-flex flex-wrap gap-2"></div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update
                    </button>
                    <a href="{{ route('destinasis.index') }}" class="btn btn-secondary">
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
                    <span class="badge bg-success position-absolute top-0 start-0 m-1">Baru ${index + 1}</span>
                `;
                preview.appendChild(div);
            }
            
            reader.readAsDataURL(file);
        });
    }
}
</script>
@endsection