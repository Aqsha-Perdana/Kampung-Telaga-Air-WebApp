<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Kampung Telaga Air</title>
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
                    <img src="{{ asset('assets/images/logos/primary-logo.png') }}" alt="Kampung Telaga Air Logo">
                </div>
                <h1>Reset your password</h1>
                <p>Enter your visitor email and we will send a secure reset link to your inbox.</p>
            </div>
        </div>

        <div class="right-section">
            <div class="login-container">
                <div class="login-header">
                    <h2>Forgot Password</h2>
                    <p>We will email you a link to create a new password.</p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('wisatawan.password.email') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">Email Address</label>
                        <div class="input-wrapper">
                            <i class="bi bi-envelope-fill input-icon"></i>
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="name@email.com" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login">
                        <i class="bi bi-send me-2"></i>Send Reset Link
                    </button>
                </form>

                <div class="back-link">
                    <a href="{{ route('wisatawan.login') }}">
                        <i class="bi bi-arrow-left me-2"></i>Back to Visitor Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
