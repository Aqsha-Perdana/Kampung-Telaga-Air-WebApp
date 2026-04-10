{{-- Base Scripts --}}
<script src="{{ asset('assets/libs/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/sidebarmenu.js') }}"></script>
<script src="{{ asset('assets/js/app.min.js') }}"></script>
<script src="{{ asset('assets/libs/simplebar/dist/simplebar.js') }}"></script>

{{-- Conditional Scripts --}}
@if(request()->is('admin/dashboard*'))
  {{-- Scripts handled by Vite/dashboard.js --}}
@endif

<x-admin.delete-swal />

@php
  $broadcastDriver = (string) config('broadcasting.default');
  $pusherConnection = config('broadcasting.connections.pusher', []);
  $pusherOptions = $pusherConnection['options'] ?? [];
  $notificationFeedPath = parse_url(route('admin.notifications.feed'), PHP_URL_PATH) ?: route('admin.notifications.feed');
  $notificationMarkReadPath = parse_url(route('admin.notifications.mark-read'), PHP_URL_PATH) ?: route('admin.notifications.mark-read');
  $notificationIndexPath = parse_url(route('admin.notifications.index'), PHP_URL_PATH) ?: route('admin.notifications.index');
@endphp

<script>
  window.adminRealtimeConfig = {
    enabled: @json($broadcastDriver === 'pusher' && !empty($pusherConnection['key'] ?? null)),
    key: @json((string) ($pusherConnection['key'] ?? '')),
    cluster: @json((string) ($pusherOptions['cluster'] ?? '')),
    host: @json((string) ($pusherOptions['host'] ?? '')),
    port: @json((int) ($pusherOptions['port'] ?? 443)),
    scheme: @json((string) ($pusherOptions['scheme'] ?? 'https')),
    authEndpoint: @json(parse_url(url('/admin/broadcasting/auth'), PHP_URL_PATH) ?: url('/admin/broadcasting/auth')),
    csrfToken: @json(csrf_token()),
    feedUrl: @json($notificationFeedPath),
    markReadUrl: @json($notificationMarkReadPath),
    notificationsUrl: @json($notificationIndexPath),
  };
</script>
<script src="{{ asset('assets/js/admin-layout.js') }}"></script>
<script src="{{ asset('assets/js/admin-ai-widget.js') }}"></script>

@yield('scripts')

@stack('scripts')
