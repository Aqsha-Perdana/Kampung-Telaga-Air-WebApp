@extends('layout.sidebar')

@section('title', 'User Management')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <h4 class="fw-bold mb-1">Users</h4>
        <p class="text-muted mb-0">Monitor identities of visitors who registered accounts.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted d-block mb-1">Total Users</small>
                <h4 class="mb-0 fw-bold">{{ number_format($stats['total_users']) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted d-block mb-1">New This Month</small>
                <h4 class="mb-0 fw-bold">{{ number_format($stats['new_this_month']) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted d-block mb-1">Active Buyers</small>
                <h4 class="mb-0 fw-bold">{{ number_format($stats['active_buyers']) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted d-block mb-1">Never Ordered</small>
                <h4 class="mb-0 fw-bold">{{ number_format($stats['never_ordered']) }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body border-bottom">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-end">
            <div class="col-lg-4">
                <label class="form-label mb-1">Search</label>
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    class="form-control"
                    placeholder="Name, email, or phone"
                >
            </div>
            <div class="col-sm-6 col-lg-2">
                <label class="form-label mb-1">Nationality</label>
                <select name="nationality" class="form-select">
                    <option value="all" {{ $nationality === 'all' ? 'selected' : '' }}>All</option>
                    @foreach($nationalities as $item)
                        <option value="{{ $item }}" {{ $nationality === $item ? 'selected' : '' }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6 col-lg-2">
                <label class="form-label mb-1">Sort</label>
                <select name="sort" class="form-select">
                    <option value="latest" {{ $sort === 'latest' ? 'selected' : '' }}>Latest</option>
                    <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Oldest</option>
                    <option value="most_orders" {{ $sort === 'most_orders' ? 'selected' : '' }}>Most Orders</option>
                    <option value="highest_spending" {{ $sort === 'highest_spending' ? 'selected' : '' }}>Highest Spending</option>
                </select>
            </div>
            <div class="col-sm-6 col-lg-2">
                <label class="form-label mb-1">Rows</label>
                <select name="per_page" class="form-select">
                    @foreach([10, 15, 25, 50] as $size)
                        <option value="{{ $size }}" {{ (int) $perPage === $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Apply</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-light border w-100">Reset</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Identity</th>
                    <th>Contact</th>
                    <th>Nationality</th>
                    <th>Address</th>
                    <th>Joined</th>
                    <th class="text-center">Orders</th>
                    <th class="text-center">Paid</th>
                    <th class="text-end">Spent (MYR)</th>
                    <th class="text-end pe-3">Last Order</th>
                    <th class="text-end pe-3">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary-subtle text-primary">
                                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                </span>
                                <div>
                                    <div class="fw-semibold">{{ $user->name ?: '-' }}</div>
                                    <small class="text-muted">#{{ $user->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="small fw-medium">{{ $user->email }}</div>
                            <div class="text-muted small">{{ $user->phone ?: '-' }}</div>
                        </td>
                        <td>{{ $user->nationality ?: '-' }}</td>
                        <td class="text-muted small" style="max-width: 220px;">
                            <span title="{{ $user->address }}">{{ $user->address ?: '-' }}</span>
                        </td>
                        <td>
                            <small>{{ optional($user->created_at)->format('d M Y, H:i') }}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">{{ number_format($user->orders_count) }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success">{{ number_format($user->paid_orders_count) }}</span>
                        </td>
                        <td class="text-end fw-semibold">RM {{ number_format((float) ($user->total_spent ?? 0), 2) }}</td>
                        <td class="text-end pe-3 text-muted small">
                            @if($user->last_order_at)
                                {{ \Carbon\Carbon::parse($user->last_order_at)->format('d M Y, H:i') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-light border">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            No user data found for current filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="card-body border-top">
            {{ $users->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
