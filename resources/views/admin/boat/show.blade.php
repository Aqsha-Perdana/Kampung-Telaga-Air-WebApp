@extends('layout.sidebar')
@section('content')

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Detail Boat</h2>
            <div>
                <a href="{{ route('boats.edit', $boat) }}" class="btn btn-warning text-white">
                    <i class="ti ti-edit"></i> Edit
                </a>
                <a href="{{ route('boats.index') }}" class="btn btn-secondary">
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
                @if($boat->foto)
                    <img src="{{ asset('storage/' . $boat->foto) }}" 
                         alt="{{ $boat->nama }}" 
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
                <h4 class="text-primary mb-3">{{ $boat->formatted_harga }}</h4>
                <p class="text-muted mb-0">Rental Price</p>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Boat Information</h4>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <th width="200"><i class="ti ti-id me-2"></i>Boat ID</th>
                            <td>: <span class="badge bg-primary">{{ $boat->id_boat }}</span></td>
                        </tr>
                        <tr>
                            <th><i class="ti ti-anchor me-2"></i>Boat Name</th>
                            <td>: <strong>{{ $boat->nama }}</strong></td>
                        </tr>
                        <tr>
                            <th><i class="ti ti-users me-2"></i>Capacity</th>
                            <td>: {{ $boat->kapasitas }} People</td>
                        </tr>
                        <tr>
                            <th><i class="ti ti-currency-dollar me-2"></i>Rental Price</th>
                            <td>: <strong class="text-success">{{ $boat->formatted_harga }}</strong></td>
                        </tr>
                        <tr>
                            <th><i class="ti ti-info-circle me-2"></i>Status</th>
                            <td>: 
                                @if($boat->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Unactive</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th><i class="ti ti-calendar me-2"></i>Created</th>
                            <td>: {{ $boat->created_at->format('d M Y, H:i') }} WIB</td>
                        </tr>
                        <tr>
                            <th><i class="ti ti-clock me-2"></i>Last Update</th>
                            <td>: {{ $boat->updated_at->format('d M Y, H:i') }} WIB</td>
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
                            <h3 class="mt-2">{{ $boat->kapasitas }}</h3>
                            <p class="text-muted mb-0">Capacity</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3">
                            <i class="ti ti-currency-dollar fs-1 text-warning"></i>
                            <h3 class="mt-2">{{ $boat->formatted_harga }}</h3>
                            <p class="text-muted mb-0">Rental Price</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3">
                            @if($boat->is_active)
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