@push('scripts')
<script>
(function () {
    const app = document.getElementById('ai-center-app');
    if (!app) {
        return;
    }

    const csrfToken = (window.adminRealtimeConfig && window.adminRealtimeConfig.csrfToken) || '';
    const historyUrlTemplate = app.dataset.historyUrlTemplate || '';
    const clearHistoryUrl = app.dataset.clearHistoryUrl || '';
    const chatBoard = document.getElementById('ai-chat-board');
    const sessionList = document.getElementById('ai-session-list');
    const sessionMemoryCard = document.getElementById('ai-session-memory-card');
    const openFloatingButton = document.getElementById('open-floating-ai-btn');
    const clearHistoryButton = document.getElementById('clear-ai-history-btn');
    const totalSessionsEl = document.getElementById('ai-usage-total-sessions');
    const totalQuestionsEl = document.getElementById('ai-usage-total-questions');
    const activeWeekEl = document.getElementById('ai-usage-active-week');
    const topTopicEl = document.getElementById('ai-usage-top-topic');

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatText(text) {
        return escapeHtml(text).replace(/\n/g, '<br>');
    }

    function buildBubble(role, text, meta) {
        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble ' + role;
        bubble.innerHTML =
            '<div class="bubble-title">' + (role === 'admin' ? 'Admin' : 'AI Assistant') + '</div>' +
            '<div class="bubble-text">' + formatText(text) + '</div>' +
            (meta ? '<div class="bubble-meta">' + escapeHtml(meta) + '</div>' : '');

        return bubble;
    }

    function clearBoard() {
        chatBoard.innerHTML = '';
    }

    function renderMessages(messages) {
        clearBoard();

        if (!Array.isArray(messages) || !messages.length) {
            chatBoard.innerHTML =
                '<div class="chat-bubble assistant" data-default-bubble="1">' +
                    '<div class="bubble-title">AI Center</div>' +
                    '<div class="bubble-text">No session selected yet. Choose a saved session on the left to view its conversation.</div>' +
                '</div>';
            return;
        }

        messages.forEach(function (message) {
            chatBoard.appendChild(buildBubble(message.role === 'admin' ? 'admin' : 'assistant', message.message || '', message.meta || ''));
        });
        chatBoard.scrollTop = 0;
    }

    function renderEmptyState() {
        renderMessages([]);
        renderSessionMemory(null);

        if (sessionList) {
            sessionList.innerHTML =
                '<div class="session-empty" id="ai-session-empty">' +
                    'No saved chat history yet. Start from the floating assistant to create the first session.' +
                '</div>';
        }
    }

    function renderSessionMemory(memory) {
        if (!sessionMemoryCard) {
            return;
        }

        if (!memory || !memory.summary_text) {
            sessionMemoryCard.innerHTML = '<div class="session-empty" id="ai-session-memory-empty">A session summary will appear after a conversation has been saved.</div>';
            return;
        }

        const topicMemories = Array.isArray(memory.topic_memories) ? memory.topic_memories : [];
        const activeTopic = memory.active_topic || {};
        const topicHtml = topicMemories.map(function (topic) {
            const label = topic.label || String(topic.intent || 'topic').replace(/_/g, ' ').replace(/\b\w/g, function (char) {
                return char.toUpperCase();
            });

            return '<span class="schema-chip">' + escapeHtml(label) + '</span>';
        }).join('');

        sessionMemoryCard.innerHTML =
            '<div class="schema-card">' +
                (activeTopic.label ? '<div class="d-flex align-items-center justify-content-between gap-2 mb-2"><strong id="ai-session-memory-active-topic">' + escapeHtml(activeTopic.label) + '</strong></div>' : '') +
                '<p class="mb-2" id="ai-session-memory-summary">' + escapeHtml(memory.summary_text) + '</p>' +
                '<div class="d-flex flex-wrap gap-2 mb-3" id="ai-session-memory-topics">' + topicHtml + '</div>' +
                (activeTopic.summary ? '<div class="text-muted small" id="ai-session-memory-active-summary">' + escapeHtml(activeTopic.summary) + '</div>' : '') +
            '</div>';
    }

    function setActiveSession(sessionId) {
        const sessionCards = sessionList ? sessionList.querySelectorAll('.session-card') : [];

        sessionCards.forEach(function (card) {
            card.classList.toggle('active', card.dataset.sessionId === sessionId);
        });
    }

    function buildHistoryUrl(sessionId) {
        return historyUrlTemplate.replace('__SESSION__', encodeURIComponent(sessionId));
    }

    function createSessionCard(session) {
        const card = document.createElement('button');
        card.type = 'button';
        card.className = 'session-card';
        card.dataset.sessionId = session.session_id;
        card.innerHTML =
            '<span class="session-title">' + escapeHtml(session.title || 'Admin chat session') + '</span>' +
            '<span class="session-preview">' + escapeHtml(session.preview || '') + '</span>' +
            '<span class="session-meta">' + escapeHtml((session.message_count || 0) + ' messages - ' + (session.last_activity_label || 'just now')) + '</span>';

        return card;
    }

    function upsertSessionCard(session) {
        if (!sessionList || !session || !session.session_id) {
            return;
        }

        const emptyState = document.getElementById('ai-session-empty');
        if (emptyState) {
            emptyState.remove();
        }

        let card = sessionList.querySelector('[data-session-id="' + session.session_id + '"]');
        if (!card) {
            card = createSessionCard(session);
            sessionList.prepend(card);
        } else {
            card.querySelector('.session-title').textContent = session.title || 'Admin chat session';
            card.querySelector('.session-preview').textContent = session.preview || '';
            card.querySelector('.session-meta').textContent = (session.message_count || 0) + ' messages - ' + (session.last_activity_label || 'just now');
        }

        setActiveSession(session.session_id);
    }

    function loadSessionHistory(sessionId) {
        if (!sessionId) {
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
                    throw new Error(data.message || 'Session history could not be loaded.');
                }

                renderMessages(data.messages || []);
                upsertSessionCard(data.session || null);
                renderSessionMemory(data.session_memory || null);
            })
            .catch(function (error) {
                clearBoard();
                chatBoard.appendChild(buildBubble('assistant', error.message || 'Session history could not be loaded.', 'Status'));
            });
    }

    function updateUsageOverview(overview) {
        if (!overview) {
            return;
        }

        if (totalSessionsEl) {
            totalSessionsEl.textContent = overview.total_sessions || 0;
        }

        if (totalQuestionsEl) {
            totalQuestionsEl.textContent = overview.total_questions || 0;
        }

        if (activeWeekEl) {
            activeWeekEl.textContent = overview.active_this_week || 0;
        }

        if (topTopicEl) {
            topTopicEl.textContent = overview.top_topic || 'No data yet';
        }
    }

    if (sessionList) {
        sessionList.addEventListener('click', function (event) {
            const target = event.target.closest('.session-card');
            if (!target) {
                return;
            }

            loadSessionHistory(target.dataset.sessionId || '');
        });
    }

    if (openFloatingButton) {
        openFloatingButton.addEventListener('click', function () {
            window.dispatchEvent(new CustomEvent('admin-ai-widget:open'));
        });
    }

    if (clearHistoryButton) {
        clearHistoryButton.addEventListener('click', function () {
            if (!clearHistoryUrl) {
                return;
            }

            const confirmed = window.confirm('Clear all AI chat history for this admin account? This action cannot be undone.');
            if (!confirmed) {
                return;
            }

            clearHistoryButton.disabled = true;

            fetch(clearHistoryUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({})
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (!data.success) {
                        throw new Error(data.message || 'AI history could not be cleared.');
                    }

                    renderEmptyState();
                    updateUsageOverview(data.usage_overview || null);

                    if (window.localStorage) {
                        localStorage.removeItem('admin-ai-widget-session-id');
                    }

                    clearBoard();
                    chatBoard.appendChild(buildBubble('assistant', data.message || 'Admin AI history has been cleared successfully.', 'Status'));
                })
                .catch(function (error) {
                    window.alert(error.message || 'AI history could not be cleared.');
                })
                .finally(function () {
                    clearHistoryButton.disabled = false;
                });
        });
    }
})();
</script>
@endpush
