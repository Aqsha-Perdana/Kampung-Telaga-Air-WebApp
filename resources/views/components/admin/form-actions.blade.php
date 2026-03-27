@props([
    'backUrl',
    'backLabel' => 'Cancel',
    'backIcon' => 'bi bi-x-circle',
    'submitLabel' => 'Save',
    'submitIcon' => 'bi bi-check-circle',
    'submitClass' => 'btn-primary',
    'backClass' => 'btn-secondary',
    'submitType' => 'submit',
])

<div class="admin-form-actions" data-admin-ai-anchor="1">
    <div class="admin-form-actions-shell">
        <a href="{{ $backUrl }}" class="btn {{ $backClass }} btn-lg shadow-sm admin-form-actions-cancel">
            <i class="{{ $backIcon }}"></i> {{ $backLabel }}
        </a>
        <button type="{{ $submitType }}" class="btn {{ $submitClass }} btn-lg shadow-sm admin-form-actions-submit">
            <i class="{{ $submitIcon }}"></i> {{ $submitLabel }}
        </button>
    </div>
</div>
