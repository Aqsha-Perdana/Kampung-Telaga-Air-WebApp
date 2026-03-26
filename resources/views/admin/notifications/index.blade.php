@extends('layout.sidebar')

@section('content')
@php
    $badgeMeta = [
        'new_order' => ['label' => 'New Order', 'class' => 'primary', 'icon' => 'shopping-cart'],
        'payment_paid' => ['label' => 'Payment Paid', 'class' => 'success', 'icon' => 'credit-card'],
        'refund_requested' => ['label' => 'Refund Requested', 'class' => 'warning text-dark', 'icon' => 'arrow-back-up'],
        'refund_processed' => ['label' => 'Refund Processed', 'class' => 'dark', 'icon' => 'cash-banknote'],
        'cart_added' => ['label' => 'Added to Cart', 'class' => 'info text-dark', 'icon' => 'shopping-cart-plus'],
    ];
@endphp

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Notifications</h2>
        <p class="text-muted mb-0">View the same admin activity history shown in the header bell.</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span class="badge bg-primary-subtle text-primary px-3 py-2">Unread: {{ number_format($unreadCount) }}</span>
        <button type="button" class="btn btn-outline-primary" id="pageMarkAllRead">
            <i class="ti ti-checks"></i> Mark all as read
        </button>
    </div>
</div>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.notifications.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Filter by type</label>
                <select name="type" class="form-select">
                    <option value="">All notifications</option>
                    @foreach($typeOptions as $value => $label)
                        <option value="{{ $value }}" {{ $selectedType === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-filter"></i> Apply Filter
                </button>
            </div>
            @if($selectedType !== '')
                <div class="col-md-auto">
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-refresh"></i> Reset
                    </a>
                </div>
            @endif
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @forelse($notifications as $notification)
            @php
                $meta = $badgeMeta[$notification->type] ?? ['label' => ucfirst(str_replace('_', ' ', $notification->type)), 'class' => 'secondary', 'icon' => 'bell'];
                $read = $notification->reads->first();
                $payload = $notification->toPayload();
                $packages = $payload['package_names'] ?? [];
            @endphp
            <div class="border-bottom px-4 py-4 {{ $read ? 'bg-white' : 'bg-light-subtle' }}">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                            <span class="badge bg-{{ $meta['class'] }}">
                                <i class="ti ti-{{ $meta['icon'] }} me-1"></i>{{ $meta['label'] }}
                            </span>
                            <span class="badge {{ $read ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                {{ $read ? 'Read' : 'Unread' }}
                            </span>
                            <small class="text-muted">{{ optional($notification->event_created_at ?? $notification->created_at)->format('d M Y, H:i') }}</small>
                        </div>
                        <h5 class="mb-1">{{ $notification->title }}</h5>
                        <p class="text-muted mb-3">{{ $notification->message }}</p>

                        <div class="row g-3 small text-muted">
                            <div class="col-md-6">
                                <strong class="text-dark d-block">Customer</strong>
                                <span>{{ $notification->customer_name ?: '-' }}</span>
                                @if($notification->customer_email)
                                    <span class="d-block">{{ $notification->customer_email }}</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <strong class="text-dark d-block">Order</strong>
                                <span>{{ $notification->order_id ?: '-' }}</span>
                            </div>
                            <div class="col-md-6">
                                <strong class="text-dark d-block">Package</strong>
                                <span>{{ count($packages) ? implode(', ', $packages) : '-' }}</span>
                            </div>
                            <div class="col-md-6">
                                <strong class="text-dark d-block">Amount</strong>
                                <span>{{ $notification->currency }} {{ number_format((float) $notification->total_amount, 2) }}</span>
                            </div>
                            <div class="col-md-6">
                                <strong class="text-dark d-block">People</strong>
                                <span>{{ number_format((int) $notification->total_people) }}</span>
                            </div>
                            <div class="col-md-6">
                                <strong class="text-dark d-block">Origin</strong>
                                <span>{{ $notification->origin ?: '-' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-2 align-items-lg-end">
                        @if($canOpenOrders && !empty($payload['order_detail_url']))
                            <a href="{{ $payload['order_detail_url'] }}" class="btn btn-outline-primary">
                                <i class="ti ti-arrow-up-right"></i> Open Order Detail
                            </a>
                        @endif
                        @if($read?->read_at)
                            <small class="text-muted">Read at {{ $read->read_at->format('d M Y, H:i') }}</small>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="px-4 py-5 text-center text-muted">
                <i class="ti ti-bell-off fs-10 d-block mb-2"></i>
                No notifications found.
            </div>
        @endforelse
    </div>
</div>

<div class="mt-4">
    {{ $notifications->links() }}
</div>
@endsection

@section('scripts')
<script>
  (function () {
    const button = document.getElementById('pageMarkAllRead');
    if (!button) {
      return;
    }

    button.addEventListener('click', async function () {
      button.disabled = true;

      try {
        const response = await fetch(@json(route('admin.notifications.mark-read')), {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': @json(csrf_token()),
            'Accept': 'application/json'
          },
          body: JSON.stringify({ scope: 'all' })
        });

        if (!response.ok) {
          throw new Error('Failed to mark notifications as read.');
        }

        window.location.reload();
      } catch (error) {
        button.disabled = false;
        alert('Unable to mark notifications as read right now.');
      }
    });
  })();
</script>
@endsection

