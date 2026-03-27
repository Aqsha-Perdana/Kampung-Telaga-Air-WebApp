<script>
  (function () {
    const widget = document.getElementById('admin-ai-widget');
    if (!widget) {
      return;
    }

    const csrfToken = (window.adminRealtimeConfig && window.adminRealtimeConfig.csrfToken) || '';
    const chatUrl = widget.dataset.chatUrl || '';
    const historyUrlTemplate = widget.dataset.historyUrlTemplate || '';
    const aiReady = widget.dataset.aiReady === '1';
    const storageKey = 'admin-ai-widget-session-id';

    const fab = document.getElementById('admin-ai-fab');
    const panel = document.getElementById('admin-ai-panel');
    const closeButton = document.getElementById('admin-ai-close');
    const form = document.getElementById('admin-ai-widget-form');
    const input = document.getElementById('admin-ai-widget-message');
    const sessionInput = document.getElementById('admin-ai-widget-session-id');
    const board = document.getElementById('admin-ai-chat-board');
    const newSessionButton = document.getElementById('admin-ai-widget-new-session');
    const promptButtons = widget.querySelectorAll('.admin-ai-prompt');

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    function formatText(text) {
      return escapeHtml(text).replace(/\n/g, '<br>');
    }

    function metaText(data) {
      const label = String((data.intent || 'assistant')).replace(/_/g, ' ').replace(/\b\w/g, function (char) {
        return char.toUpperCase();
      });

      return 'Topik: ' + label;
    }

    function buildBubble(role, text, meta) {
      const bubble = document.createElement('div');
      bubble.className = 'admin-ai-bubble ' + role;
      bubble.innerHTML =
        '<div class="admin-ai-bubble-title">' + (role === 'admin' ? 'Anda' : 'AI Assistant') + '</div>' +
        '<div class="admin-ai-bubble-text">' + formatText(text) + '</div>' +
        (meta ? '<div class="admin-ai-bubble-meta">' + escapeHtml(meta) + '</div>' : '');

      return bubble;
    }

    function appendBubble(role, text, meta) {
      const bubble = buildBubble(role, text, meta);
      board.appendChild(bubble);
      board.scrollTop = board.scrollHeight;
    }

    function clearBoard() {
      board.innerHTML = '';
    }

    function renderEmpty() {
      clearBoard();
      board.innerHTML =
        '<div class="admin-ai-chat-empty" data-default-bubble="1">' +
          '<strong>Siap membantu</strong>' +
          '<span>Coba tanya ringkas seperti “trend penjualan minggu ini” atau “detail Paket Cihuy”.</span>' +
        '</div>';
    }

    function setSessionId(sessionId) {
      sessionInput.value = sessionId || '';

      if (sessionId) {
        localStorage.setItem(storageKey, sessionId);
      } else {
        localStorage.removeItem(storageKey);
      }
    }

    function buildHistoryUrl(sessionId) {
      return historyUrlTemplate.replace('__SESSION__', encodeURIComponent(sessionId));
    }

    function openPanel() {
      panel.classList.add('is-open');
      panel.setAttribute('aria-hidden', 'false');
      fab.classList.add('is-hidden');
      input.focus();

      const currentSessionId = sessionInput.value || localStorage.getItem(storageKey) || '';
      if (currentSessionId && board.querySelector('[data-default-bubble="1"]')) {
        loadHistory(currentSessionId);
      }
    }

    function closePanel() {
      panel.classList.remove('is-open');
      panel.setAttribute('aria-hidden', 'true');
      fab.classList.remove('is-hidden');
    }

    function loadHistory(sessionId) {
      if (!sessionId || !aiReady) {
        return;
      }

      fetch(buildHistoryUrl(sessionId), {
        headers: {
          'Accept': 'application/json'
        }
      })
        .then(function (response) {
          return response.json();
        })
        .then(function (data) {
          if (!data.success) {
            throw new Error(data.message || 'Riwayat AI gagal dimuat.');
          }

          setSessionId(data.session_id || sessionId);
          clearBoard();

          if (!Array.isArray(data.messages) || !data.messages.length) {
            renderEmpty();
            return;
          }

          data.messages.slice(-10).forEach(function (message) {
            appendBubble(message.role === 'admin' ? 'admin' : 'assistant', message.message || '', message.meta || '');
          });
        })
        .catch(function (error) {
          clearBoard();
          appendBubble('assistant', error.message || 'Riwayat AI gagal dimuat.', 'Status');
        });
    }

    function submitChat(message) {
      appendBubble('admin', message, '');

      fetch(chatUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
          message: message,
          session_id: sessionInput.value || ''
        })
      })
        .then(function (response) {
          return response.json();
        })
        .then(function (data) {
          if (!data.success) {
            throw new Error(data.message || 'AI assistant gagal menjawab.');
          }

          setSessionId(data.session_id || '');
          appendBubble('assistant', data.message || '', metaText(data));
        })
        .catch(function (error) {
          appendBubble('assistant', error.message || 'Terjadi kesalahan saat memproses chat.', 'Status');
        });
    }

    if (fab) {
      fab.addEventListener('click', openPanel);
    }

    if (closeButton) {
      closeButton.addEventListener('click', closePanel);
    }

    window.addEventListener('admin-ai-widget:open', function () {
      openPanel();
    });

    if (form) {
      form.addEventListener('submit', function (event) {
        event.preventDefault();

        if (!aiReady) {
          clearBoard();
          appendBubble('assistant', 'AI chat belum siap. Jalankan migration AI terlebih dahulu.', 'Status');
          return;
        }

        const message = input.value.trim();
        if (!message) {
          return;
        }

        if (board.querySelector('[data-default-bubble="1"]')) {
          clearBoard();
        }

        input.value = '';
        submitChat(message);
      });
    }

    promptButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        if (!aiReady) {
          clearBoard();
          appendBubble('assistant', 'AI chat belum siap. Jalankan migration AI terlebih dahulu.', 'Status');
          return;
        }

        const prompt = button.dataset.prompt || '';
        if (!prompt) {
          return;
        }

        openPanel();

        if (board.querySelector('[data-default-bubble="1"]')) {
          clearBoard();
        }

        submitChat(prompt);
      });
    });

    if (newSessionButton) {
      newSessionButton.addEventListener('click', function () {
        setSessionId('');
        renderEmpty();
        input.focus();
      });
    }

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && panel.classList.contains('is-open')) {
        closePanel();
      }
    });

    renderEmpty();
    const existingSessionId = localStorage.getItem(storageKey) || '';
    if (existingSessionId) {
      setSessionId(existingSessionId);
    }
  })();
</script>
