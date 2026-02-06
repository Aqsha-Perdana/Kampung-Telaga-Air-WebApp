@extends('layout.sidebar')
@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Culinary Detail</h2>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $culinary->nama }}</h5>
            <span class="badge bg-primary">{{ $culinary->id_culinary }}</span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12 mb-4">
                    <h6 class="mb-3">Gallery Photo ({{ $culinary->fotos->count() }} Photo)</h6>
                    @if($culinary->fotos->count() > 0)
                        <div id="carouselCulinary" class="carousel slide mb-3" data-bs-ride="carousel">
                            <div class="carousel-indicators">
                                @foreach($culinary->fotos as $index => $foto)
                                    <button type="button" 
                                            data-bs-target="#carouselCulinary" 
                                            data-bs-slide-to="{{ $index }}" 
                                            class="{{ $index == 0 ? 'active' : '' }}"
                                            aria-label="Slide {{ $index + 1 }}"></button>
                                @endforeach
                            </div>
                            <div class="carousel-inner">
                                @foreach($culinary->fotos as $index => $foto)
                                    <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                        <img src="{{ asset('storage/'.$foto->foto) }}" 
                                             class="d-block w-100" 
                                             alt="{{ $culinary->nama }}"
                                             style="height: 500px; object-fit: cover;">
                                        <div class="carousel-caption d-none d-md-block">
                                            <span class="badge bg-primary">Photo {{ $foto->urutan }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselCulinary" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carouselCulinary" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            @foreach($culinary->fotos as $foto)
                                <img src="{{ asset('storage/'.$foto->foto) }}" 
                                     alt="{{ $culinary->nama }}" 
                                     class="img-thumbnail" 
                                     style="width: 100px; height: 100px; object-fit: cover; cursor: pointer;"
                                     onclick="document.querySelector('[data-bs-slide-to=&quot;{{ $loop->index }}&quot;]').click()">
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            There are no photos for this Culinary yet.
                        </div>
                    @endif
                </div>

                <div class="col-md-12">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Culinary ID</th>
                            <td>: <span class="badge bg-primary">{{ $culinary->id_culinary }}</span></td>
                        </tr>
                        <tr>
                            <th>Culinary Name</th>
                            <td>: {{ $culinary->nama }}</td>
                        </tr>
                        <tr>
                            <th>Location</th>
                            <td>: {{ $culinary->lokasi }}</td>
                        </tr>
                        <tr>
                            <th>Total Package</th>
                            <td>: {{ $culinary->pakets->count() }} Package</td>
                        </tr>
                        <tr>
                            <th>Total Photo</th>
                            <td>: {{ $culinary->fotos->count() }} Photo</td>
                        </tr>
                        <tr>
                            <th>Created</th>
                            <td>: {{ $culinary->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Last Update</th>
                            <td>: {{ $culinary->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($culinary->deskripsi)
            <div class="row mt-3">
                <div class="col-md-12">
                    <h6>Description:</h6>
                    <p class="text-justify">{{ $culinary->deskripsi }}</p>
                </div>
            </div>
            @endif

            <hr class="my-4">

            <div class="row">
                <div class="col-md-12">
                    <h6 class="mb-3">Package List</h6>
                    <div class="row">
                        @foreach($culinary->pakets as $index => $paket)
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <strong>{{ $paket->nama_paket }}</strong>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <th width="120">Capacity</th>
                                            <td>: {{ $paket->kapasitas }} orang</td>
                                        </tr>
                                        <tr>
                                            <th>Price</th>
                                            <td>: <strong class="text-success">{{ format_ringgit($paket->harga) }}</strong></td>
                                        </tr>
                                        @if($paket->deskripsi_paket)
                                        <tr>
                                            <th class="align-top">Package</th>
                                            <td>: {{ $paket->deskripsi_paket }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('culinaries.edit', $culinary->id_culinary) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="{{ route('culinaries.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Cancel
                </a>
                <form action="{{ route('culinaries.destroy', $culinary->id_culinary) }}" 
                      method="POST" 
                      style="display: inline;"
                      onsubmit="return confirm('Yakin ingin menghapus kuliner ini beserta {{ $culinary->fotos->count() }} foto dan {{ $culinary->pakets->count() }} paket?')">
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