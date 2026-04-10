@props([
    'icon' => 'bi-suitcase-lg',
    'title' => 'Tour Package',
    'subtitle' => '',
])

<div class="row mb-4">
    <div class="col-md-12 px-0">
        <div class="position-relative overflow-hidden rounded-4" style="height: 200px;">
            <img
                src="{{ asset('assets/images/backgrounds/bg-package.png') }}"
                alt="{{ $title }}"
                class="w-100 h-100"
                style="object-fit: cover; filter: brightness(0.6);">

            <div class="position-absolute top-50 start-50 translate-middle text-center text-white w-100 px-3" style="z-index: 2;">
                <h1 class="display-4 fw-bold mb-2" style="color: white !important; text-shadow: 2px 2px 8px rgba(0,0,0,0.8);">
                    {{ $title }}
                </h1>
                @if($subtitle)
                    <p class="lead mb-0" style="text-shadow: 1px 1px 6px rgba(0,0,0,0.65);">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
