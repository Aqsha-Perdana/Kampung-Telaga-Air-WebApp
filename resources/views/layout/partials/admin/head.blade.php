<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Admin Page') - Kampung Telaga Air</title>
  <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/logo.png') }}" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <link rel="stylesheet" href="{{ asset('assets/css/styles.min.css') }}" />
  @include('layout.partials.admin.styles')
  @yield('styles')
</head>
