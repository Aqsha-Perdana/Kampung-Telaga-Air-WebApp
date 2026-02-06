@extends('layout.sidebar')
@section('content')

<div class="row mb-4">
    <div class="col-md-12">
        <h2>Edit Homestay</h2>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('homestays.update', $homestay) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="alert alert-info">
                <strong>ID Homestay:</strong> {{ $homestay->id_homestay }}
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Homestay Name</label>
                        <input type="text" 
                               class="form-control @error('nama') is-invalid @enderror" 
                               id="nama" 
                               name="nama" 
                               value="{{ old('nama', $homestay->nama) }}"
                               placeholder="Input Homestay Name"
                               required>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="kapasitas" class="form-label">Capacity (People) *</label>
                        <input type="number" 
                               class="form-control @error('kapasitas') is-invalid @enderror" 
                               id="kapasitas" 
                               name="kapasitas" 
                               value="{{ old('kapasitas', $homestay->kapasitas) }}"
                               placeholder="Masukkan kapasitas"
                               min="1"
                               required>
                        @error('kapasitas')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="harga_per_malam" class="form-label">Price per Night (RM) *</label>
                <input type="number" 
                       class="form-control @error('harga_per_malam') is-invalid @enderror" 
                       id="harga_per_malam" 
                       name="harga_per_malam" 
                       value="{{ old('harga_per_malam', $homestay->harga_per_malam) }}"
                       placeholder="Masukkan harga per malam"
                       min="0"
                       step="0.01"
                       required>
                @error('harga_per_malam')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="foto" class="form-label">Photo</label>
                @if($homestay->foto)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $homestay->foto) }}" 
                             alt="{{ $homestay->nama }}" 
                             class="img-thumbnail" 
                             style="max-width: 200px;">
                        <p class="text-muted small mt-1">Recent Photo</p>
                    </div>
                @endif
                <input type="file" 
                       class="form-control @error('foto') is-invalid @enderror" 
                       id="foto" 
                       name="foto"
                       accept="image/*">
                <small class="text-muted">Format: JPG, PNG, GIF. Max 2MB.</small>
                @error('foto')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" 
                           type="checkbox" 
                           id="is_active" 
                           name="is_active" 
                           value="1"
                           {{ old('is_active', $homestay->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Active
                    </label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy"></i> Update
                </button>
                <a href="{{ route('homestays.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection