@php
    $adminAiReady = \Illuminate\Support\Facades\Schema::hasTable('ai_chat_logs');
    $adminAiPrompts = [
        ['label' => 'Sales Trend', 'prompt' => 'bagaimana tren penjualan 7 hari terakhir dibanding 7 hari sebelumnya?'],
        ['label' => 'Top Customer', 'prompt' => 'siapa customer paling bernilai dalam 90 hari terakhir?'],
        ['label' => 'Finance', 'prompt' => 'ringkas kondisi keuangan 30 hari terakhir'],
        ['label' => 'Bottleneck', 'prompt' => 'resource mana yang paling berisiko bottleneck dalam 7 hari ke depan?'],
    ];
@endphp

<div
    id="admin-ai-widget"
    class="admin-ai-widget"
    data-chat-url="{{ route('admin.ai-center.chat') }}"
    data-history-url-template="{{ route('admin.ai-center.history', ['sessionId' => '__SESSION__']) }}"
    data-center-url="{{ route('admin.ai-center.index') }}"
    data-ai-ready="{{ $adminAiReady ? '1' : '0' }}"
>
    <button type="button" class="admin-ai-fab" id="admin-ai-fab" aria-label="Open AI assistant">
        <span class="admin-ai-fab-icon"><i class="ti ti-message-chatbot"></i></span>
        <span class="admin-ai-fab-text">
            <strong>AI Assistant</strong>
            <small>Tanya cepat dari halaman ini</small>
        </span>
    </button>

    <div class="admin-ai-panel" id="admin-ai-panel" aria-hidden="true">
        <div class="admin-ai-panel-header">
            <div>
                <h6 class="mb-1">Admin AI Assistant</h6>
                <p class="mb-0">Tanya penjualan, resource, paket, keuangan, atau refund tanpa pindah halaman.</p>
            </div>
            <button type="button" class="admin-ai-close" id="admin-ai-close" aria-label="Close AI assistant">
                <i class="ti ti-x"></i>
            </button>
        </div>

        <div class="admin-ai-panel-body">
            <div class="admin-ai-prompts" id="admin-ai-widget-prompts">
                @foreach($adminAiPrompts as $prompt)
                    <button type="button" class="admin-ai-prompt" data-prompt="{{ $prompt['prompt'] }}">
                        <span>{{ $prompt['label'] }}</span>
                    </button>
                @endforeach
            </div>

            <div class="admin-ai-chat-board" id="admin-ai-chat-board">
                <div class="admin-ai-chat-empty" data-default-bubble="1">
                    <strong>Siap membantu</strong>
                    <span>Coba tanya ringkas seperti “trend penjualan minggu ini” atau “detail Paket Cihuy”.</span>
                </div>
            </div>
        </div>

        <div class="admin-ai-panel-footer">
            <form id="admin-ai-widget-form" class="admin-ai-form">
                <input type="hidden" id="admin-ai-widget-session-id" value="">
                <input
                    type="text"
                    id="admin-ai-widget-message"
                    class="form-control"
                    maxlength="2000"
                    placeholder="Tulis pertanyaan admin di sini..."
                >
                <button type="submit" class="btn btn-primary" id="admin-ai-widget-send">
                    <i class="ti ti-send"></i>
                </button>
            </form>
            <div class="admin-ai-panel-actions">
                <button type="button" class="btn btn-light btn-sm" id="admin-ai-widget-new-session">
                    <i class="ti ti-plus me-1"></i>New Chat
                </button>
                <a href="{{ route('admin.ai-center.index') }}" class="btn btn-outline-dark btn-sm">
                    <i class="ti ti-history me-1"></i>Riwayat AI Center
                </a>
            </div>
        </div>
    </div>
</div>
