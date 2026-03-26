@php
    $mobileNavIndex = 0;
    if (request()->routeIs('landing.paket-wisata')) {
        $mobileNavIndex = 1;
    } elseif (request()->routeIs('cart.*')) {
        $mobileNavIndex = 2;
    } elseif (request()->routeIs('orders.*')) {
        $mobileNavIndex = 3;
    } elseif (request()->routeIs('wisatawan.*')) {
        $mobileNavIndex = 4;
    }
@endphp
<nav class="mobile-bottom-nav d-md-none" aria-label="Mobile Navigation" style="--active-index: {{ $mobileNavIndex }};">
    <span class="mobile-bottom-nav__glider" aria-hidden="true"></span>
    <a href="{{ route('home') }}" class="mobile-bottom-nav__item {{ request()->routeIs('home') ? 'is-active' : '' }}">
        <i class="bi bi-house-door"></i>
        <span>Home</span>
    </a>
    <a href="{{ route('landing.paket-wisata') }}" class="mobile-bottom-nav__item {{ request()->routeIs('landing.paket-wisata') ? 'is-active' : '' }}">
        <i class="bi bi-compass"></i>
        <span>Package</span>
    </a>
    <a href="{{ route('cart.index') }}" class="mobile-bottom-nav__item {{ request()->routeIs('cart.*') ? 'is-active' : '' }}">
        <span class="position-relative d-inline-flex align-items-center justify-content-center">
            <i class="bi bi-cart3"></i>
            @if(($cartCount ?? 0) > 0)
                <small class="mobile-nav-badge">{{ $cartCount > 9 ? '9+' : $cartCount }}</small>
            @endif
        </span>
        <span>Cart</span>
    </a>
    <a href="{{ Auth::check() ? route('orders.history') : route('wisatawan.login') }}" class="mobile-bottom-nav__item {{ request()->routeIs('orders.*') ? 'is-active' : '' }}">
        <i class="bi bi-receipt-cutoff"></i>
        <span>Orders</span>
    </a>
    <a href="{{ Auth::check() ? route('wisatawan.profile') : route('wisatawan.login') }}" class="mobile-bottom-nav__item {{ request()->routeIs('wisatawan.*') ? 'is-active' : '' }}">
        <i class="bi bi-person"></i>
        <span>Profile</span>
    </a>
</nav>
