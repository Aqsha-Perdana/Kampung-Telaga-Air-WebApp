<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom fixed-top">
    <div class="container">
        <a href="{{ url('/') }}" class="text-nowrap logo-img">
            <img src="{{ asset('assets/images/logos/primary-logo.png') }}" width="100" alt="" />
        </a>
        <button class="navbar-toggler d-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a href="{{ Auth::check() ? route('orders.history') : route('wisatawan.login') }}" class="mobile-notif-trigger d-md-none" aria-label="Notifications">
            <i class="bi bi-bell"></i>
            <span class="visually-hidden">Notifications</span>
        </a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}#home">
                        Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('landing.paket-wisata') }}">
                        Tour Package
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
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        More Info
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="#galeri">
                                About Us
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#testimoni">
                                Contact Us
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#testimoni">
                                Teams
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>

            <div class="d-flex align-items-center nav-actions">
                <a href="{{ route('cart.index') }}" class="btn btn-outline-primary me-2 position-relative">
                    <i class="bi bi-cart3"></i>

                    @if($cartCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>

                @guest
                    <a href="{{ route('wisatawan.login') }}" class="btn-auth-login">
                        <i class="bi bi-box-arrow-in-right"></i>
                    </a>
                @else
                    <div class="dropdown account-dropdown">
                        <button
                            id="accountMenuButton"
                            class="btn-user-profile dropdown-toggle d-flex align-items-center gap-2"
                            type="button"
                            data-bs-toggle="dropdown"
                            data-bs-auto-close="outside"
                            data-bs-offset="0,10"
                            aria-expanded="false"
                            aria-label="Open account menu"
                        >
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

                            <div class="user-info text-start d-none d-md-block">
                                <div class="user-name fw-semibold">
                                    {{ Auth::user()->name }}
                                </div>
                                <div class="user-status text-muted small">
                                    Traveler
                                </div>
                            </div>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-custom shadow-lg" aria-labelledby="accountMenuButton">
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
                                <a class="dropdown-item-custom d-flex align-items-center gap-3" href="{{ route('wisatawan.profile') }}">
                                    <div class="icon-wrapper bg-blue-soft">
                                        <i class="bi bi-person-badge-fill"></i>
                                    </div>
                                    <div class="menu-text">
                                        <span class="d-block fw-semibold">Profile</span>
                                        <small class="text-muted">Edit your account details</small>
                                    </div>
                                </a>
                            </li>

                            <li class="px-2 mt-1">
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
