<!-- Floating Chatbot Widget -->
<div
    id="chatbot-widget"
    data-endpoint="{{ route('chatbot.send') }}"
    data-csrf="{{ csrf_token() }}"
>
    <!-- Toggle Button -->
    <button id="chatbot-toggle" class="chatbot-toggle">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        <span class="chatbot-badge" id="chatbot-badge" style="display: none;">AI</span>
    </button>

    <!-- Chat Window -->
    <div id="chatbot-window" class="chatbot-window" style="display: none;">
        <div class="chatbot-header">
            <h3>Chat Assistant</h3>
            <button id="chatbot-close" class="chatbot-close">&times;</button>
        </div>
        
        <div class="chatbot-messages" id="chatbot-messages">
            <div class="chatbot-message bot-message">
                <div class="message-content">
                    Hello, welcome to Telaga Air. How can I help you today?
                </div>
            </div>
        </div>
        
        <form id="chatbot-form" class="chatbot-input-form">
            @csrf
            <input 
                type="text" 
                id="chatbot-input" 
                class="chatbot-input" 
                placeholder="Type your message..."
                autocomplete="off"
                required
            >
            <button type="submit" class="chatbot-send" id="chatbot-send">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </form>
    </div>
</div>
