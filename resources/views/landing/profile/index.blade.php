@extends('landing.layout')

@section('content')
@php
    $statusMeta = [
        'paid' => ['label' => 'Completed', 'class' => 'success'],
        'pending' => ['label' => 'Pending', 'class' => 'warning'],
        'failed' => ['label' => 'Failed', 'class' => 'danger'],
        'cancelled' => ['label' => 'Cancelled', 'class' => 'secondary'],
        'refund_requested' => ['label' => 'Refund Requested', 'class' => 'info'],
        'refunded' => ['label' => 'Refunded', 'class' => 'dark'],
    ];
@endphp

<section class="profile-page py-5" style="margin-top: 84px;">
    <div class="container">
        <div class="profile-head mb-4">
            <h2 class="fw-bold mb-1">Traveler Profile</h2>
            <p class="text-muted mb-0">Manage your account, security, and order activity.</p>
        </div>

        @if(session('success_profile'))
            <div class="alert alert-success">{{ session('success_profile') }}</div>
        @endif

        @if(session('success_password'))
            <div class="alert alert-success">{{ session('success_password') }}</div>
        @endif

        <div class="row g-4 align-items-start">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm profile-side-card">
                    <div class="card-body p-4 p-md-4">
                        <div class="profile-avatar mb-3">
                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                        </div>
                        <h5 class="fw-semibold mb-1">{{ $user->name }}</h5>
                        <p class="text-muted mb-3 small">{{ $user->email }}</p>

                        <div class="profile-meta">
                            <div>
                                <small class="text-muted d-block">Phone Number</small>
                                <strong>{{ $user->phone ?: '-' }}</strong>
                            </div>
                            <div>
                                <small class="text-muted d-block">Nationality</small>
                                <strong>{{ $user->nationality ?: '-' }}</strong>
                            </div>
                            <div>
                                <small class="text-muted d-block">Member Since</small>
                                <strong>{{ optional($user->created_at)->format('d M Y') }}</strong>
                            </div>
                        </div>

                        <hr class="my-3">

                        <div class="profile-side-stats">
                            <div>
                                <small>Total Orders</small>
                                <strong>{{ $totalOrders }}</strong>
                            </div>
                            <div>
                                <small>Completed</small>
                                <strong>{{ $completedOrders }}</strong>
                            </div>
                            <div>
                                <small>Total Spending</small>
                                <strong>RM {{ number_format($totalSpent, 2) }}</strong>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="{{ route('orders.history') }}" class="btn btn-outline-primary btn-sm py-2">
                                <i class="bi bi-bag-check me-1"></i> Full History
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="profile-tabs-wrap">
                    <div class="profile-tabs-nav">
                        <a href="{{ route('wisatawan.profile', ['tab' => 'profile']) }}" class="profile-tab-link {{ $activeTab === 'profile' ? 'is-active' : '' }}">
                            <i class="bi bi-person-vcard me-1"></i> Profile
                        </a>
                        <a href="{{ route('wisatawan.profile', ['tab' => 'security']) }}" class="profile-tab-link {{ $activeTab === 'security' ? 'is-active' : '' }}">
                            <i class="bi bi-shield-lock me-1"></i> Security
                        </a>
                        <a href="{{ route('wisatawan.profile', ['tab' => 'activity']) }}" class="profile-tab-link {{ $activeTab === 'activity' ? 'is-active' : '' }}">
                            <i class="bi bi-activity me-1"></i> Activity
                        </a>
                    </div>
                </div>

                <div class="tab-panel {{ $activeTab === 'profile' ? 'is-active' : '' }}">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="fw-semibold mb-3">Profile Information</h5>
                            <form action="{{ route('wisatawan.profile.update', ['tab' => 'profile']) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name', 'profileUpdate') is-invalid @enderror" required>
                                        @error('name', 'profileUpdate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email', 'profileUpdate') is-invalid @enderror" required>
                                        @error('email', 'profileUpdate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control @error('phone', 'profileUpdate') is-invalid @enderror" placeholder="+60 ...">
                                        @error('phone', 'profileUpdate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nationality</label>
                                        <input type="text" name="nationality" value="{{ old('nationality', $user->nationality) }}" class="form-control @error('nationality', 'profileUpdate') is-invalid @enderror" placeholder="Malaysia / Indonesia / Other">
                                        @error('nationality', 'profileUpdate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Address</label>
                                        <textarea name="address" rows="3" class="form-control @error('address', 'profileUpdate') is-invalid @enderror" placeholder="Full address">{{ old('address', $user->address) }}</textarea>
                                        @error('address', 'profileUpdate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check2-circle me-1"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="tab-panel {{ $activeTab === 'security' ? 'is-active' : '' }}">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                <h5 class="fw-semibold mb-0">Account Security</h5>
                            </div>

                            <form action="{{ route('wisatawan.profile.password.update', ['tab' => 'security']) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" name="current_password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" required>
                                        @error('current_password', 'updatePassword')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="new_password" class="form-control @error('new_password', 'updatePassword') is-invalid @enderror" required>
                                        @error('new_password', 'updatePassword')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" name="new_password_confirmation" class="form-control" required>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-dark">
                                        <i class="bi bi-shield-lock me-1"></i> Update Password
                                    </button>
                                </div>
                            </form>

                            <div class="security-note mt-3">
                                <i class="bi bi-info-circle me-1"></i>
                                Use a mix of uppercase, lowercase, numbers, and symbols for stronger account security.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-panel {{ $activeTab === 'activity' ? 'is-active' : '' }}">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-4">
                            <h5 class="fw-semibold mb-3">Activity Summary</h5>
                            <div class="activity-kpi-grid">
                                <div class="activity-kpi-item">
                                    <small>Total Orders</small>
                                    <strong>{{ $totalOrders }}</strong>
                                </div>
                                <div class="activity-kpi-item">
                                    <small>Completed Orders</small>
                                    <strong>{{ $completedOrders }}</strong>
                                </div>
                                <div class="activity-kpi-item">
                                    <small>Needs Attention</small>
                                    <strong>{{ $pendingOrders }}</strong>
                                </div>
                                <div class="activity-kpi-item">
                                    <small>Total Spending</small>
                                    <strong>RM {{ number_format($totalSpent, 2) }}</strong>
                                </div>
                            </div>
                            <p class="text-muted small mb-0 mt-3">
                                @if($latestOrderAt)
                                    Last activity on {{ \Carbon\Carbon::parse($latestOrderAt)->format('d M Y, H:i') }}.
                                @else
                                    No transaction activity yet for this account.
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-semibold mb-0">Recent Activity</h5>
                                <a href="{{ route('orders.history') }}" class="small text-decoration-none">View all</a>
                            </div>

                            @if($recentOrders->isEmpty())
                                <div class="text-center py-4">
                                    <i class="bi bi-inbox fs-2 text-muted"></i>
                                    <p class="mb-0 text-muted mt-2">No order history yet.</p>
                                </div>
                            @else
                                <div class="activity-list">
                                    @foreach($recentOrders as $order)
                                        @php
                                            $meta = $statusMeta[$order->status] ?? ['label' => ucfirst($order->status), 'class' => 'secondary'];
                                        @endphp
                                        <a href="{{ route('orders.show', $order->id_order) }}" class="activity-item">
                                            <div class="activity-item__left">
                                                <strong>{{ $order->id_order }}</strong>
                                                <small class="text-muted d-block">
                                                    {{ optional($order->created_at)->format('d M Y, H:i') }}
                                                </small>
                                                <small class="text-muted d-block">
                                                    {{ $order->items->pluck('nama_paket')->take(2)->join(', ') ?: 'Tour package' }}
                                                </small>
                                            </div>
                                            <div class="activity-item__right text-end">
                                                <span class="badge bg-{{ $meta['class'] }}">{{ $meta['label'] }}</span>
                                                <div class="mt-1 fw-semibold">RM {{ number_format((float) ($order->base_amount ?? $order->total_amount), 2) }}</div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .profile-page .profile-head {
        background: linear-gradient(145deg, #f8fbff 0%, #ffffff 100%);
        border: 1px solid rgba(148, 163, 184, .24);
        border-radius: 18px;
        padding: 1rem 1.1rem;
    }

    .profile-page .profile-side-card {
        position: sticky;
        top: 96px;
    }

    .profile-page .profile-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        font-weight: 700;
        color: #1d4ed8;
        background: linear-gradient(160deg, #dbeafe 0%, #eff6ff 100%);
        border: 1px solid rgba(59, 130, 246, 0.25);
    }

    .profile-page .profile-meta {
        display: grid;
        gap: .8rem;
    }

    .profile-page .profile-side-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .5rem;
        margin-bottom: .9rem;
    }

    .profile-page .profile-side-stats div {
        border: 1px solid rgba(148, 163, 184, .26);
        border-radius: 12px;
        padding: .5rem;
        background: #f8fbff;
    }

    .profile-page .profile-side-stats small {
        display: block;
        font-size: .68rem;
        color: #64748b;
    }

    .profile-page .profile-side-stats strong {
        display: block;
        font-size: .85rem;
        color: #1e293b;
        margin-top: 2px;
    }

    .profile-page .profile-tabs-wrap {
        margin-bottom: .9rem;
    }

    .profile-page .profile-tabs-nav {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
        padding: .45rem;
        border-radius: 14px;
        background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
        border: 1px solid rgba(148, 163, 184, .24);
    }

    .profile-page .profile-tab-link {
        flex: 1;
        min-width: 130px;
        text-align: center;
        text-decoration: none;
        color: #334155;
        padding: .58rem .75rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: .92rem;
        transition: all .2s ease;
    }

    .profile-page .profile-tab-link:hover {
        color: #1d4ed8;
        background: rgba(255, 255, 255, .75);
    }

    .profile-page .profile-tab-link.is-active {
        color: #1d4ed8;
        background: #ffffff;
        box-shadow: 0 10px 20px rgba(29, 78, 216, .12);
    }

    .profile-page .tab-panel {
        display: none;
    }

    .profile-page .tab-panel.is-active {
        display: block;
    }

    .profile-page .security-note {
        border: 1px dashed rgba(59, 130, 246, .28);
        border-radius: 12px;
        background: #f8fbff;
        padding: .72rem .85rem;
        color: #475569;
        font-size: .9rem;
    }

    .profile-page .activity-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .6rem;
    }

    .profile-page .activity-kpi-item {
        border: 1px solid rgba(148, 163, 184, .22);
        border-radius: 12px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        padding: .7rem .75rem;
    }

    .profile-page .activity-kpi-item small {
        display: block;
        color: #64748b;
        font-size: .76rem;
    }

    .profile-page .activity-kpi-item strong {
        display: block;
        color: #0f172a;
        margin-top: .25rem;
        font-size: .95rem;
    }

    .profile-page .activity-list {
        display: grid;
        gap: .6rem;
    }

    .profile-page .activity-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .75rem;
        border: 1px solid rgba(148, 163, 184, .22);
        border-radius: 12px;
        background: #fff;
        padding: .8rem .9rem;
        text-decoration: none;
        color: inherit;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .profile-page .activity-item:hover {
        border-color: rgba(59, 130, 246, .35);
        box-shadow: 0 10px 20px rgba(30, 64, 175, .08);
    }

    .profile-page .activity-item__left strong {
        font-size: .93rem;
    }

    @media (max-width: 991.98px) {
        .profile-page .profile-side-card {
            position: static;
        }
    }

    @media (max-width: 767.98px) {
        .profile-page {
            margin-top: 74px !important;
        }

        .profile-page .card-body {
            padding: 1rem !important;
        }

        .profile-page .profile-side-stats {
            grid-template-columns: 1fr;
        }

        .profile-page .profile-tab-link {
            min-width: 100%;
        }

        .profile-page .activity-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .profile-page .activity-item {
            flex-direction: column;
            align-items: flex-start;
        }

        .profile-page .activity-item__right {
            text-align: left !important;
        }
    }
</style>
@endsection
