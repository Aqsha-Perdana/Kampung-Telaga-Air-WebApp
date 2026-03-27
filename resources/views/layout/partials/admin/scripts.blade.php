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
    const footerText = document.getElementById('adminNotifFooterText');

    if (!notifList || !notifCount || !notifPing || !notifToggle || !markReadButton || !footerText) {
      return;
    }

    const state = {
      unread: 0,
      items: [],
      maxItems: 10,
      markReadPending: false,
    };

    const typeMeta = {
      new_order: { label: 'New Booking', icon: 'shopping-cart', className: 'bg-primary' },
      payment_paid: { label: 'Payment Confirmed', icon: 'credit-card', className: 'bg-success' },
      refund_requested: { label: 'Refund Requested', icon: 'arrow-back-up', className: 'bg-warning text-dark' },
      refund_processed: { label: 'Refund Processed', icon: 'cash-banknote', className: 'bg-dark' },
      cart_added: { label: 'Cart Activity', icon: 'shopping-cart-plus', className: 'bg-info text-dark' },
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

    const formatRelativeTime = function (value) {
      if (!value) {
        return 'Unknown time';
      }

      const date = new Date(value);
      if (Number.isNaN(date.getTime())) {
        return 'Unknown time';
      }

      const diffMs = date.getTime() - Date.now();
      const diffSeconds = Math.round(diffMs / 1000);
      const absSeconds = Math.abs(diffSeconds);

      if (absSeconds < 60) {
        return diffSeconds >= 0 ? 'In a few seconds' : 'Just now';
      }

      const thresholds = [
        { unit: 'minute', seconds: 60 },
        { unit: 'hour', seconds: 3600 },
        { unit: 'day', seconds: 86400 },
      ];

      for (const threshold of thresholds) {
        const valueInUnit = diffSeconds / threshold.seconds;
        if (Math.abs(valueInUnit) < (threshold.unit === 'minute' ? 60 : 24)) {
          return new Intl.RelativeTimeFormat('en', { numeric: 'auto' })
            .format(Math.round(valueInUnit), threshold.unit);
        }
      }

      return formatDate(value);
    };

    const badgeClass = function (className) {
      return String(className || '')
        .split(' ')
        .filter(Boolean)
        .map(function (token) {
          return token.startsWith('bg-') ? token : '';
        })
        .filter(Boolean)
        .join(' ') || 'bg-secondary';
    };

    const normalizePayload = function (payload, asUnread) {
      return Object.assign({
        notification_id: null,
        type: 'new_order',
        title: 'Notification',
        message: '',
        package_names: [],
        total_amount: 0,
        currency: 'MYR',
        total_people: 0,
        origin: '',
        source_ip: '',
        created_at: null,
        action_url: config.notificationsUrl || '#',
        order_detail_url: null,
        is_read: !asUnread,
        read_at: null,
      }, payload || {}, {
        is_read: typeof (payload || {}).is_read === 'boolean' ? payload.is_read : !asUnread,
      });
    };

    const updateUnreadBadge = function () {
      if (state.unread > 0) {
        notifCount.textContent = state.unread > 99 ? '99+' : String(state.unread);
        notifCount.style.display = 'inline-flex';
        notifPing.classList.add('is-visible');
      } else {
        notifCount.style.display = 'none';
        notifCount.textContent = '0';
        notifPing.classList.remove('is-visible');
      }

      footerText.textContent = state.unread > 0
        ? state.unread + ' unread · latest ' + state.maxItems + ' items'
        : 'All caught up · latest ' + state.maxItems + ' items';

      markReadButton.disabled = state.markReadPending || state.unread < 1;
    };

    const renderItems = function () {
      if (!state.items.length) {
        notifList.innerHTML = [
          '<div class="admin-notif-empty">',
          '  <strong>No notifications yet</strong>',
          '  <span>New admin activity will appear here automatically.</span>',
          '</div>',
        ].join('');
        return;
      }

      notifList.innerHTML = state.items.map(function (payload) {
        const item = normalizePayload(payload, !payload.is_read);
        const meta = typeMeta[item.type] || { label: 'Notification', icon: 'bell', className: 'bg-secondary' };
        const packages = Array.isArray(payload.package_names) && payload.package_names.length
          ? payload.package_names.join(', ')
          : 'No package data';
        const facts = [
          item.order_id ? ['Order', item.order_id] : null,
          item.customer_name ? ['Customer', item.customer_name] : null,
          Number(item.total_amount || 0) > 0 ? ['Amount', formatCurrency(item.total_amount, item.currency)] : null,
          Number(item.total_people || 0) > 0 ? ['Participants', String(item.total_people)] : null,
          packages ? ['Package', packages] : null,
          item.origin ? ['Source', item.origin] : null,
        ].filter(Boolean);

        const stateClass = item.is_read ? ' is-read' : ' is-unread';
        const href = item.order_detail_url || item.action_url || '';
        const tagName = href ? 'a' : 'div';
        const hrefAttr = href ? ' href="' + escapeHtml(href) + '"' : '';

        return [
          '<' + tagName + ' class="admin-notif-item' + stateClass + '"' + hrefAttr + '>',
          '  <div class="admin-notif-icon ' + escapeHtml(meta.className) + '">',
          '    <i class="ti ti-' + escapeHtml(meta.icon) + '"></i>',
          '  </div>',
          '  <div class="admin-notif-content">',
          '    <div class="admin-notif-item-head">',
          '      <div>',
          '        <div class="admin-notif-title">' + escapeHtml(item.title || meta.label) + '</div>',
          '        <div class="admin-notif-time" title="' + escapeHtml(formatDate(item.created_at)) + '">' + escapeHtml(formatRelativeTime(item.created_at)) + '</div>',
          '      </div>',
          '      <span class="admin-notif-status ' + (item.is_read ? 'is-read' : 'is-unread') + '">' + (item.is_read ? 'Read' : 'Unread') + '</span>',
          '    </div>',
          '    <p class="admin-notif-text">' + escapeHtml(item.message || 'No additional details provided.') + '</p>',
          '    <div class="admin-notif-facts">' + facts.map(function (fact) {
                return '<span><strong>' + escapeHtml(fact[0]) + ':</strong> ' + escapeHtml(fact[1]) + '</span>';
              }).join('') + '</div>',
          '    <div class="d-flex align-items-center justify-content-between gap-2 mt-2">',
          '      <span class="badge rounded-pill ' + escapeHtml(badgeClass(meta.className)) + '">' + escapeHtml(meta.label) + '</span>',
          (item.order_detail_url
            ? '      <span class="small text-primary fw-semibold">Open order <i class="ti ti-arrow-up-right ms-1"></i></span>'
            : '      <span class="small text-muted">Open notification details</span>'),
          '    </div>',
          '  </div>',
          '</' + tagName + '>',
        ].join('');
      }).join('');
    };

    const setItems = function (items, unreadCount) {
      state.items = Array.isArray(items)
        ? items.slice(0, state.maxItems).map(function (item) {
            return normalizePayload(item, !item.is_read);
          })
        : [];
      state.unread = Number(unreadCount || 0);
      renderItems();
      updateUnreadBadge();
    };

    const pushNotification = function (payload, asUnread) {
      const normalized = normalizePayload(payload, asUnread);
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
        state.items = [];
        state.unread = 0;
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
      updateUnreadBadge();
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
        updateUnreadBadge();
      }
    };

    markReadButton.addEventListener('click', function (event) {
      event.preventDefault();
      markAllAsRead();
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

@include('layout.partials.admin.ai-assistant-scripts')

@yield('scripts')

@stack('scripts')
