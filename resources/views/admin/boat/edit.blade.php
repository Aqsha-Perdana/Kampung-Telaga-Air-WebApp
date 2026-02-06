@extends('layout.sidebar')
@section('content')

<div class="row mb-4">
    <div class="col-md-12">
        <h2>Edit Boat</h2>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('boats.update', $boat) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="alert alert-info">
                <strong>ID Boat:</strong> {{ $boat->id_boat }}
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Boat *</label>
                        <input type="text" 
                               class="form-control @error('nama') is-invalid @enderror" 
                               id="nama" 
                               name="nama" 
                               value="{{ old('nama', $boat->nama) }}"
                               placeholder="Masukkan nama boat"
                               required>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="kapasitas" class="form-label">Kapasitas (Orang) *</label>
                        <input type="number" 
                               class="form-control @error('kapasitas') is-invalid @enderror" 
                               id="kapasitas" 
                               name="kapasitas" 
                               value="{{ old('kapasitas', $boat->kapasitas) }}"
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
                <label for="harga_sewa" class="form-label">Harga Sewa (RM) *</label>
                <input type="number" 
                       class="form-control @error('harga_sewa') is-invalid @enderror" 
                       id="harga_sewa" 
                       name="harga_sewa" 
                       value="{{ old('harga_sewa', $boat->harga_sewa) }}"
                       placeholder="Masukkan harga sewa"
                       min="0"
                       step="0.01"
                       required>
                @error('harga_sewa')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="foto" class="form-label">Foto</label>
                @if($boat->foto)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $boat->foto) }}" 
                             alt="{{ $boat->nama }}" 
                             class="img-thumbnail" 
                             style="max-width: 200px;">
                        <p class="text-muted small mt-1">Foto saat ini</p>
                    </div>
                @endif
                <input type="file" 
                       class="form-control @error('foto') is-invalid @enderror" 
                       id="foto" 
                       name="foto"
                       accept="image/*">
                <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 2MB. Kosongkan jika tidak ingin mengganti.</small>
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
                           {{ old('is_active', $boat->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Aktif
                    </label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy"></i> Update
                </button>
                <a href="{{ route('boats.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>

@endsection