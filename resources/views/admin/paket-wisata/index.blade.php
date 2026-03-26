@extends('layout.sidebar')
@section('content')
<div class="container-fluid">
    <!-- Header dengan Background Image -->
    <div class="row mb-4">
        <div class="col-md-12 px-0">
            <div class="position-relative" style="height: 200px; border-radius: 10px; overflow: hidden;">
                <!-- Background Image -->
                <img src="{{ asset('assets/images/backgrounds/bg-package.png') }}" 
                     alt="Background" 
                     class="w-100 h-100" 
                     style="object-fit: cover; filter: brightness(0.6);">
                
                <!-- Overlay Text -->
                <div class="position-absolute top-50 start-50 translate-middle text-center text-white" style="z-index: 2;">
                    <h1 class="display-4 fw-bold mb-2" style="color: white !important; text-shadow: 2px 2px 8px rgba(0,0,0,0.8);">Tour Package Data</h1>
                    <p class="lead mb-0">Manage All Your Package Data</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Package List</h5>
            <a href="{{ route('paket-wisata.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Package
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Package ID</th>
                            <th>Package Name</th>
                            <th>Duration</th>
                            <th>Package Contents</th>
                            <th>Cost Price</th>
                            <th>Selling Price</th>
                            <th>Final Price</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paketWisatas as $index => $paket)
                        @php
                            $profit = $paket->harga_final - $paket->harga_modal;
                            $profitPersen = $paket->harga_modal > 0 ? (($profit / $paket->harga_modal) * 100) : 0;
                        @endphp
                        <tr>
                            <td>{{ $paketWisatas->firstItem() + $index }}</td>
                            <td><span class="badge bg-primary">{{ $paket->id_paket }}</span></td>
                            <td><strong>{{ $paket->nama_paket }}</strong></td>
                            <td>{{ $paket->durasi_hari }} Days</td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @if($paket->destinasis->count() > 0)
                                        <span class="badge bg-info">{{ $paket->destinasis->count() }} Destination</span>
                                    @endif
                                    @if($paket->homestays->count() > 0)
                                        <span class="badge bg-success">{{ $paket->homestays->count() }} Homestay</span>
                                    @endif
                                    @if($paket->paketCulinaries->count() > 0)
                                        <span class="badge bg-warning text-dark">{{ $paket->paketCulinaries->count() }} Culinary</span>
                                    @endif
                                    @if($paket->boats->count() > 0)
                                        <span class="badge bg-primary">{{ $paket->boats->count() }} Boat</span>
                                    @endif
                                    @if($paket->kiosks->count() > 0)
                                        <span class="badge bg-secondary">{{ $paket->kiosks->count() }} Kiosk</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <small class="text-muted">{{ format_ringgit($paket->harga_total) }}</small>
                            </td>
                            <td>
                                <strong>{{ format_ringgit($paket->harga_jual) }}</strong>
                                @if($paket->tipe_diskon !== 'none')
                                    <br>
                                    <small class="badge bg-danger">
                                        @if($paket->tipe_diskon === 'nominal')
                                            -{{ format_ringgit($paket->diskon_nominal) }}
                                        @else
                                            -{{ $paket->diskon_persen }}%
                                        @endif
                                    </small>
                                @endif
                            </td>
                            <td>
                                <strong class="text-success">{{ format_ringgit($paket->harga_final) }}</strong>
                            </td>
                            <td>
                                @if($paket->status == 'aktif')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('paket-wisata.show', $paket->id_paket) }}" 
                                       class="btn btn-info btn-sm" 
                                       title="Detail">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <a href="{{ route('paket-wisata.edit', $paket->id_paket) }}" 
                                       class="btn btn-warning btn-sm" 
                                       title="Edit">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <form action="{{ route('paket-wisata.destroy', $paket->id_paket) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Yakin ingin menghapus paket wisata ini?')"
                                          style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center">No tour package data available yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $paketWisatas->links() }}
            </div>
        </div>
    </div>
</div>

<style>
.table th {
    white-space: nowrap;
}

.btn-group .btn {
    padding: 0.25rem 0.5rem;
}
</style>
@endsection