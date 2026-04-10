@extends('layout.sidebar')
@section('content')

<div class="row mb-4">
        <div class="col-md-12 px-0">
            <div class="position-relative" style="height: 200px; border-radius: 10px; overflow: hidden;">
                <!-- Background Image -->
                <img src="{{ asset('assets/images/backgrounds/bg-destination.jpg') }}" 
                     alt="Background" 
                     class="w-100 h-100" 
                     style="object-fit: cover; filter: brightness(0.6);">
                
                <!-- Overlay Text -->
                <div class="position-absolute top-50 start-50 translate-middle text-center text-white" style="z-index: 2;">
                    <h1 class="display-4 fw-bold mb-2" style="color: white !important; text-shadow: 2px 2px 8px rgba(0,0,0,0.8);">Destination Data</h1>
                    <p class="lead mb-0">Manage All Your Destination Data!</p>
                </div>
            </div>
        </div>
    </div>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Destination Detail</h2>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $destinasi->nama }}</h5>
            <span class="badge bg-primary">{{ $destinasi->id_destinasi }}</span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12 mb-4">
                    <h6 class="mb-3">Gallery ({{ $destinasi->fotos->count() }} foto)</h6>
                    @if($destinasi->fotos->count() > 0)
                        <div id="carouselDestinasi" class="carousel slide mb-3" data-bs-ride="carousel">
                            <div class="carousel-indicators">
                                @foreach($destinasi->fotos as $index => $foto)
                                    <button type="button" 
                                            data-bs-target="#carouselDestinasi" 
                                            data-bs-slide-to="{{ $index }}" 
                                            class="{{ $index == 0 ? 'active' : '' }}"
                                            aria-label="Slide {{ $index + 1 }}"></button>
                                @endforeach
                            </div>
                            <div class="carousel-inner">
                                @foreach($destinasi->fotos as $index => $foto)
                                    <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                        <img src="{{ asset('storage/'.$foto->foto) }}" 
                                             class="d-block w-100" 
                                             alt="{{ $destinasi->nama }}"
                                             style="height: 500px; object-fit: cover;">
                                        <div class="carousel-caption d-none d-md-block">
                                            <span class="badge bg-primary">Photo {{ $foto->urutan }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselDestinasi" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carouselDestinasi" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            @foreach($destinasi->fotos as $foto)
                                <img src="{{ asset('storage/'.$foto->foto) }}" 
                                     alt="{{ $destinasi->nama }}" 
                                     class="img-thumbnail" 
                                     style="width: 100px; height: 100px; object-fit: cover; cursor: pointer;"
                                     onclick="document.querySelector('[data-bs-slide-to=&quot;{{ $loop->index }}&quot;]').click()">
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            No photos yet for this destination
                        </div>
                    @endif
                </div>

                <div class="col-md-12">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Destination ID</th>
                            <td>: <span class="badge bg-primary">{{ $destinasi->id_destinasi }}</span></td>
                        </tr>
                        <tr>
                            <th>Destination Name</th>
                            <td>: {{ $destinasi->nama }}</td>
                        </tr>
                        <tr>
                            <th>Location</th>
                            <td>: {{ $destinasi->lokasi }}</td>
                        </tr>
                        <tr>
                            <th>Number of Photos</th>
                            <td>: {{ $destinasi->fotos->count() }} foto</td>
                        </tr>
                        <tr>
                            <th>Created</th>
                            <td>: {{ $destinasi->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Last Update</th>
                            <td>: {{ $destinasi->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-12">
                    <h6>Description:</h6>
                    <p class="text-justify">{{ $destinasi->deskripsi }}</p>
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('destinasis.edit', $destinasi->id_destinasi) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="{{ route('destinasis.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Cancel
                </a>
                <form action="{{ route('destinasis.destroy', $destinasi->id_destinasi) }}" 
                      method="POST" 
                      style="display: inline;"
                      onsubmit="event.preventDefault(); adminDeleteSwal({ actionUrl: '{{ route('destinasis.destroy', $destinasi->id_destinasi) }}', itemLabel: @js($destinasi->nama), title: 'Delete Destination?', html: 'This will permanently delete <strong>' + @js($destinasi->nama) + '</strong> and its ' + @js($destinasi->fotos->count()) + ' photo(s). This action cannot be undone.' });">
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
