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
                    <h1 class="display-4 fw-bold mb-2" style="color: white !important; text-shadow: 2px 2px 8px rgba(0,0,0,0.8);">Homestay Data</h1>
                    <p class="lead mb-0">Manage All Your Homestay Data!</p>
                </div>
            </div>
        </div>
    </div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Detail Homestay</h2>
            <div>
                <a href="{{ route('homestays.edit', $homestay) }}" class="btn btn-warning text-white">
                    <i class="ti ti-edit"></i> Edit
                </a>
                <a href="{{ route('homestays.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left"></i> Cancel
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                @if($homestay->foto)
                    <img src="{{ asset('storage/' . $homestay->foto) }}" 
                         alt="{{ $homestay->nama }}" 
                         class="img-fluid rounded mb-3"
                         style="max-height: 300px; object-fit: cover; width: 100%;">
                @else
                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded mb-3" 
                         style="height: 300px;">
                        <i class="ti ti-photo" style="font-size: 5rem;"></i>
                    </div>
                @endif
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body text-center">
                <h4 class="text-primary mb-3">{{ $homestay->formatted_harga }}</h4>
                <p class="text-muted mb-0">Per Night</p>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Homestay Information</h4>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <th width="200"><i class="ti ti-id me-2"></i>Homestay ID</th>
                            <td>: <span class="badge bg-primary">{{ $homestay->id_homestay }}</span></td>
                        </tr>
                        <tr>
                            <th><i class="ti ti-home me-2"></i>Homestay Name</th>
                            <td>: <strong>{{ $homestay->nama }}</strong></td>
                        </tr>
                        <tr>
                            <th><i class="ti ti-users me-2"></i>Capacity</th>
                            <td>: {{ $homestay->kapasitas }} People</td>
                        </tr>
                        <tr>
                            <th><i class="ti ti-currency-dollar me-2"></i>Price Per Night</th>
                            <td>: <strong class="text-success">{{ $homestay->formatted_harga }}</strong></td>
                        </tr>
                        <tr>
                            <th><i class="ti ti-info-circle me-2"></i>Status</th>
                            <td>: 
                                @if($homestay->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Unactive</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th><i class="ti ti-calendar me-2"></i>Created</th>
                            <td>: {{ $homestay->created_at->format('d M Y, H:i') }} WIB</td>
                        </tr>
                        <tr>
                            <th><i class="ti ti-clock me-2"></i>Last Update</th>
                            <td>: {{ $homestay->updated_at->format('d M Y, H:i') }} WIB</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="ti ti-chart-bar me-2"></i>Statistic</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="p-3">
                            <i class="ti ti-users fs-1 text-primary"></i>
                            <h3 class="mt-2">{{ $homestay->kapasitas }}</h3>
                            <p class="text-muted mb-0">Capacity</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3">
                            <i class="ti ti-moon fs-1 text-warning"></i>
                            <h3 class="mt-2">{{ $homestay->formatted_harga }}</h3>
                            <p class="text-muted mb-0">Per Night</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3">
                            @if($homestay->is_active)
                                <i class="ti ti-circle-check fs-1 text-success"></i>
                                <h3 class="mt-2 text-success">Active</h3>
                            @else
                                <i class="ti ti-circle-x fs-1 text-secondary"></i>
                                <h3 class="mt-2 text-secondary">Unactive</h3>
                            @endif
                            <p class="text-muted mb-0">Status</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection