@extends('layout.sidebar')
@section('content')

<div class="row mb-4">
        <div class="col-md-12 px-0">
            <div class="position-relative" style="height: 200px; border-radius: 10px; overflow: hidden;">
                <!-- Background Image -->
                <img src="{{ asset('assets/images/backgrounds/bg-homestay.png') }}" 
                     alt="Background" 
                     class="w-100 h-100" 
                     style="object-fit: cover; filter: brightness(0.6);">
                
                <!-- Overlay Text -->
                <div class="position-absolute top-50 start-50 translate-middle text-center text-white" style="z-index: 2;">
                    <h1 class="display-4 fw-bold mb-2" style="color: white !important; text-shadow: 2px 2px 8px rgba(0,0,0,0.8);">Add Homestay Data</h1>
                </div>
            </div>
        </div>
    </div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('homestays.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Homestay Name</label>
                        <input type="text" 
                               class="form-control @error('nama') is-invalid @enderror" 
                               id="nama" 
                               name="nama" 
                               value="{{ old('nama') }}"
                               placeholder="Enter the name of the homestay"
                               required>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="kapasitas" class="form-label">Capacity (people) *</label>
                        <input type="number" 
                               class="form-control @error('kapasitas') is-invalid @enderror" 
                               id="kapasitas" 
                               name="kapasitas" 
                               value="{{ old('kapasitas') }}"
                               placeholder="Enter the capacity"
                               min="1"
                               required>
                        @error('kapasitas')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="harga_per_malam" class="form-label">Price/Night</label>
                <input type="number" 
                       class="form-control @error('harga_per_malam') is-invalid @enderror" 
                       id="harga_per_malam" 
                       name="harga_per_malam" 
                       value="{{ old('harga_per_malam') }}"
                       placeholder="Enter the price per night"
                       min="0"
                       step="0.01"
                       required>
                @error('harga_per_malam')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="foto" class="form-label">Photo</label>
                <input type="file" 
                       class="form-control @error('foto') is-invalid @enderror" 
                       id="foto" 
                       name="foto"
                       accept="image/*">
                <small class="text-muted">Format: JPG, PNG, GIF. Max 2MB</small>
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
                           {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Active
                    </label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy"></i> Save
                </button>
                <a href="{{ route('homestays.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection