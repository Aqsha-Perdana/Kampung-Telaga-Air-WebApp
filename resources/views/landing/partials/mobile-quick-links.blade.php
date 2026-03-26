@if(request()->routeIs('home'))
<nav class="mobile-quick-links d-md-none" aria-label="Mobile Quick Links">
    <a href="{{ route('landing.paket-wisata') }}"><i class="bi bi-compass"></i> Package</a>
    <a href="{{ route('landing.culinary') }}"><i class="bi bi-cup-hot"></i> Culinary</a>
    <a href="{{ route('landing.kiosk') }}"><i class="bi bi-shop"></i> Kiosk</a>
    <a href="{{ route('landing.homestay') }}"><i class="bi bi-house-heart"></i> Homestay</a>
    <a href="#site-footer-info"><i class="bi bi-info-circle"></i> More Info</a>
</nav>
@endif
