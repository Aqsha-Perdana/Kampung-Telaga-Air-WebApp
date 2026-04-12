@extends('landing.layout')

@section('title', 'Destinations - Kampung Telaga Air')

@section('content')
<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="fw-bold mb-1">
                    <i class="bi bi-geo-alt"></i> Destinations
                </h2>
                <p class="text-muted mb-0">Explore beautiful places in Kampung Telaga Air.</p>
            </div>
        </div>

        <div class="row g-4">
            @forelse($destinasis as $destinasi)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm">
                        @php
                            $foto = $destinasi->fotos->first();
                        @endphp
                        <img
                            src="{{ $foto?->foto ? Storage::url($foto->foto) : asset('assets/images/backgrounds/bg-destination.JPG') }}"
                            class="card-img-top"
                            alt="{{ $destinasi->nama_destinasi }}"
                            style="height: 220px; object-fit: cover;"
                        >
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">{{ $destinasi->nama_destinasi }}</h5>
                            <p class="card-text text-muted small mb-3">
                                {{ \Illuminate\Support\Str::limit($destinasi->deskripsi, 120) }}
                            </p>
                            <a href="{{ route('landing.detail-destinasi', $destinasi->id_destinasi) }}" class="btn btn-primary mt-auto">
                                <i class="bi bi-eye"></i> View Detail
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        No destination is available right now.
                    </div>
                </div>
            @endforelse
        </div>

        @if($destinasis->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $destinasis->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
