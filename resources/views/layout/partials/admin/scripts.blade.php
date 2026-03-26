{{-- Base Scripts --}}
<script src="{{ asset('assets/libs/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/sidebarmenu.js') }}"></script>
<script src="{{ asset('assets/js/app.min.js') }}"></script>
<script src="{{ asset('assets/libs/simplebar/dist/simplebar.js') }}"></script>
<script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>

{{-- Conditional Scripts --}}
@if(request()->is('admin/dashboard*'))
  {{-- Scripts handled by Vite/dashboard.js --}}
@endif

@php
  $broadcastDriver = (string) config('broadcasting.default');
  $pusherConnection = config('broadcasting.connections.pusher', []);
  $pusherOptions = $pusherConnection['options'] ?? [];
@endphp

<script>
  window.adminRealtimeConfig = {
    enabled: @json($broadcastDriver === 'pusher' && !empty($pusherConnection['key'] ?? null)),
    key: @json((string) ($pusherConnection['key'] ?? '')),
    cluster: @json((string) ($pusherOptions['cluster'] ?? '')),
    host: @json((string) ($pusherOptions['host'] ?? '')),
    port: @json((int) ($pusherOptions['port'] ?? 443)),
    scheme: @json((string) ($pusherOptions['scheme'] ?? 'https')),
    authEndpoint: @json(url('/admin/broadcasting/auth')),
    csrfToken: @json(csrf_token()),
    feedUrl: @json(route('admin.notifications.feed')),
    markReadUrl: @json(route('admin.notifications.mark-read')),
    notificationsUrl: @json(route('admin.notifications.index')),
  };
</script>

<script>
  (function () {
    const mainWrapper = document.getElementById('main-wrapper');
    if (!mainWrapper) {
      return;
    }

    const menuLinks = document.querySelectorAll('.sidebar-nav .sidebar-link');
    const isModifiedClick = function (event) {
      return event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0;
    };

    menuLinks.forEach(function (link) {
      link.addEventListener('click', function (event) {
        if (isModifiedClick(event) || link.target === '_blank') {
          return;
        }

        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:')) {
          return;
        }

        const targetUrl = new URL(href, window.location.origin);
        if (targetUrl.origin !== window.location.origin || targetUrl.href === window.location.href) {
          return;
        }

        document.querySelectorAll('.sidebar-item.is-pending').forEach(function (item) {
          item.classList.remove('is-pending');
        });

        const menuItem = link.closest('.sidebar-item');
        if (menuItem) {
          menuItem.classList.add('is-pending');
        }

        mainWrapper.classList.add('is-leaving');
      });
    });

    window.addEventListener('pageshow', function () {
      mainWrapper.classList.remove('is-leaving');
      document.querySelectorAll('.sidebar-item.is-pending').forEach(function (item) {
        item.classList.remove('is-pending');
      });
    });
  })();
</script>

<script>
  (function () {
    const config = window.adminRealtimeConfig || {};
    const notifList = document.getElementById('adminNotifList');
    const notifCount = document.getElementById('adminNotifCount');
    const notifPing = document.getElementById('adminNotifPing');
    const notifToggle = document.getElementById('adminNotifToggle');
    const markReadButton = document.getElementById('adminNotifMarkRead');

    if (!notifList || !notifCount || !notifPing || !notifToggle || !markReadButton) {
      return;
    }

    const state = {
      unread: 0,
      items: [],
      maxItems: 10,
      markReadPending: false,
    };

    const escapeHtml = function (value) {
      return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    };

    const formatCurrency = function (amount, currency) {
      const numeric = Number(amount || 0);
      try {
        return new Intl.NumberFormat('en-MY', {
          style: 'currency',
          currency: currency || 'MYR',
          minimumFractionDigits: 2,
          maximumFractionDigits: 2,
        }).format(numeric);
      } catch (error) {
        return 'RM ' + numeric.toFixed(2);
      }
    };

    const formatDate = function (value) {
      if (!value) {
        return '-';
      }

      const date = new Date(value);
      if (Number.isNaN(date.getTime())) {
        return '-';
      }

      return date.toLocaleString('en-GB', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
      });
    };

    const updateUnreadBadge = function () {
      if (state.unread > 0) {
        notifCount.textContent = state.unread > 99 ? '99+' : String(state.unread);
        notifCount.style.display = 'inline-flex';
        notifPing.classList.add('is-visible');
        return;
      }

      notifCount.style.display = 'none';
      notifCount.textContent = '0';
      notifPing.classList.remove('is-visible');
    };

    const renderItems = function () {
      if (!state.items.length) {
        notifList.innerHTML = '<div class="admin-notif-empty">No recent notifications yet.</div>';
        return;
      }

      notifList.innerHTML = state.items.map(function (payload) {
        const packages = Array.isArray(payload.package_names) && payload.package_names.length
          ? payload.package_names.join(', ')
          : '-';

        const metaParts = [
          payload.order_id ? 'Order: ' + payload.order_id : '',
          'Package: ' + packages,
          'Total: ' + formatCurrency(payload.total_amount, payload.currency),
          'People: ' + (payload.total_people || 0),
          'Origin: ' + (payload.origin || 'Unknown'),
          payload.source_ip ? 'IP: ' + payload.source_ip : '',
          'Time: ' + formatDate(payload.created_at),
        ].filter(Boolean);

        const tagName = payload.action_url ? 'a' : 'div';
        const hrefAttr = payload.action_url ? ' href="' + escapeHtml(payload.action_url) + '"' : '';
        const stateClass = payload.is_read ? ' is-read' : ' is-unread';

        return [
          '<' + tagName + ' class="admin-notif-item' + stateClass + '"' + hrefAttr + '>',
          '  <h6 class="admin-notif-title">' + escapeHtml(payload.title || 'Notification') + '</h6>',
          '  <p class="admin-notif-text">' + escapeHtml(payload.message || '-') + '</p>',
          '  <div class="admin-notif-meta">' + metaParts.map(escapeHtml).join(' | ') + '</div>',
          '</' + tagName + '>',
        ].join('');
      }).join('');
    };

    const setItems = function (items, unreadCount) {
      state.items = Array.isArray(items) ? items.slice(0, state.maxItems) : [];
      state.unread = Number(unreadCount || 0);
      renderItems();
      updateUnreadBadge();
    };

    const pushNotification = function (payload, asUnread) {
      const normalized = Object.assign({ is_read: !asUnread }, payload || {});
      state.items = [normalized].concat(state.items.filter(function (item) {
        return item.notification_id !== normalized.notification_id;
      }));
      if (state.items.length > state.maxItems) {
        state.items = state.items.slice(0, state.maxItems);
      }

      if (asUnread) {
        state.unread += 1;
      }

      renderItems();
      updateUnreadBadge();
    };

    const fetchFeed = async function () {
      try {
        const response = await fetch(config.feedUrl, {
          headers: {
            'Accept': 'application/json'
          }
        });

        if (!response.ok) {
          throw new Error('Failed to load notification feed.');
        }

        const data = await response.json();
        setItems(data.items || [], data.unread_count || 0);
      } catch (error) {
        renderItems();
        updateUnreadBadge();
      }
    };

    const markAllAsRead = async function () {
      if (state.markReadPending || state.unread < 1) {
        state.items = state.items.map(function (item) {
          return Object.assign({}, item, { is_read: true });
        });
        state.unread = 0;
        renderItems();
        updateUnreadBadge();
        return;
      }

      state.markReadPending = true;
      try {
        const response = await fetch(config.markReadUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': config.csrfToken || '',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ scope: 'all' })
        });

        if (!response.ok) {
          throw new Error('Failed to mark notifications as read.');
        }

        state.items = state.items.map(function (item) {
          return Object.assign({}, item, { is_read: true });
        });
        state.unread = 0;
        renderItems();
        updateUnreadBadge();
      } catch (error) {
      } finally {
        state.markReadPending = false;
      }
    };

    markReadButton.addEventListener('click', function (event) {
      event.preventDefault();
      markAllAsRead();
    });

    notifToggle.addEventListener('click', function () {
      setTimeout(function () {
        const expanded = notifToggle.getAttribute('aria-expanded') === 'true';
        if (expanded) {
          markAllAsRead();
        }
      }, 50);
    });

    fetchFeed();

    if (!config.enabled || !config.key || typeof window.Pusher === 'undefined') {
      return;
    }

    const trimmedHost = String(config.host || '').trim();
    const isDefaultPusherHost = trimmedHost.startsWith('api-') && trimmedHost.includes('.pusher.com');
    const useCustomHost = trimmedHost !== '' && !isDefaultPusherHost;

    const pusherOptions = {
      cluster: config.cluster || undefined,
      authEndpoint: config.authEndpoint,
      auth: {
        headers: {
          'X-CSRF-TOKEN': config.csrfToken || '',
        },
      },
    };

    if (useCustomHost) {
      pusherOptions.wsHost = trimmedHost;
      pusherOptions.httpHost = trimmedHost;
      pusherOptions.wsPort = Number(config.port || 80);
      pusherOptions.wssPort = Number(config.port || 443);
      pusherOptions.forceTLS = String(config.scheme || 'https') === 'https';
      pusherOptions.enabledTransports = ['ws', 'wss'];
    }

    const pusher = new window.Pusher(config.key, pusherOptions);
    const channel = pusher.subscribe('private-admin.notifications');

    channel.bind('admin.realtime.notification', function (payload) {
      pushNotification(payload || {}, true);
    });

    window.addEventListener('beforeunload', function () {
      channel.unbind_all();
      pusher.disconnect();
    });
  })();
</script>

@yield('scripts')

@stack('scripts')
