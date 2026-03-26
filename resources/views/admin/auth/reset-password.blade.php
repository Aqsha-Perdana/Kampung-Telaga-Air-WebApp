<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password Admin - Kampung Telaga Air</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/logo.png') }}" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
</head>
<body>
    <div class="login-wrapper">
        <div class="left-section">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="welcome-content">
                <div class="logo-section">
                    <img src="{{ asset('assets/images/logos/primary-logo.png') }}" alt="Logo Kampung Telaga Air">
                </div>
                <h1>Set New Password</h1>
                <p>Use a strong password to secure your admin account.</p>
            </div>
        </div>

        <div class="right-section">
            <div class="login-container">
                <div class="login-header">
                    <h2>Reset Password</h2>
                    <p>Please enter your new password.</p>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('admin.password.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-4">
                        <label class="form-label">Email</label>
                        <div class="input-wrapper">
                            <i class="bi bi-envelope-fill input-icon"></i>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $email) }}" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">New Password</label>
                        <div class="input-wrapper">
                            <i class="bi bi-lock-fill input-icon"></i>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Confirm New Password</label>
                        <div class="input-wrapper">
                            <i class="bi bi-shield-lock-fill input-icon"></i>
                            <input type="password" class="form-control" name="password_confirmation" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login">
                        <i class="bi bi-check-circle me-2"></i>Reset Password
                    </button>
                </form>

                <div class="back-link">
                    <a href="{{ route('admin.login') }}">
                        <i class="bi bi-arrow-left me-2"></i>Back to Admin Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

