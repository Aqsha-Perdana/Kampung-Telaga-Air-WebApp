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
            min-height: 100vh;
            overflow: auto;
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

        .login-wrapper {
            min-height: 100vh;
            height: auto;
        }

        .register-link {
            margin-top: 24px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
        }

        .register-link p {
            margin: 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.45;
        }

        .btn-register {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-width: 208px;
            min-height: 48px;
            padding: 10px 16px;
            border-radius: 12px;
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

        .google-login-btn {
            width: min(100%, 400px);
            margin-left: auto;
            margin-right: auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 54px;
            padding: 11px 18px;
            border-radius: 16px;
            border: 1.5px solid #d7e2ec;
            background: #ffffff;
            color: #1f2937;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.07);
            transition: all 0.2s ease;
        }

        .google-login-btn:hover {
            transform: translateY(-1px);
            border-color: #bdd3e0;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.10);
            color: #111827;
        }

        .google-login-btn:focus {
            color: #111827;
            box-shadow: 0 0 0 4px rgba(66, 133, 244, 0.14);
        }

        .google-login-badge {
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .google-login-badge svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .right-section {
            align-items: flex-start;
            overflow-y: auto;
            padding: 28px 54px;
        }

        .login-container {
            max-width: 520px;
            width: 100%;
            padding-inline: 10px;
            margin: 8px 0 20px;
        }

        .login-header {
            text-align: left;
            margin-bottom: 24px;
        }

        .login-header h2 {
            line-height: 1.14;
            margin-bottom: 10px;
        }

        .login-header p {
            line-height: 1.55;
            max-width: 400px;
            margin-bottom: 0;
        }

        .mb-4 {
            margin-bottom: 1.1rem !important;
        }

        .input-wrapper {
            margin-bottom: 18px;
        }

        .form-control {
            padding-top: 12px;
            padding-bottom: 12px;
            min-height: 52px;
        }

        .btn-login {
            padding: 14px 16px;
        }

        .back-link {
            margin-top: 22px;
        }

        .footer-text {
            margin-top: 22px;
            padding-top: 16px;
        }

        .footer-text p {
            line-height: 1.5;
        }

        @media (max-width: 992px) {
            .right-section {
                padding: 24px 26px;
            }

            .login-container {
                max-width: 540px;
                padding-inline: 0;
            }
        }

        @media (max-width: 576px) {
            .right-section {
                padding: 18px 18px 24px;
            }

            .register-link {
                gap: 12px;
            }

            .btn-register,
            .google-login-btn {
                width: 100%;
                min-width: 0;
            }

            .footer-text {
                margin-top: 20px;
                padding-top: 14px;
            }
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
                        <a href="{{ route('wisatawan.password.request') }}" class="small text-decoration-none">Forgot password?</a>
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
                    <a href="{{ route('google.login') }}" class="google-login-btn">
                        <span class="google-login-badge">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path fill="#4285F4" d="M21.81 10.04H12v3.95h5.63c-.27 1.27-1.03 2.34-2.17 3.06v2.54h3.5c2.05-1.89 3.22-4.69 3.22-8.03 0-.53-.05-1.05-.14-1.52Z"/>
                                <path fill="#34A853" d="M12 22c2.91 0 5.35-.96 7.13-2.61l-3.5-2.54c-.97.65-2.2 1.04-3.63 1.04-2.79 0-5.16-1.88-6-4.4H2.39v2.62A10 10 0 0 0 12 22Z"/>
                                <path fill="#FBBC05" d="M6 13.49A5.98 5.98 0 0 1 5.66 12c0-.52.09-1.02.24-1.49V7.89H2.39A10 10 0 0 0 1.33 12c0 1.62.39 3.14 1.06 4.51L6 13.49Z"/>
                                <path fill="#EA4335" d="M12 6.11c1.58 0 2.99.54 4.11 1.61l3.08-3.08C17.34 2.91 14.91 2 12 2a10 10 0 0 0-9.61 5.89l3.51 2.62c.84-2.52 3.21-4.4 6.1-4.4Z"/>
                            </svg>
                        </span>
                        <span>Login with Google</span>
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
