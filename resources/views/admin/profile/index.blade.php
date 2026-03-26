@extends('layout.sidebar')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-1">Admin Profile</h4>
            <p class="text-muted mb-0">Manage account information and security settings.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="mb-1">{{ auth('admin')->user()->name }}</h5>
                    <p class="text-muted mb-2">{{ auth('admin')->user()->email }}</p>
                    <span class="badge bg-primary">
                        {{ auth('admin')->user()->isAdmin() ? 'Admin' : 'Pengelola' }}
                    </span>

                    <hr>

                    <div class="mb-2">
                        <small class="text-muted d-block">Phone</small>
                        <strong>{{ auth('admin')->user()->phone ?: '-' }}</strong>
                    </div>
                    <div>
                        <small class="text-muted d-block">Joined</small>
                        <strong>{{ optional(auth('admin')->user()->created_at)->format('d M Y') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Profile Information</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" value="{{ old('name', auth('admin')->user()->name) }}" class="form-control @error('name', 'profileUpdate') is-invalid @enderror" required>
                                @error('name', 'profileUpdate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" value="{{ old('email', auth('admin')->user()->email) }}" class="form-control @error('email', 'profileUpdate') is-invalid @enderror" required>
                                @error('email', 'profileUpdate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" value="{{ old('phone', auth('admin')->user()->phone) }}" class="form-control @error('phone', 'profileUpdate') is-invalid @enderror" placeholder="+60 ...">
                                @error('phone', 'profileUpdate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Save Profile</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Change Password</h6>
                    <a href="{{ route('admin.password.request') }}" class="small text-decoration-none">Forgot password?</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.profile.password.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control @error('current_password', 'passwordUpdate') is-invalid @enderror" required>
                                @error('current_password', 'passwordUpdate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control @error('new_password', 'passwordUpdate') is-invalid @enderror" required>
                                @error('new_password', 'passwordUpdate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="new_password_confirmation" class="form-control" required>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-dark">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

