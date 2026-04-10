<!-- Scripts -->
<script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script defer src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
window.landingLayoutConfig = {
    success: @json(session('success')),
    error: @json(session('error')),
    logoutTitle: 'Log out?',
    logoutText: 'You will exit the Kampung Telaga Air visit session.',
    logoutConfirm: 'Yes, Exit',
    logoutCancel: 'Cancel'
};
</script>
<script defer src="{{ asset('assets/js/landing-layout.js') }}"></script>
