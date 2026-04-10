(function () {
  const mainWrapper = document.getElementById('main-wrapper');
  if (!mainWrapper) {
    return;
  }

  const menuLinks = document.querySelectorAll('.sidebar-nav .sidebar-link');
  const navigableLinks = document.querySelectorAll('.sidebar-nav .sidebar-link, .app-header a[href], .message-body a[href]');
  const warmedUrls = new Set();
  const warmingUrls = new Set();
  const hoverTimers = new WeakMap();

  const isModifiedClick = function (event) {
    return event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0;
  };

  const getLink = function (event) {
    if (!event.target || !event.target.closest) {
      return null;
    }

    return event.target.closest('a[href]');
  };

  const canPrefetch = function (link) {
    if (!link || link.classList.contains('no-prefetch')) {
      return false;
    }

    if (link.target === '_blank' || link.hasAttribute('download')) {
      return false;
    }

    const href = link.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('javascript:')) {
      return false;
    }

    try {
      const url = new URL(href, window.location.origin);
      return url.origin === window.location.origin && url.href !== window.location.href;
    } catch (error) {
      return false;
    }
  };

  const prefetchUrl = function (href) {
    const url = new URL(href, window.location.origin).toString();
    if (warmedUrls.has(url) || warmingUrls.has(url)) {
      return;
    }

    warmingUrls.add(url);
    const hint = document.createElement('link');
    hint.rel = 'prefetch';
    hint.href = url;
    hint.as = 'document';
    document.head.appendChild(hint);

    fetch(url, {
      method: 'GET',
      credentials: 'same-origin',
      headers: { 'X-Admin-Nav-Warm': '1' }
    }).catch(function () {
    }).finally(function () {
      warmingUrls.delete(url);
      warmedUrls.add(url);
    });
  };

  const prefetchOnHover = function (event) {
    const link = getLink(event);
    if (!canPrefetch(link)) {
      return;
    }

    if (event.type === 'mouseout') {
      if (event.relatedTarget && link.contains(event.relatedTarget)) {
        return;
      }

      const existingTimer = hoverTimers.get(link);
      if (existingTimer) {
        clearTimeout(existingTimer);
        hoverTimers.delete(link);
      }
      return;
    }

    if (hoverTimers.has(link)) {
      return;
    }

    const timer = setTimeout(function () {
      prefetchUrl(link.getAttribute('href'));
      hoverTimers.delete(link);
    }, 60);

    hoverTimers.set(link, timer);
  };

  const prefetchOnIntent = function (event) {
    const link = getLink(event);
    if (!canPrefetch(link)) {
      return;
    }

    prefetchUrl(link.getAttribute('href'));
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

  document.addEventListener('mouseover', prefetchOnHover, { passive: true });
  document.addEventListener('mouseout', prefetchOnHover, { passive: true });
  document.addEventListener('pointerdown', prefetchOnIntent, { passive: true });
  document.addEventListener('focusin', prefetchOnIntent, { passive: true });

  const warmPrimaryAdminLinks = function () {
    Array.from(navigableLinks)
      .filter(canPrefetch)
      .slice(0, 8)
      .forEach(function (link, index) {
        setTimeout(function () {
          prefetchUrl(link.getAttribute('href'));
        }, 120 * index);
      });
  };

  if ('requestIdleCallback' in window) {
    window.requestIdleCallback(warmPrimaryAdminLinks, { timeout: 1200 });
  } else {
    setTimeout(warmPrimaryAdminLinks, 500);
  }

  window.addEventListener('pageshow', function () {
    mainWrapper.classList.remove('is-leaving');
    document.querySelectorAll('.sidebar-item.is-pending').forEach(function (item) {
      item.classList.remove('is-pending');
    });
  });
})();

(function () {
  const sidebarNav = document.querySelector('.sidebar-nav');
  if (!sidebarNav) {
    return;
  }

  const storageKey = 'admin.sidebar.scrollTop';

  const getScrollElement = function () {
    return sidebarNav.querySelector('.simplebar-content-wrapper') || sidebarNav;
  };

  const saveScrollPosition = function () {
    const scroller = getScrollElement();
    sessionStorage.setItem(storageKey, String(scroller.scrollTop || 0));
  };

  const restoreScrollPosition = function () {
    const scroller = getScrollElement();
    const savedScrollTop = sessionStorage.getItem(storageKey);
    const activeItem = sidebarNav.querySelector('.sidebar-item.active');

    if (savedScrollTop !== null) {
      scroller.scrollTop = parseInt(savedScrollTop, 10) || 0;
    } else if (activeItem) {
      activeItem.scrollIntoView({ block: 'center', behavior: 'auto' });
    }
  };

  const bindScroller = function () {
    const scroller = getScrollElement();
    scroller.addEventListener('scroll', saveScrollPosition, { passive: true });
    window.addEventListener('beforeunload', saveScrollPosition, { once: true });
    window.addEventListener('pagehide', saveScrollPosition, { once: true });
    setTimeout(restoreScrollPosition, 0);
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindScroller, { once: true });
  } else {
    bindScroller();
  }
})();

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
    feedLoaded: false,
    feedLoading: false,
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
      .replace(/\"/g, '&quot;')
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
        headers: { 'Accept': 'application/json' }
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

  const ensureFeedLoaded = async function () {
    if (state.feedLoaded || state.feedLoading) {
      return;
    }

    state.feedLoading = true;
    try {
      await fetchFeed();
      state.feedLoaded = true;
    } finally {
      state.feedLoading = false;
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

  const loadPusherScript = function () {
    return new Promise(function (resolve, reject) {
      if (typeof window.Pusher !== 'undefined') {
        resolve(window.Pusher);
        return;
      }

      const existing = document.querySelector('script[data-admin-pusher=\"1\"]');
      if (existing) {
        existing.addEventListener('load', function () {
          resolve(window.Pusher);
        }, { once: true });
        existing.addEventListener('error', reject, { once: true });
        return;
      }

      const script = document.createElement('script');
      script.src = 'https://js.pusher.com/8.4.0/pusher.min.js';
      script.async = true;
      script.dataset.adminPusher = '1';
      script.addEventListener('load', function () {
        resolve(window.Pusher);
      }, { once: true });
      script.addEventListener('error', reject, { once: true });
      document.head.appendChild(script);
    });
  };

  const initRealtimeNotifications = function () {
    if (!config.enabled || !config.key) {
      return;
    }

    loadPusherScript().then(function () {
      if (typeof window.Pusher === 'undefined') {
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
    }).catch(function () {
    });
  };

  markReadButton.addEventListener('click', function (event) {
    event.preventDefault();
    markAllAsRead();
  });

  notifToggle.addEventListener('click', function () {
    ensureFeedLoaded();
  }, { passive: true });

  if ('requestIdleCallback' in window) {
    window.requestIdleCallback(function () {
      ensureFeedLoaded();
      initRealtimeNotifications();
    }, { timeout: 2000 });
  } else {
    setTimeout(function () {
      ensureFeedLoaded();
      initRealtimeNotifications();
    }, 1200);
  }
})();
