<div class="container-fluid">
    @include('admin.paket-wisata.shared.page-header', [
        'icon' => 'bi-suitcase-lg',
        'title' => 'Add New Tour Package',
        'subtitle' => 'Select destinations, accommodations, and services to create attractive tour packages.',
    ])

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

    @include('admin.paket-wisata.shared.recommendations')

    <form action="{{ route('paket-wisata.store') }}" method="POST" id="paketForm" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">
            @include('admin.paket-wisata.shared.left-column')
            @include('admin.paket-wisata.shared.resources')
        </div>

        <x-admin.form-actions
            :back-url="route('paket-wisata.index')"
            back-label="Cancel"
            submit-label="Save Package"
            submit-icon="bi bi-check-circle"
            back-icon="bi bi-x-circle"
        />
    </form>
</div>
