@extends('layout.sidebar')
@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Edit Culinary</h2>
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
            <h5 class="mb-0">Edit Culinary Form - {{ $culinary->id_culinary }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('culinaries.update', $culinary->id_culinary) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Culinary Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nama') is-invalid @enderror" 
                                   id="nama" 
                                   name="nama" 
                                   value="{{ old('nama', $culinary->nama) }}" 
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
                                   value="{{ old('lokasi', $culinary->lokasi) }}" 
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
                              rows="4">{{ old('deskripsi', $culinary->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-4">
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Culinary Package <span class="text-danger">*</span></h6>
                        <button type="button" class="btn btn-sm btn-success" onclick="addPaket()">
                            <i class="bi bi-plus"></i> Add Package
                        </button>
                    </div>
                    
                    <div id="paket-container">
                        @foreach($culinary->pakets as $index => $paket)
                        <div class="paket-item card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Package {{ $index + 1 }}</h6>
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="hapus_paket[]" 
                                               value="{{ $paket->id }}" 
                                               id="hapusPaket{{ $paket->id }}">
                                        <label class="form-check-label text-danger" for="hapusPaket{{ $paket->id }}">
                                            <i class="bi bi-trash"></i> Delete Package
                                        </label>
                                    </div>
                                </div>
                                <input type="hidden" name="paket_id[]" value="{{ $paket->id }}">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Package Name <span class="text-danger">*</span></label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   name="paket_nama[]" 
                                                   value="{{ $paket->nama_paket }}"
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">Capacity (People) <span class="text-danger">*</span></label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   name="paket_kapasitas[]" 
                                                   value="{{ $paket->kapasitas }}"
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
                                                   value="{{ $paket->harga }}"
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
                                                      rows="2">{{ $paket->deskripsi_paket }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <hr class="my-4">

                <div class="mb-3">
                    <label class="form-label">Current Photo</label>
                    @if($culinary->fotos->count() > 0)
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            @foreach($culinary->fotos as $foto)
                                <div class="position-relative">
                                    <img src="{{ asset('storage/'.$foto->foto) }}" 
                                         alt="{{ $culinary->nama }}" 
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
                        <p class="text-muted">No Photos Yet</p>
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
                    <small class="text-muted">Format: JPG, JPEG, PNG, GIF. Max 2MB per Photo</small>
                    @error('fotos.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    
                    <div id="preview-container" class="mt-3 d-flex flex-wrap gap-2"></div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update
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
let paketCount = {{ $culinary->pakets->count() }};

function addPaket() {
    paketCount++;
    const container = document.getElementById('paket-container');
    const newPaket = `
        <div class="paket-item card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Paket Baru ${paketCount}</h6>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removePaket(this)">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </div>
                <input type="hidden" name="paket_id[]" value="">
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