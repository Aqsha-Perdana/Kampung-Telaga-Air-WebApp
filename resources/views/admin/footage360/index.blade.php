@extends('layout.sidebar')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="h3 mb-1 fw-bold text-gray-800">360° Footage Management</h1>
            <div class="d-flex align-items-center">
                <span class="badge bg-soft-success text-success border border-success-subtle px-2 py-1">
                    <i class="fas fa-cloud me-1"></i> Cloudinary Powered
                </span>
                <span class="ms-2 text-muted small">Total: {{ $footages->total() }} items</span>
            </div>
        </div>
        <a href="{{ route('footage360.create') }}" class="btn btn-primary shadow-sm px-4">
            <i class="fas fa-plus-circle me-1"></i> Add New Footage
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-uppercase small fw-bold text-muted">Preview</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted">Details</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted">Destination</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted text-center">Order</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted text-center">Status</th>
                            <th class="py-3 text-uppercase small fw-bold text-muted">Created At</th>
                            <th class="pe-4 py-3 text-uppercase small fw-bold text-muted text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($footages as $footage)
                        <tr>
                            <td class="ps-4">
                                <div class="position-relative group">
                                    <img src="{{ $footage->file_foto }}" 
                                         alt="{{ $footage->judul }}" 
                                         class="rounded shadow-sm border"
                                         style="width: 100px; height: 64px; object-fit: cover;">
                                    <div class="status-indicator {{ $footage->is_active ? 'bg-success' : 'bg-secondary' }}"></div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $footage->judul }}</div>
                                @if($footage->deskripsi)
                                    <div class="small text-muted text-truncate" style="max-width: 200px;">
                                        {{ $footage->deskripsi }}
                                    </div>
                                @endif
                                <div class="mt-1">
                                    <span class="badge rounded-pill bg-light text-primary border border-primary-subtle small-badge">
                                        <i class="fas fa-hdd me-1"></i> Cloudinary
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-map-marker-alt text-danger me-2 small"></i>
                                    <span class="small fw-semibold">{{ $footage->destinasi->nama }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold text-secondary">#{{ $footage->urutan }}</span>
                            </td>
                            <td class="text-center">
                                @if($footage->is_active)
                                    <span class="badge bg-soft-success text-success border border-success-subtle">Active</span>
                                @else
                                    <span class="badge bg-soft-secondary text-secondary border border-secondary-subtle">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted small">
                                    <i class="far fa-calendar-alt me-1"></i> {{ $footage->created_at->format('M d, Y') }}
                                </span>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="btn-group shadow-sm">
                                    <a href="{{ route('view360.show', $footage->id_footage360) }}" 
                                       class="btn btn-white btn-sm text-info border" 
                                       target="_blank"
                                       title="View 360°">
                                        <i class="ti ti-eye me-1"></i>
                                    </a>
                                    <a href="{{ route('footage360.edit', $footage->id_footage360) }}" 
                                       class="btn btn-white btn-sm text-warning border"
                                       title="Edit">
                                        <i class="ti ti-edit me-1   "></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-white btn-sm text-danger border"
                                            title="Delete"
                                            onclick="confirmDelete('{{ $footage->id_footage360 }}')">
                                        <i class="ti ti-trash me-1"></i>
                                    </button>
                                </div>

                                <form id="delete-form-{{ $footage->id_footage360 }}" 
                                      action="{{ route('footage360.destroy', $footage->id_footage360) }}" 
                                      method="POST" 
                                      class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="py-4">
                                    <div class="mb-3">
                                        <i class="fas fa-folder-open fa-4x text-light"></i>
                                    </div>
                                    <h5 class="text-muted">No Footage Found</h5>
                                    <p class="text-muted small">You haven't uploaded any 360° footage to Cloudinary yet.</p>
                                    <a href="{{ route('footage360.create') }}" class="btn btn-primary btn-sm mt-2">
                                        <i class="fas fa-plus me-1"></i> Upload Your First Media
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($footages->hasPages())
        <div class="card-footer bg-white border-top-0 py-3">
            {{ $footages->links() }}
        </div>
        @endif
    </div>
</div>

<style>
    /* UI Customizations */
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); }
    .bg-soft-secondary { background-color: rgba(108, 117, 125, 0.1); }
    .text-gray-800 { color: #2d3748; }
    .btn-white { background-color: #fff; }
    .btn-white:hover { background-color: #f8f9fa; }
    .small-badge { font-size: 0.7rem; padding: 2px 8px; }
    
    /* Active indicator on image */
    .status-indicator {
        position: absolute;
        bottom: -2px;
        right: -2px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid #fff;
    }
    
    /* Table hover effect */
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.02);
        transition: background-color 0.2s ease;
    }

    .table thead th {
        letter-spacing: 0.05em;
    }
</style>

<script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this footage? This will also remove the file from Cloudinary.')) {
            document.getElementById('delete-form-' + id).submit();
        }
    }
</script>
@endsection