<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Account - Kampung Telaga Air</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/logo.png') }}" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
    <style>
        body { min-height: 100vh; overflow: auto; }
        .login-wrapper { min-height: 100vh; height: auto; }
        .right-section { align-items: flex-start; overflow-y: auto; padding: 28px 40px; }
        .login-container { max-width: 560px; margin: 10px 0 20px; }
        .verification-code-input {
            text-align: center;
            font-size: 2rem;
            letter-spacing: 0.6rem;
            font-weight: 700;
            padding-left: 1.25rem;
        }
        .verification-helper {
            margin-top: -6px;
            margin-bottom: 18px;
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
        }
        .verification-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            justify-content: center;
            margin-top: 18px;
        }
        .verification-actions form { margin: 0; }
        .verification-status {
            margin-top: 12px;
            text-align: center;
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
        }
        .verification-status strong {
            color: #2a93cc;
        }
        .verification-expiry {
            margin-top: 8px;
            text-align: center;
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
        }
        .verification-expiry strong {
            color: #1f2937;
        }
        @media (max-width: 992px) { .right-section { padding: 24px 20px; } }
    </style>
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
                <h1>Verify your account</h1>
                <p>We have sent a 6-digit verification code to your email for a safer sign-in experience.</p>
            </div>
        </div>

        <div class="right-section">
            <div class="login-container">
                <div class="login-header">
                    <h2>Account Verification</h2>
                    <p>Enter the 6-digit code sent to <strong>{{ $maskedEmail }}</strong>.</p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning">{{ session('warning') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('wisatawan.verification.verify') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label">Verification Code</label>
                        <input
                            type="text"
                            name="code"
                            class="form-control verification-code-input @error('code') is-invalid @enderror"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="6"
                            placeholder="000000"
                            value="{{ old('code') }}"
                            required
                            autofocus
                        >
                    </div>

                    <p class="verification-helper">
                        The code expires in 15 minutes. If the email is not in your inbox, check spam or request a new code.
                    </p>

                    @if(!empty($expiresAt))
                        <div class="verification-expiry" id="verificationExpiry">
                            This code expires in <strong><span id="expiryCountdown">15:00</span></strong>.
                        </div>
                    @endif

                    <button type="submit" class="btn btn-login">
                        <i class="bi bi-shield-check me-2"></i>Verify Account
                    </button>
                </form>

                <div class="verification-actions">
                    <form action="{{ route('wisatawan.verification.resend') }}" method="POST">
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-outline-primary"
                            id="resendCodeButton"
                            {{ $cooldownRemaining > 0 ? 'disabled' : '' }}
                        >
                            <i class="bi bi-arrow-repeat me-2"></i>Resend Code
                        </button>
                    </form>

                    <form action="{{ route('wisatawan.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-light border">
                            <i class="bi bi-box-arrow-left me-2"></i>Sign Out
                        </button>
                    </form>
                </div>

                <div class="verification-status" id="verificationStatus">
                    @if($cooldownRemaining > 0)
                        You can request a new code in <strong><span id="resendCountdown">{{ $cooldownRemaining }}</span>s</strong>.
                    @else
                        You can request a new code now if you did not receive the email.
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const resendButton = document.getElementById('resendCodeButton');
            const countdownElement = document.getElementById('resendCountdown');
            const statusElement = document.getElementById('verificationStatus');
            const expiryElement = document.getElementById('verificationExpiry');
            const expiryCountdown = document.getElementById('expiryCountdown');
            let remaining = {{ (int) $cooldownRemaining }};
            const expiresAt = @json($expiresAt);

            if (!resendButton || !statusElement || remaining < 1) {
                if (!expiryElement || !expiryCountdown || !expiresAt) {
                    return;
                }
            }

            const render = function () {
                if (remaining > 0) {
                    resendButton.disabled = true;

                    if (countdownElement) {
                        countdownElement.textContent = String(remaining);
                    }

                    statusElement.innerHTML = 'You can request a new code in <strong><span id="resendCountdown">' + remaining + '</span>s</strong>.';
                    return;
                }

                resendButton.disabled = false;
                statusElement.textContent = 'You can request a new code now if you did not receive the email.';
            };

            const renderExpiry = function () {
                if (!expiryElement || !expiryCountdown || !expiresAt) {
                    return;
                }

                const target = new Date(expiresAt);
                const diffMs = target.getTime() - Date.now();

                if (Number.isNaN(target.getTime())) {
                    expiryElement.textContent = 'This verification code has an invalid expiry time.';
                    return;
                }

                if (diffMs <= 0) {
                    expiryElement.innerHTML = 'This code has <strong>expired</strong>. Please request a new one.';
                    return;
                }

                const totalSeconds = Math.floor(diffMs / 1000);
                const minutes = Math.floor(totalSeconds / 60);
                const seconds = totalSeconds % 60;
                const formatted = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

                expiryCountdown.textContent = formatted;
            };

            render();
            renderExpiry();

            const timer = window.setInterval(function () {
                remaining -= 1;
                render();
                renderExpiry();

                const expiryFinished = !expiresAt || (new Date(expiresAt).getTime() - Date.now()) <= 0;
                if (remaining <= 0 && expiryFinished) {
                    window.clearInterval(timer);
                }
            }, 1000);
        })();
    </script>
</body>
</html>
