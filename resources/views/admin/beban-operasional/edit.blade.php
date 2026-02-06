@extends('layout.sidebar')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Beban Operasional</h3>
                </div>

                <form action="{{ route('beban-operasional.update', $bebanOperasional) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label>Kode Transaksi</label>
                            <input type="text" class="form-control" value="{{ $bebanOperasional->kode_transaksi }}" readonly>
                        </div>

                        <div class="form-group">
                            <label for="tanggal">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" 
                                   name="tanggal" 
                                   id="tanggal" 
                                   class="form-control @error('tanggal') is-invalid @enderror" 
                                   value="{{ old('tanggal', $bebanOperasional->tanggal->format('Y-m-d')) }}" 
                                   required>
                            @error('tanggal')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="kategori">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori" 
                                    id="kategori" 
                                    class="form-control @error('kategori') is-invalid @enderror" 
                                    required>
                                <option value="">Pilih Kategori</option>
                                @foreach($kategoriList as $kat)
                                    <option value="{{ $kat }}" {{ old('kategori', $bebanOperasional->kategori) == $kat ? 'selected' : '' }}>
                                        {{ $kat }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kategori')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="deskripsi" 
                                   id="deskripsi" 
                                   class="form-control @error('deskripsi') is-invalid @enderror" 
                                   value="{{ old('deskripsi', $bebanOperasional->deskripsi) }}"
                                   required>
                            @error('deskripsi')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="jumlah">Jumlah (Rp) <span class="text-danger">*</span></label>
                            <input type="number" 
                                   name="jumlah" 
                                   id="jumlah" 
                                   class="form-control @error('jumlah') is-invalid @enderror" 
                                   value="{{ old('jumlah', $bebanOperasional->jumlah) }}"
                                   step="0.01"
                                   min="0"
                                   required>
                            @error('jumlah')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="metode_pembayaran">Metode Pembayaran <span class="text-danger">*</span></label>
                            <select name="metode_pembayaran" 
                                    id="metode_pembayaran" 
                                    class="form-control @error('metode_pembayaran') is-invalid @enderror" 
                                    required>
                                <option value="">Pilih Metode</option>
                                @foreach($metodePembayaran as $metode)
                                    <option value="{{ $metode }}" {{ old('metode_pembayaran', $bebanOperasional->metode_pembayaran) == $metode ? 'selected' : '' }}>
                                        {{ $metode }}
                                    </option>
                                @endforeach
                            </select>
                            @error('metode_pembayaran')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="nomor_referensi">Nomor Referensi</label>
                            <input type="text" 
                                   name="nomor_referensi" 
                                   id="nomor_referensi" 
                                   class="form-control @error('nomor_referensi') is-invalid @enderror" 
                                   value="{{ old('nomor_referensi', $bebanOperasional->nomor_referensi) }}">
                            @error('nomor_referensi')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <textarea name="keterangan" 
                                      id="keterangan" 
                                      class="form-control @error('keterangan') is-invalid @enderror" 
                                      rows="3">{{ old('keterangan', $bebanOperasional->keterangan) }}</textarea>
                            @error('keterangan')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="bukti_pembayaran">Bukti Pembayaran</label>
                            @if($bebanOperasional->bukti_pembayaran)
                                <div class="mb-2">
                                    <a href="{{ Storage::url($bebanOperasional->bukti_pembayaran) }}" 
                                       target="_blank" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-file"></i> Lihat Bukti Saat Ini
                                    </a>
                                </div>
                            @endif
                            <input type="file" 
                                   name="bukti_pembayaran" 
                                   id="bukti_pembayaran" 
                                   class="form-control-file @error('bukti_pembayaran') is-invalid @enderror"
                                   accept=".jpg,.jpeg,.png,.pdf">
                            <small class="form-text text-muted">Format: JPG, PNG, PDF (Max: 2MB). Kosongkan jika tidak ingin mengubah.</small>
                            @error('bukti_pembayaran')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update
                        </button>
                        <a href="{{ route('beban-operasional.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection