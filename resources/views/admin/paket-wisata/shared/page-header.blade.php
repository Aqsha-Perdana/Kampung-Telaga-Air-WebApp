@props([
    'icon' => 'bi-suitcase-lg',
    'title' => 'Tour Package',
    'subtitle' => '',
])

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="bi {{ $icon }}"></i> {{ $title }}</h2>
        @if($subtitle)
            <p class="text-muted">{{ $subtitle }}</p>
        @endif
    </div>
</div>
