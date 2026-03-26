<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Visitor - Kampung Telaga Air</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/logo.png') }}" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
    <style>
        body {
            transition: opacity 0.22s ease, transform 0.22s ease;
        }

        body.page-enter {
            opacity: 0;
            transform: translateY(8px);
        }

        body.page-ready {
            opacity: 1;
            transform: translateY(0);
        }

        body.page-exit {
            opacity: 0;
            transform: translateY(8px);
        }

        .register-link {
            margin-top: 18px;
            text-align: center;
        }

        .register-link p {
            margin: 0 0 10px;
            color: #64748b;
            font-size: 14px;
        }

        .btn-register {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-width: 180px;
            padding: 11px 16px;
            border-radius: 10px;
            border: 1.5px solid #2a93cc;
            background: #f0f9ff;
            color: #247eaf;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .btn-register:hover {
            background: linear-gradient(135deg, #96defb 0%, #2a93cc 100%);
            color: #ffffff;
            border-color: #2a93cc;
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="page-enter">
    <div class="login-wrapper">
        <!-- Left Section -->
        <div class="left-section">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            
            <div class="welcome-content">
                <div class="logo-section">
                    <img src="{{ asset('assets/images/logos/primary-logo.png') }}" alt="Logo Kampung Telaga Air">
                </div>
                
                <h1>Welcome, Visitor</h1>
                <p>Tourism Service Platform<br>Kampung Telaga Air</p>
            </div>
        </div>

        <!-- Right Section -->
        <div class="right-section">
            <div class="login-container">
                <div class="login-header">
                    <h2>Log in to your account</h2>
                    <p>Please log in to continue your journey.</p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>{{ session('success') }}</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span>
                            @foreach($errors->all() as $error)
                                {{ $error }}
                            @endforeach
                        </span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('wisatawan.login.post') }}" method="POST" id="loginForm">
                    @csrf                    
                    <div class="mb-4">
                        <label class="form-label">Email</label>
                        <div class="input-wrapper">
                            <i class="bi bi-envelope-fill input-icon"></i>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   name="email" 
                                   placeholder="name@email.com"
                                   value="{{ old('email') }}"
                                   required 
                                   autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="input-wrapper">
                            <i class="bi bi-lock-fill input-icon"></i>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   name="password" 
                                   id="password"
                                   placeholder="Enter password"
                                   required>
                            <i class="bi bi-eye password-toggle" id="togglePassword"></i>
                        </div>
                    </div>

                    <div class="form-check d-flex justify-content-between align-items-center">
                        <div>
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember">
                                Remember Me
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login
                    </button>
                </form>

                <div class="register-link">
                    <p>Don't have an account yet?</p>
                    <a href="{{ route('wisatawan.register') }}" class="btn-register js-page-transition">
                        <i class="bi bi-person-plus"></i>
                        Register Now
                    </a>
                </div>

                <div class="back-link">
                    <a href="{{ url('/') }}" class="js-page-transition">
                        <i class="bi bi-arrow-left me-2"></i>Back to Home
                    </a>
                </div>

                <div class="footer-text">
                    <p>
                        <i class="bi bi-shield-lock-fill"></i>
                       Protected with end-to-end encryption
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });

        // Form submission animation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = this.querySelector('.btn-login');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            btn.disabled = true;
        });

        requestAnimationFrame(() => document.body.classList.add('page-ready'));

        document.querySelectorAll('.js-page-transition').forEach((link) => {
            link.addEventListener('click', function (event) {
                if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                    return;
                }

                const href = this.getAttribute('href');
                if (!href || href.startsWith('#')) {
                    return;
                }

                event.preventDefault();
                document.body.classList.remove('page-ready');
                document.body.classList.add('page-exit');

                setTimeout(() => {
                    window.location.href = href;
                }, 180);
            });
        });
    </script>
</body>
</html>
