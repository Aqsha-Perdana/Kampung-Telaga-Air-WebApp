<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tourist Registration - Kampung Telaga Air</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/logo.png') }}" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('assets/css/register.css') }}">
</head>
<body>
    <div class="register-wrapper">
        <div class="left-section">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            
            <div class="welcome-content">
                <div class="logo-section">
                    <img src="{{ asset('assets/images/logos/primary-logo.png') }}" alt="Kampung Telaga Air Logo">
                </div>
                
                <h1>Welcome, Visitor</h1>
                <p>Kampung Telaga Air</p>
            </div>
        </div>

        <div class="right-section">
            <div class="register-container">
                <div class="register-header">
                    <h2>Create Tourist Account</h2>
                    <p>Please register to continue your journey</p>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('wisatawan.register.post') }}" method="POST" id="registerForm">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <div class="input-wrapper">
                            <i class="bi bi-person-fill input-icon"></i>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   name="name" 
                                   placeholder="Enter your full name"
                                   value="{{ old('name') }}"
                                   required 
                                   autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <div class="input-wrapper">
                            <i class="bi bi-envelope-fill input-icon"></i>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   name="email" 
                                   placeholder="name@example.com"
                                   value="{{ old('email') }}"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <div class="input-wrapper">
                            <i class="bi bi-phone-fill input-icon"></i>
                            <input type="tel" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   name="phone" 
                                   id="phone"
                                   placeholder="08123456789"
                                   value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nationality</label>
                        <div class="input-wrapper">
                            <i class="bi bi-flag-fill input-icon"></i>
                            <select class="form-select @error('nationality') is-invalid @enderror" 
                                    name="nationality" 
                                    required>
                                <option value="">Select Country</option>
                                <optgroup label="Southeast Asia">
                                    <option value="Malaysia" {{ old('nationality') == 'Malaysia' ? 'selected' : '' }}>Malaysia</option>
                                    <option value="Indonesia" {{ old('nationality') == 'Indonesia' ? 'selected' : '' }}>Indonesia</option>
                                    <option value="Singapore" {{ old('nationality') == 'Singapore' ? 'selected' : '' }}>Singapore</option>
                                    <option value="Thailand" {{ old('nationality') == 'Thailand' ? 'selected' : '' }}>Thailand</option>
                                    <option value="Philippines" {{ old('nationality') == 'Philippines' ? 'selected' : '' }}>Philippines</option>
                                    <option value="Vietnam" {{ old('nationality') == 'Vietnam' ? 'selected' : '' }}>Vietnam</option>
                                    <option value="Brunei" {{ old('nationality') == 'Brunei' ? 'selected' : '' }}>Brunei Darussalam</option>
                                    <option value="Myanmar" {{ old('nationality') == 'Myanmar' ? 'selected' : '' }}>Myanmar</option>
                                    <option value="Cambodia" {{ old('nationality') == 'Cambodia' ? 'selected' : '' }}>Cambodia</option>
                                    <option value="Laos" {{ old('nationality') == 'Laos' ? 'selected' : '' }}>Laos</option>
                                </optgroup>
                                <optgroup label="East Asia">
                                    <option value="China" {{ old('nationality') == 'China' ? 'selected' : '' }}>China</option>
                                    <option value="Japan" {{ old('nationality') == 'Japan' ? 'selected' : '' }}>Japan</option>
                                    <option value="South Korea" {{ old('nationality') == 'South Korea' ? 'selected' : '' }}>South Korea</option>
                                    <option value="Hong Kong" {{ old('nationality') == 'Hong Kong' ? 'selected' : '' }}>Hong Kong</option>
                                    <option value="Taiwan" {{ old('nationality') == 'Taiwan' ? 'selected' : '' }}>Taiwan</option>
                                </optgroup>
                                <optgroup label="South Asia">
                                    <option value="India" {{ old('nationality') == 'India' ? 'selected' : '' }}>India</option>
                                    <option value="Pakistan" {{ old('nationality') == 'Pakistan' ? 'selected' : '' }}>Pakistan</option>
                                    <option value="Bangladesh" {{ old('nationality') == 'Bangladesh' ? 'selected' : '' }}>Bangladesh</option>
                                    <option value="Sri Lanka" {{ old('nationality') == 'Sri Lanka' ? 'selected' : '' }}>Sri Lanka</option>
                                </optgroup>
                                <optgroup label="Middle East">
                                    <option value="Saudi Arabia" {{ old('nationality') == 'Saudi Arabia' ? 'selected' : '' }}>Saudi Arabia</option>
                                    <option value="United Arab Emirates" {{ old('nationality') == 'United Arab Emirates' ? 'selected' : '' }}>United Arab Emirates</option>
                                    <option value="Qatar" {{ old('nationality') == 'Qatar' ? 'selected' : '' }}>Qatar</option>
                                    <option value="Kuwait" {{ old('nationality') == 'Kuwait' ? 'selected' : '' }}>Kuwait</option>
                                </optgroup>
                                <optgroup label="Europe">
                                    <option value="United Kingdom" {{ old('nationality') == 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                                    <option value="France" {{ old('nationality') == 'France' ? 'selected' : '' }}>France</option>
                                    <option value="Germany" {{ old('nationality') == 'Germany' ? 'selected' : '' }}>Germany</option>
                                    <option value="Netherlands" {{ old('nationality') == 'Netherlands' ? 'selected' : '' }}>Netherlands</option>
                                    <option value="Spain" {{ old('nationality') == 'Spain' ? 'selected' : '' }}>Spain</option>
                                    <option value="Italy" {{ old('nationality') == 'Italy' ? 'selected' : '' }}>Italy</option>
                                </optgroup>
                                <optgroup label="Americas & Oceania">
                                    <option value="United States" {{ old('nationality') == 'United States' ? 'selected' : '' }}>United States</option>
                                    <option value="Canada" {{ old('nationality') == 'Canada' ? 'selected' : '' }}>Canada</option>
                                    <option value="Australia" {{ old('nationality') == 'Australia' ? 'selected' : '' }}>Australia</option>
                                    <option value="New Zealand" {{ old('nationality') == 'New Zealand' ? 'selected' : '' }}>New Zealand</option>
                                </optgroup>
                                <optgroup label="Other">
                                    <option value="Other" {{ old('nationality') == 'Other' ? 'selected' : '' }}>Other</option>
                                </optgroup>
                            </select>
                            @error('nationality')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <div class="input-wrapper">
                            <i class="bi bi-geo-alt-fill input-icon"></i>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      name="address" 
                                      placeholder="Enter your full address"
                                      rows="3">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-wrapper">
                            <i class="bi bi-lock-fill input-icon"></i>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   name="password" 
                                   id="password"
                                   placeholder="Minimum 8 characters"
                                   required>
                            <i class="bi bi-eye password-toggle" id="togglePassword"></i>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="password-strength" id="passwordStrength" style="display: none;">
                            <div class="strength-meter">
                                <div class="strength-meter-fill" id="strengthMeterFill"></div>
                            </div>
                            <div class="strength-text" id="strengthText"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-wrapper">
                            <i class="bi bi-lock-fill input-icon"></i>
                            <input type="password" 
                                   class="form-control @error('password_confirmation') is-invalid @enderror" 
                                   name="password_confirmation" 
                                   id="password_confirmation"
                                   placeholder="Repeat your password"
                                   required>
                            <i class="bi bi-eye password-toggle" id="togglePasswordConfirm"></i>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn-register">
                        <i class="bi bi-person-plus"></i>
                        Register Now
                    </button>
                </form>

                <div class="login-link">
                    <p>Already have an account? <a href="{{ route('wisatawan.login') }}">Login Here</a></p>
                </div>

                <div class="back-link">
                    <a href="{{ url('/') }}">
                        <i class="bi bi-arrow-left"></i>
                        Back to Home
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
        const password = document.getElementById('password');
        const passwordConfirm = document.getElementById('password_confirmation');

        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });

        togglePasswordConfirm.addEventListener('click', function() {
            const type = passwordConfirm.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordConfirm.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });

        // Password Strength Meter
        const passwordStrength = document.getElementById('passwordStrength');
        const strengthMeterFill = document.getElementById('strengthMeterFill');
        const strengthText = document.getElementById('strengthText');

        password.addEventListener('input', function() {
            const pwd = this.value;
            
            if (pwd.length === 0) {
                passwordStrength.style.display = 'none';
                return;
            }

            passwordStrength.style.display = 'block';
            
            let strength = 0;
            let text = '';
            let color = '';

            // Check password strength
            if (pwd.length >= 8) strength++;
            if (pwd.match(/[a-z]/) && pwd.match(/[A-Z]/)) strength++;
            if (pwd.match(/[0-9]/)) strength++;
            if (pwd.match(/[^a-zA-Z0-9]/)) strength++;

            switch(strength) {
                case 0:
                case 1:
                    text = 'Weak';
                    color = '#dc3545';
                    break;
                case 2:
                    text = 'Medium';
                    color = '#ffc107';
                    break;
                case 3:
                    text = 'Strong';
                    color = '#20c997';
                    break;
                case 4:
                    text = 'Very Strong';
                    color = '#198754';
                    break;
            }

            strengthMeterFill.style.width = (strength * 25) + '%';
            strengthMeterFill.style.backgroundColor = color;
            strengthText.textContent = 'Password Strength: ' + text;
            strengthText.style.color = color;
        });

        // Form submission animation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const btn = this.querySelector('.btn-register');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            btn.disabled = true;
        });

        // Auto dismiss alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        // Phone number formatting
        const phoneInput = document.getElementById('phone');
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.startsWith('0')) {
                e.target.value = value;
            }
        });

        
    </script>
</body>
</html>