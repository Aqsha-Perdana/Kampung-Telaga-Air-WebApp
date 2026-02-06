<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kampung Telaga Air</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/logo.png') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Swiper Slider -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
    
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a href="{{ url('/') }}" class="text-nowrap logo-img">
            <img src="{{ asset('assets/images/logos/primary-logo.png') }}" width="100" alt="" />
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}#home">
                           </i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('landing.paket-wisata') }}">
                            </i> Tour Package
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('landing.culinary') }}">
                            Culinary
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('landing.kiosk') }}">
                            Kiosk
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('landing.homestay') }}">
                             Homestay
                        </a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="#homestay">
                             Fishermen's Catch
                        </a>
                    </li> -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                           </i> More Info
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="#galeri">
                                    </i> About Us
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#testimoni">
                                    </i> Contact Us
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#testimoni">
                                    </i> Teams
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
            <a href="{{ route('cart.index') }}" class="btn btn-outline-primary me-2 position-relative">
                <i class="bi bi-cart3"></i>

                @if($cartCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ $cartCount }}
                    </span>
                @endif
            </a>
                @guest
        <!-- Login Button -->
        <a href="{{ route('wisatawan.login') }}" class="btn-auth-login">
            <i class="bi bi-box-arrow-in-right"></i>
        </a>
    @else
        <!-- User Profile Dropdown -->
        <div class="dropdown">

    <!-- Trigger -->
    <button
        class="btn-user-profile dropdown-toggle d-flex align-items-center gap-2"
        type="button"
        data-bs-toggle="dropdown"
        aria-expanded="false"
    >
        <!-- Avatar -->
        <div class="user-avatar flex-shrink-0">
            @if(Auth::user()->profile_photo)
                <img
                    src="{{ Auth::user()->profile_photo }}"
                    alt="Profile Photo"
                    class="w-100 h-100 rounded-circle object-fit-cover"
                >
            @else
                <span class="avatar-initial">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </span>
            @endif
        </div>

        <!-- User Info -->
        <div class="user-info text-start d-none d-md-block">
            <div class="user-name fw-semibold">
                {{ Auth::user()->name }}
            </div>
            <div class="user-status text-muted small">
                Traveler
            </div>
        </div>
    </button>

    <!-- Dropdown Menu -->
    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-custom shadow-lg">
    <li class="dropdown-header-custom">
        <div class="d-flex align-items-center px-3 py-3">
            <div class="flex-grow-1">
                <p class="header-label mb-0">ACCOUNT</p>
                <h6 class="user-name-header mb-0">{{ Auth::user()->name }}</h6>
                <p class="user-email-header mb-0">{{ Auth::user()->email }}</p>
            </div>
        </div>
    </li>

    <li><hr class="dropdown-divider"></li>

    <li class="px-2">
        <a class="dropdown-item-custom d-flex align-items-center gap-3" href="{{ route('orders.history') }}">
            <div class="icon-wrapper bg-blue-soft">
                <i class="bi bi-bag-heart-fill"></i>
            </div>
            <div class="menu-text">
                <span class="d-block fw-semibold">Order History</span>
                <small class="text-muted">Check your bookings</small>
            </div>
        </a>
    </li>

    <li class="px-2 mt-1 mb-2">
    <button type="button" onclick="confirmLogout()" class="dropdown-item-custom dropdown-item-logout d-flex align-items-center gap-3 w-100 border-0">
        <div class="icon-wrapper bg-red-soft">
            <i class="bi bi-power"></i>
        </div>
        <div class="menu-text text-start">
            <span class="d-block fw-semibold">Log Out</span>
        </div>
    </button>

    <form id="logout-form-action" action="{{ route('wisatawan.logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</li>
</ul>
</div>
    @endguest
            </div>
        </div>
    </div>
</nav>
    @yield('content')

    <!-- Footer -->
    <footer class="site-footer">
  <div class="footer-container">
    <div class="footer-top">
      <!-- Left -->
      <div class="footer-left">
        <img src="{{ asset('assets/images/logos/primary-logo.png') }}" alt="Visit Telaga Air" class="footer-logo">
        <p>
          This website is the result of a collaborative effort between i-CATS
          University College, Malaysia, and Telkom University, Indonesia, in
          partnership with the community of Kampung Telaga Air. The initiative
          was developed as part of an international academic collaboration and
          community engagement program, with the shared goal of promoting the
          unique culture, heritage, and livelihood of the village.
        </p>

        <div class="footer-contact">
          <p><strong>E.</strong> zainitanggek1612@gmail.com</p>
          <p><strong>P.</strong> (+60) 17-854 3178</p>
        </div>
      </div>

      <!-- Right -->
      <div class="footer-right">
        <div class="footer-column">
          <h4>Interest</h4>
          <ul>
            <li><a href="{{ route('landing.paket-wisata') }}">Tour Package</a></li>
            <li><a href="#">Homestay</a></li>
            <li><a href="#">Culinary</a></li>
            <li><a href="#">Kiosk</a></li>
            <!-- <li><a href="#">Fisherman's Catch</a></li> -->
          </ul>
        </div>

        <div class="footer-column">
          <h4>More Info</h4>
          <ul>
            <li><a href="#">About us</a></li>
            <li><a href="#">Contact Us</a></li>
            <li><a href="#">Teams</a></li>
          </ul>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <p>
        © 2026 Kampung Telaga Air. All rights reserved.
      </p>
    </div>
  </div>
</footer>


    <!-- Floating WhatsApp -->
    <a href="https://wa.me/6281234567890" class="whatsapp-float" target="_blank">
        <i class="bi bi-whatsapp"></i>
    </a>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-custom');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Konfigurasi Toast (notifikasi kecil di pojok)
    // Konfigurasi dasar notifikasi (Toast)
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    // Menampilkan Notifikasi Sukses dari Controller
    @if(session('success'))
        Toast.fire({
            icon: 'success',
            title: "{{ session('success') }}"
        });
    @endif

    // Menampilkan Notifikasi Error dari Controller
    @if(session('error'))
        Toast.fire({
            icon: 'error',
            title: "{{ session('error') }}"
        });
    @endif

    function confirmLogout() {
        Swal.fire({
            title: 'Log out?',
            text: "You will exit the Kampung Telaga Air visit session.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Exit',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Menjalankan form logout jika user klik "Ya"
                document.getElementById('logout-form-action').submit();
            }
        });
    }
    </script>

    @stack('scripts')
    @include('components.chatbot-widget')
</body>
</html>