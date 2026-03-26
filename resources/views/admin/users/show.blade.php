@extends('layout.sidebar')

@section('title', 'User Detail')

@php
    $statusBadge = [
        'paid' => 'success',
        'pending' => 'warning',
        'failed' => 'danger',
        'cancelled' => 'secondary',
        'refund_requested' => 'info',
        'refunded' => 'dark',
    ];
@endphp

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h4 class="fw-bold mb-1">User Detail</h4>
        <p class="text-muted mb-0">Account identity and transaction history.</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-light border">
        <i class="ti ti-arrow-left"></i> Back to Users
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="badge bg-primary-subtle text-primary fs-4 px-3 py-2">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    </span>
                    <div>
                        <h5 class="mb-0">{{ $user->name ?: '-' }}</h5>
                        <small class="text-muted">User ID: {{ $user->id }}</small>
                    </div>
                </div>

                <div class="mb-2"><strong>Email:</strong> {{ $user->email }}</div>
                <div class="mb-2"><strong>Phone:</strong> {{ $user->phone ?: '-' }}</div>
                <div class="mb-2"><strong>Nationality:</strong> {{ $user->nationality ?: '-' }}</div>
                <div class="mb-2"><strong>Address:</strong> {{ $user->address ?: '-' }}</div>
                <div class="mb-2"><strong>Joined:</strong> {{ optional($user->created_at)->format('d M Y, H:i') }}</div>
                <div class="mb-0">
                    <strong>Profile Completion:</strong>
                    <span class="badge bg-info-subtle text-info">{{ $summary['profile_completion'] }}%</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="row g-3">
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">Total Orders</small>
                        <h4 class="fw-bold mb-0">{{ number_format($summary['total_orders']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">Paid Orders</small>
                        <h4 class="fw-bold mb-0">{{ number_format($summary['paid_orders']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">Pending Orders</small>
                        <h4 class="fw-bold mb-0">{{ number_format($summary['pending_orders']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">Cancelled/Failed</small>
                        <h4 class="fw-bold mb-0">{{ number_format($summary['cancelled_orders']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">Total Spent (MYR)</small>
                        <h4 class="fw-bold mb-0">RM {{ number_format((float) $summary['total_spent'], 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted d-block">Average Paid Order</small>
                        <h4 class="fw-bold mb-0">RM {{ number_format((float) $summary['avg_order_value'], 2) }}</h4>
                        <small class="text-muted">
                            Last Order:
                            {{ $summary['last_order_at'] ? \Carbon\Carbon::parse($summary['last_order_at'])->format('d M Y, H:i') : '-' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h6 class="mb-0 fw-bold">Reset User Password</h6>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            Use this when the user forgot their password. The user can log in immediately using the new password.
        </p>
        <form method="POST" action="{{ route('admin.users.password.update', $user) }}" class="row g-3">
            @csrf
            @method('PUT')

            <div class="col-md-4">
                <label class="form-label">Admin Current Password</label>
                <input
                    type="password"
                    name="admin_current_password"
                    class="form-control @error('admin_current_password', 'passwordReset') is-invalid @enderror"
                    required
                >
                @error('admin_current_password', 'passwordReset')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">New User Password</label>
                <div class="input-group">
                    <input
                        type="text"
                        id="newUserPassword"
                        name="new_password"
                        class="form-control @error('new_password', 'passwordReset') is-invalid @enderror"
                        autocomplete="new-password"
                        required
                    >
                    <button type="button" class="btn btn-outline-secondary" id="generatePasswordBtn">
                        Generate
                    </button>
                </div>
                @error('new_password', 'passwordReset')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted d-none" id="generatedPasswordHint"></small>
            </div>

            <div class="col-md-4">
                <label class="form-label">Confirm New Password</label>
                <input
                    type="text"
                    id="confirmNewUserPassword"
                    name="new_password_confirmation"
                    class="form-control"
                    autocomplete="new-password"
                    required
                >
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-warning">
                    <i class="ti ti-key"></i> Reset Password User
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h6 class="mb-0 fw-bold">Order Status Breakdown</h6>
        <div class="d-flex gap-2 flex-wrap">
            @forelse($statusBreakdown as $item)
                <span class="badge bg-light text-dark border">
                    {{ strtoupper(str_replace('_', ' ', $item->status)) }}: {{ $item->total }}
                </span>
            @empty
                <span class="text-muted small">No order data</span>
            @endforelse
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body border-bottom">
        <form method="GET" action="{{ route('admin.users.show', $user) }}" class="row g-2 align-items-end">
            <div class="col-sm-4 col-lg-3">
                <label class="form-label mb-1">Filter Status</label>
                <select name="status" class="form-select">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                    @foreach($availableStatuses as $availableStatus)
                        <option value="{{ $availableStatus }}" {{ $status === $availableStatus ? 'selected' : '' }}>
                            {{ strtoupper(str_replace('_', ' ', $availableStatus)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Apply</button>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Order ID</th>
                    <th>Created At</th>
                    <th>Packages</th>
                    <th class="text-center">Participants</th>
                    <th class="text-end">Amount (MYR)</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td class="ps-3 fw-semibold">{{ $order->id_order }}</td>
                        <td class="text-muted small">{{ optional($order->created_at)->format('d M Y, H:i') }}</td>
                        <td>
                            <span title="{{ $order->items->pluck('nama_paket')->join(', ') }}">
                                {{ $order->items->pluck('nama_paket')->take(2)->join(', ') ?: '-' }}
                                @if($order->items->count() > 2)
                                    <small class="text-muted">+{{ $order->items->count() - 2 }} more</small>
                                @endif
                            </span>
                        </td>
                        <td class="text-center">{{ number_format((int) $order->items->sum('jumlah_peserta')) }}</td>
                        <td class="text-end fw-semibold">RM {{ number_format((float) $order->base_amount, 2) }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $statusBadge[$order->status] ?? 'secondary' }}">
                                {{ strtoupper(str_replace('_', ' ', $order->status)) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
        <div class="card-body border-top">
            {{ $orders->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const generateBtn = document.getElementById('generatePasswordBtn');
    const passwordInput = document.getElementById('newUserPassword');
    const confirmInput = document.getElementById('confirmNewUserPassword');
    const hint = document.getElementById('generatedPasswordHint');

    if (!generateBtn || !passwordInput || !confirmInput || !hint) {
        return;
    }

    const upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const lower = 'abcdefghijklmnopqrstuvwxyz';
    const numbers = '0123456789';
    const symbols = '!@#$%^&*_-+=';
    const allChars = upper + lower + numbers + symbols;

    function randomInt(max) {
        if (window.crypto && window.crypto.getRandomValues) {
            const array = new Uint32Array(1);
            window.crypto.getRandomValues(array);
            return array[0] % max;
        }

        return Math.floor(Math.random() * max);
    }

    function pick(set) {
        return set[randomInt(set.length)];
    }

    function shuffle(chars) {
        for (let i = chars.length - 1; i > 0; i--) {
            const j = randomInt(i + 1);
            const temp = chars[i];
            chars[i] = chars[j];
            chars[j] = temp;
        }

        return chars;
    }

    function generatePassword(length) {
        const chars = [pick(upper), pick(lower), pick(numbers), pick(symbols)];

        while (chars.length < length) {
            chars.push(pick(allChars));
        }

        return shuffle(chars).join('');
    }

    generateBtn.addEventListener('click', async function () {
        const generated = generatePassword(14);
        passwordInput.value = generated;
        confirmInput.value = generated;

        let copied = false;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            try {
                await navigator.clipboard.writeText(generated);
                copied = true;
            } catch (e) {
                copied = false;
            }
        }

        hint.classList.remove('d-none');
        hint.textContent = copied
            ? 'Generated password filled and copied to clipboard.'
            : 'Generated password filled in both fields.';
    });
});
</script>
@endpush
