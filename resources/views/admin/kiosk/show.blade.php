@extends('layout.sidebar')
@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Detail Kiosk</h2>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $kiosk->nama }}</h5>
            <span class="badge bg-primary">{{ $kiosk->id_kiosk }}</span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12 mb-4">
                    <h6 class="mb-3">Gallery Photo ({{ $kiosk->fotos->count() }} Photo)</h6>
                    @if($kiosk->fotos->count() > 0)
                        <div id="carouselKiosk" class="carousel slide mb-3" data-bs-ride="carousel">
                            <div class="carousel-indicators">
                                @foreach($kiosk->fotos as $index => $foto)
                                    <button type="button" 
                                            data-bs-target="#carouselKiosk" 
                                            data-bs-slide-to="{{ $index }}" 
                                            class="{{ $index == 0 ? 'active' : '' }}"
                                            aria-label="Slide {{ $index + 1 }}"></button>
                                @endforeach
                            </div>
                            <div class="carousel-inner">
                                @foreach($kiosk->fotos as $index => $foto)
                                    <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                        <img src="{{ asset('storage/'.$foto->foto) }}" 
                                             class="d-block w-100" 
                                             alt="{{ $kiosk->nama }}"
                                             style="height: 500px; object-fit: cover;">
                                        <div class="carousel-caption d-none d-md-block">
                                            <span class="badge bg-primary">Photo {{ $foto->urutan }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselKiosk" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carouselKiosk" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            @foreach($kiosk->fotos as $foto)
                                <img src="{{ asset('storage/'.$foto->foto) }}" 
                                     alt="{{ $kiosk->nama }}" 
                                     class="img-thumbnail" 
                                     style="width: 100px; height: 100px; object-fit: cover; cursor: pointer;"
                                     onclick="document.querySelector('[data-bs-slide-to=&quot;{{ $loop->index }}&quot;]').click()">
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            There are no photos for this kiosk yet.
                        </div>
                    @endif
                </div>

                <div class="col-md-12">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Kiosk ID</th>
                            <td>: <span class="badge bg-primary">{{ $kiosk->id_kiosk }}</span></td>
                        </tr>
                        <tr>
                            <th>Kiosk Name</th>
                            <td>: {{ $kiosk->nama }}</td>
                        </tr>
                        <tr>
                            <th>Capacity</th>
                            <td>: {{ $kiosk->kapasitas }} orang</td>
                        </tr>
                        <tr>
                            <th>Price per Package</th>
                            <td>:{{ format_ringgit($kiosk->harga_per_paket) }}</td>
                        </tr>
                        <tr>
                            <th>Total Photo</th>
                            <td>: {{ $kiosk->fotos->count() }} foto</td>
                        </tr>
                        <tr>
                            <th>Created</th>
                            <td>: {{ $kiosk->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Last Update</th>
                            <td>: {{ $kiosk->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($kiosk->deskripsi)
            <div class="row mt-3">
                <div class="col-md-12">
                    <h6>Description:</h6>
                    <p class="text-justify">{{ $kiosk->deskripsi }}</p>
                </div>
            </div>
            @endif

            <div class="mt-4">
                <a href="{{ route('kiosks.edit', $kiosk->id_kiosk) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="{{ route('kiosks.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Cancel
                </a>
                <form action="{{ route('kiosks.destroy', $kiosk->id_kiosk) }}" 
                      method="POST" 
                      style="display: inline;"
                      onsubmit="return confirm('Yakin ingin menghapus kiosk ini beserta {{ $kiosk->fotos->count() }} foto?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection