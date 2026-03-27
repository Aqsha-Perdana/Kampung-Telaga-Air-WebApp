<div
    class="container-fluid py-3"
    id="ai-center-app"
    data-history-url-template="{{ $historyUrlTemplate }}"
    data-active-session-id="{{ $activeSessionId }}"
    data-clear-history-url="{{ $clearHistoryUrl }}"
>
    <div class="card border-0 shadow-sm ai-hero-card mb-4">
        <div class="card-body p-4 p-lg-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div>
                <h2 class="mb-2 fw-bold text-white">
                    <i class="ti ti-history me-2"></i>AI Conversation History
                </h2>
                <p class="mb-0 text-white-50">
                    Review saved AI conversations, session summaries, and general usage activity in one place.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3" id="clear-ai-history-btn">
                    <i class="ti ti-trash me-1"></i>Clear AI History
                </button>
                <button type="button" class="btn btn-light btn-sm rounded-pill px-3" id="open-floating-ai-btn">
                    <i class="ti ti-message-chatbot me-1"></i>Open AI Assistant
                </button>
            </div>
        </div>
    </div>

    @unless($aiTablesReady)
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <strong>AI chat table is not ready yet.</strong> Run the migration before using AI conversation history.
        </div>
    @endunless

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted small mb-2">Total Sessions</p>
                    <h3 class="fw-bold mb-0" id="ai-usage-total-sessions">{{ $usageOverview['total_sessions'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted small mb-2">Total Questions</p>
                    <h3 class="fw-bold mb-0" id="ai-usage-total-questions">{{ $usageOverview['total_questions'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted small mb-2">Active Sessions This Week</p>
                    <h3 class="fw-bold mb-0" id="ai-usage-active-week">{{ $usageOverview['active_this_week'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted small mb-2">Most Frequent Topic</p>
                    <h6 class="fw-bold mb-0" id="ai-usage-top-topic">{{ $usageOverview['top_topic'] }}</h6>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="mb-1 fw-bold"><i class="ti ti-bulb me-2 text-primary"></i>What Can AI Help With?</h5>
                    <p class="mb-0 text-muted small">Focused on practical day-to-day admin decisions.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="row g-3">
                        @foreach($capabilityGroups as $capability)
                            <div class="col-md-6">
                                <div class="schema-card h-100">
                                    <h6 class="mb-2">{{ $capability['title'] }}</h6>
                                    <p class="mb-0 text-muted small">{{ $capability['description'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="mb-1 fw-bold"><i class="ti ti-sparkles me-2 text-primary"></i>Sample Questions</h5>
                    <p class="mb-0 text-muted small">Use these as quick inspiration when opening the floating assistant.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($sampleQuestions as $question)
                            <span class="schema-chip">{{ $question }}</span>
                        @endforeach
                    </div>
                    <hr>
                    <div class="d-flex flex-column gap-2">
                        @foreach($usageGuide as $guide)
                            <div class="check-item mb-0"><i class="ti ti-circle-check text-success"></i> {{ $guide }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex align-items-center justify-content-between gap-2">
                    <div>
                        <h6 class="mb-1 fw-bold"><i class="ti ti-history me-2 text-primary"></i>Session History</h6>
                        <p class="mb-0 text-muted small">Select a session to review its conversation.</p>
                    </div>
                    <span class="badge bg-light text-dark border">{{ count($recentSessions) }} sessions</span>
                </div>
                <div class="card-body px-4 pb-4">
                    <div id="ai-session-list" class="session-list">
                        @forelse($recentSessions as $session)
                            <button
                                type="button"
                                class="session-card {{ $activeSessionId === $session['session_id'] ? 'active' : '' }}"
                                data-session-id="{{ $session['session_id'] }}"
                            >
                                <span class="session-title">{{ $session['title'] }}</span>
                                <span class="session-preview">{{ $session['preview'] }}</span>
                                <span class="session-meta">{{ $session['message_count'] }} messages - {{ $session['last_activity_label'] }}</span>
                            </button>
                        @empty
                            <div class="session-empty" id="ai-session-empty">
                                No saved chat history yet. Start from the floating assistant to create the first session.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h6 class="mb-1 fw-bold"><i class="ti ti-brain me-2 text-primary"></i>Session Summary</h6>
                    <p class="mb-0 text-muted small">A simple recap so admins can quickly understand what the session was about.</p>
                </div>
                <div class="card-body px-4 pb-4" id="ai-session-memory-card">
                    @if(!empty($activeSessionMemory['summary_text']))
                        <div class="schema-card">
                            @if(!empty($activeSessionMemory['active_topic']['label']))
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                    <strong id="ai-session-memory-active-topic">{{ $activeSessionMemory['active_topic']['label'] }}</strong>
                                </div>
                            @endif
                            <p class="mb-2" id="ai-session-memory-summary">{{ $activeSessionMemory['summary_text'] }}</p>
                            <div class="d-flex flex-wrap gap-2 mb-3" id="ai-session-memory-topics">
                                @foreach(($activeSessionMemory['topic_memories'] ?? []) as $topicMemory)
                                    <span class="schema-chip">
                                        {{ $topicMemory['label'] ?? \Illuminate\Support\Str::headline(str_replace('_', ' ', $topicMemory['intent'] ?? 'topic')) }}
                                    </span>
                                @endforeach
                            </div>
                            @if(!empty($activeSessionMemory['active_topic']['summary']))
                                <div class="text-muted small" id="ai-session-memory-active-summary">
                                    {{ $activeSessionMemory['active_topic']['summary'] }}
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="session-empty" id="ai-session-memory-empty">
                            A session summary will appear after a conversation has been saved.
                        </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h6 class="mb-1 fw-bold"><i class="ti ti-message-dots me-2 text-primary"></i>Conversation Details</h6>
                    <p class="mb-0 text-muted small">Full message history for the selected session.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="chat-board mb-0" id="ai-chat-board">
                        @forelse($activeMessages as $message)
                            <div class="chat-bubble {{ $message['role'] === 'admin' ? 'admin' : 'assistant' }}">
                                <div class="bubble-title">{{ $message['role'] === 'admin' ? 'Admin' : 'AI Assistant' }}</div>
                                <div class="bubble-text">{{ $message['message'] }}</div>
                                @if($message['meta'] !== '')
                                    <div class="bubble-meta">{{ $message['meta'] }}</div>
                                @endif
                            </div>
                        @empty
                            <div class="chat-bubble assistant" data-default-bubble="1">
                                <div class="bubble-title">AI Center</div>
                                <div class="bubble-text">No session selected yet. Choose a saved session on the left to view its conversation.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
