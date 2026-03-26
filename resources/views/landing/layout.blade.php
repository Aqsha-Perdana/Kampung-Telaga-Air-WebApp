<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kampung Telaga Air</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/logo.png') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Preconnect to CDN domains -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://unpkg.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>

    <!-- Bootstrap 5 (critical) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- AOS Animation (deferred) -->
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css" media="print" onload="this.media='all'">
    <!-- Swiper Slider (deferred) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/landing-layout.css') }}">
</head>
<body class="visitor-app">
    @include('landing.partials.navbar')
    @include('landing.partials.mobile-quick-links')

    @yield('content')

    @include('landing.partials.mobile-bottom-nav')
    @include('landing.partials.footer')
    @include('landing.partials.layout-scripts')

    @stack('scripts')
    @include('components.chatbot-widget')
</body>
</html>
