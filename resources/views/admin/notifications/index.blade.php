@extends('layout.sidebar')

@section('content')
@php
    $badgeMeta = [
        'new_order' => ['label' => 'New Booking', 'class' => 'primary', 'icon' => 'shopping-cart'],
        'payment_paid' => ['label' => 'Payment Confirmed', 'class' => 'success', 'icon' => 'credit-card'],
        'refund_requested' => ['label' => 'Refund Requested', 'class' => 'warning text-dark', 'icon' => 'arrow-back-up'],
        'refund_processed' => ['label' => 'Refund Processed', 'class' => 'dark', 'icon' => 'cash-banknote'],
        'cart_added' => ['label' => 'Cart Activity', 'class' => 'info text-dark', 'icon' => 'shopping-cart-plus'],
    ];
@endphp

<div class="notif-page-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Notifications</h2>
        <p class="text-muted mb-0">A complete activity feed for bookings, payments, refunds, and cart activity.</p>
    </div>
    <div class="notif-page-actions d-flex flex-wrap gap-2 align-items-center">
        <span class="notif-pill notif-pill-primary">Unread {{ number_format($unreadCount) }}</span>
        <button type="button" class="btn btn-outline-primary btn-sm" id="pageMarkAllRead">
            <i class="ti ti-checks me-1"></i> Mark all as read
        </button>
    </div>
</div>

<div class="notif-summary-grid mb-3">
    <div class="notif-summary-card">
        <span class="notif-summary-label">Matching results</span>
        <strong>{{ number_format($filteredTotal) }}</strong>
        <small class="text-muted">Notifications in the current view</small>
    </div>
    <div class="notif-summary-card">
        <span class="notif-summary-label">Unread in view</span>
        <strong>{{ number_format($filteredUnreadCount) }}</strong>
        <small class="text-muted">Unread items after filters are applied</small>
    </div>
    <div class="notif-summary-card">
        <span class="notif-summary-label">Total unread</span>
        <strong>{{ number_format($unreadCount) }}</strong>
        <small class="text-muted">Unread items across all notifications</small>
    </div>
</div>

<div class="card notif-filter-card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.notifications.index') }}" class="row g-2 align-items-end">
            <div class="col-xl-3 col-lg-4 col-md-6">
                <label class="form-label small text-muted mb-1">Notification type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">All notifications</option>
                    @foreach($typeOptions as $value => $label)
                        <option value="{{ $value }}" {{ $selectedType === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-6">
                <label class="form-label small text-muted mb-1">Read status</label>
                <select name="read_status" class="form-select form-select-sm">
                    @foreach($readStatusOptions as $value => $label)
                        <option value="{{ $value }}" {{ $selectedReadStatus === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-6">
                <label class="form-label small text-muted mb-1">From date</label>
                <input type="date" name="date_from" value="{{ $selectedDateFrom }}" class="form-control form-control-sm">
            </div>
            <div class="col-xl-2 col-lg-3 col-md-6">
                <label class="form-label small text-muted mb-1">To date</label>
                <input type="date" name="date_to" value="{{ $selectedDateTo }}" class="form-control form-control-sm">
            </div>
            <div class="col-xl-auto col-lg-auto col-md-auto">
                <button type="submit" class="btn btn-primary btn-sm px-3">
                    <i class="ti ti-filter me-1"></i> Filter
                </button>
            </div>
            @if($hasActiveFilters)
                <div class="col-xl-auto col-lg-auto col-md-auto">
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-light border btn-sm px-3">
                        <i class="ti ti-refresh me-1"></i> Reset
                    </a>
                </div>
            @endif
        </form>

        <div class="notif-quick-filters mt-3">
            <span class="small text-muted">Quick range</span>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.notifications.index', array_filter(['date_from' => now()->toDateString(), 'date_to' => now()->toDateString(), 'type' => $selectedType, 'read_status' => $selectedReadStatus])) }}" class="btn btn-light border btn-sm">Today</a>
                <a href="{{ route('admin.notifications.index', array_filter(['date_from' => now()->subDays(6)->toDateString(), 'date_to' => now()->toDateString(), 'type' => $selectedType, 'read_status' => $selectedReadStatus])) }}" class="btn btn-light border btn-sm">Last 7 days</a>
                <a href="{{ route('admin.notifications.index', array_filter(['date_from' => now()->subDays(29)->toDateString(), 'date_to' => now()->toDateString(), 'type' => $selectedType, 'read_status' => $selectedReadStatus])) }}" class="btn btn-light border btn-sm">Last 30 days</a>
                <a href="{{ route('admin.notifications.index', array_filter(['read_status' => 'unread', 'type' => $selectedType, 'date_from' => $selectedDateFrom, 'date_to' => $selectedDateTo])) }}" class="btn btn-light border btn-sm">Unread only</a>
            </div>
        </div>
    </div>
</div>

@if($hasActiveFilters)
    <div class="notif-active-filters mb-3">
        <span class="small text-muted">Active filters</span>
        <div class="d-flex flex-wrap gap-2 mt-2">
            @if($selectedType !== '')
                <span class="notif-filter-chip">Type: {{ $typeOptions[$selectedType] ?? $selectedType }}</span>
            @endif
            @if($selectedReadStatus !== '')
                <span class="notif-filter-chip">Read status: {{ $readStatusOptions[$selectedReadStatus] ?? $selectedReadStatus }}</span>
            @endif
            @if($selectedDateFrom !== '')
                <span class="notif-filter-chip">From: {{ $selectedDateFrom }}</span>
            @endif
            @if($selectedDateTo !== '')
                <span class="notif-filter-chip">To: {{ $selectedDateTo }}</span>
            @endif
        </div>
    </div>
@endif

<div class="card notif-list-card border-0 shadow-sm">
    <div class="card-body p-0">
        @forelse($notifications as $notification)
            @php
                $meta = $badgeMeta[$notification->type] ?? ['label' => ucfirst(str_replace('_', ' ', $notification->type)), 'class' => 'secondary', 'icon' => 'bell'];
                $read = $notification->reads->first();
                $payload = $notification->toPayload();
                $packages = $payload['package_names'] ?? [];
                $packageLabel = count($packages) ? implode(', ', $packages) : 'No package data';
                $eventTime = optional($notification->event_created_at ?? $notification->created_at);
            @endphp
            <div class="notif-row {{ $read ? 'is-read' : 'is-unread' }}">
                <div class="notif-row-accent bg-{{ \Illuminate\Support\Str::before($meta['class'], ' ') }}"></div>
                <div class="notif-row-body">
                    <div class="notif-row-main">
                        <div class="notif-icon-wrap bg-{{ $meta['class'] }}">
                            <i class="ti ti-{{ $meta['icon'] }}"></i>
                        </div>
                        <div class="notif-copy">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <span class="badge bg-{{ $meta['class'] }}">{{ $meta['label'] }}</span>
                                <span class="badge {{ $read ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                    {{ $read ? 'Read' : 'Unread' }}
                                </span>
                                <small class="text-muted">{{ $eventTime?->format('d M Y, H:i') }}</small>
                            </div>
                            <h6 class="mb-1">{{ $notification->title }}</h6>
                            <p class="text-muted mb-2">{{ $notification->message }}</p>

                            <div class="notif-facts">
                                <span><strong>Customer:</strong> {{ $notification->customer_name ?: 'Unknown customer' }}</span>
                                <span><strong>Order:</strong> {{ $notification->order_id ?: 'Not linked yet' }}</span>
                                <span><strong>Package:</strong> {{ $packageLabel }}</span>
                                <span><strong>Amount:</strong> {{ $notification->currency }} {{ number_format((float) $notification->total_amount, 2) }}</span>
                                <span><strong>Participants:</strong> {{ number_format((int) $notification->total_people) }}</span>
                                <span><strong>Source:</strong> {{ $notification->origin ?: 'Unknown' }}</span>
                                @if($notification->customer_email)
                                    <span><strong>Email:</strong> {{ $notification->customer_email }}</span>
                                @endif
                                @if($read?->read_at)
                                    <span><strong>Read at:</strong> {{ $read->read_at->format('d M Y, H:i') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="notif-row-actions">
                        @if($canOpenOrders && !empty($payload['order_detail_url']))
                            <a href="{{ $payload['order_detail_url'] }}" class="btn btn-outline-primary btn-sm">
                                <i class="ti ti-arrow-up-right me-1"></i> Open order
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="notif-empty-state text-center text-muted py-5 px-4">
                <i class="ti ti-bell-off fs-10 d-block mb-2"></i>
                <h6 class="mb-1 text-dark">No notifications yet</h6>
                <p class="mb-0">New admin activity will appear here automatically.</p>
            </div>
        @endforelse
    </div>
</div>

<div class="mt-3">
    {{ $notifications->links() }}
</div>
@endsection

@section('styles')
<style>
  .notif-page-header h2 {
    font-size: 1.7rem;
    font-weight: 700;
  }

  .notif-summary-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
  }

  .notif-summary-card {
    background: #fff;
    border: 1px solid #eef2f7;
    border-radius: 16px;
    padding: 16px 18px;
    box-shadow: 0 10px 26px rgba(15, 23, 42, 0.04);
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .notif-summary-card strong {
    font-size: 1.55rem;
    line-height: 1;
    color: #101828;
  }

  .notif-summary-label {
    font-size: 0.78rem;
    font-weight: 700;
    color: #475467;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .notif-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
  }

  .notif-pill-primary {
    background: #eef4ff;
    color: #2850a7;
  }

  .notif-filter-card .card-body,
  .notif-list-card .card-body {
    background: #fff;
  }

  .notif-quick-filters {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding-top: 12px;
    border-top: 1px solid #f1f5f9;
  }

  .notif-active-filters {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .notif-filter-chip {
    display: inline-flex;
    align-items: center;
    padding: 6px 10px;
    border-radius: 999px;
    background: #f5f7fb;
    border: 1px solid #e4e7ec;
    color: #344054;
    font-size: 0.78rem;
    font-weight: 600;
  }

  .notif-row {
    position: relative;
    display: flex;
    border-bottom: 1px solid #eef2f7;
  }

  .notif-row:last-child {
    border-bottom: 0;
  }

  .notif-row.is-unread {
    background: #fbfdff;
  }

  .notif-row-accent {
    width: 3px;
    flex-shrink: 0;
    opacity: 0.9;
  }

  .notif-row.is-read .notif-row-accent {
    opacity: 0.35;
  }

  .notif-row-body {
    width: 100%;
    display: flex;
    justify-content: space-between;
    gap: 16px;
    padding: 16px 18px;
  }

  .notif-row-main {
    display: flex;
    gap: 14px;
    min-width: 0;
    flex: 1;
  }

  .notif-icon-wrap {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    flex-shrink: 0;
    font-size: 1rem;
  }

  .notif-copy h6 {
    font-size: 0.98rem;
    font-weight: 700;
  }

  .notif-copy p {
    font-size: 0.9rem;
    line-height: 1.45;
  }

  .notif-facts {
    display: flex;
    flex-wrap: wrap;
    gap: 8px 14px;
    color: #667085;
    font-size: 0.8rem;
  }

  .notif-facts span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }

  .notif-facts strong {
    color: #344054;
    font-weight: 600;
  }

  .notif-row-actions {
    display: flex;
    align-items: flex-start;
    justify-content: flex-end;
    flex-shrink: 0;
  }

  .notif-empty-state h6 {
    font-weight: 700;
  }

  @media (max-width: 991px) {
    .notif-summary-grid {
      grid-template-columns: 1fr;
    }

    .notif-row-body {
      flex-direction: column;
    }

    .notif-row-actions {
      justify-content: flex-start;
      padding-left: 52px;
    }
  }

  @media (max-width: 576px) {
    .notif-quick-filters {
      align-items: flex-start;
      justify-content: flex-start;
    }

    .notif-row-body {
      padding: 14px;
    }

    .notif-row-main {
      gap: 12px;
    }

    .notif-icon-wrap {
      width: 34px;
      height: 34px;
      border-radius: 10px;
    }

    .notif-row-actions {
      padding-left: 46px;
    }
  }
</style>
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
