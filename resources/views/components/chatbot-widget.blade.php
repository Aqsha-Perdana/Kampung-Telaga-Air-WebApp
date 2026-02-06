<!-- Floating Chatbot Widget -->
<div id="chatbot-widget">
    <!-- Toggle Button -->
    <button id="chatbot-toggle" class="chatbot-toggle">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        <span class="chatbot-badge" id="chatbot-badge">?</span>
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
                    Halo Telaga People !🌊💧 Is there anything I can help you with?
                </div>
            </div>
        </div>
        
        <form id="chatbot-form" class="chatbot-input-form">
            @csrf
            <input 
                type="text" 
                id="chatbot-input" 
                class="chatbot-input" 
                placeholder="Write Your Message..."
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

<style>
#chatbot-widget {
    position: fixed;
    bottom: 110px; /* Di atas tombol WhatsApp (60px height + 30px margin + 20px gap) */
    right: 30px;
    z-index: 9999;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

.chatbot-toggle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #96defb 20%, #2a93cc);
    border: none;
    color: white;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: all 0.3s ease;
}

.chatbot-toggle:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
}

.chatbot-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ff4757;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
}

.chatbot-window {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 380px;
    height: 500px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: slideUp 0.3s ease;
    max-height: calc(100vh - 220px); /* Prevent window from going off screen */
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.chatbot-header {
    background: linear-gradient(135deg, #96defb 20%, #2a93cc);
    color: white;
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chatbot-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.chatbot-close {
    background: none;
    border: none;
    color: white;
    font-size: 28px;
    cursor: pointer;
    line-height: 1;
    padding: 0;
    width: 30px;
    height: 30px;
}

.chatbot-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f7f9fc;
}

.chatbot-message {
    margin-bottom: 16px;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message-content {
    display: inline-block;
    padding: 12px 16px;
    border-radius: 12px;
    max-width: 80%;
    word-wrap: break-word;
    line-height: 1.5;
}

.bot-message .message-content {
    background: white;
    color: #333;
    border-bottom-left-radius: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.user-message {
    text-align: right;
}

.user-message .message-content {
    background: linear-gradient(135deg, #96defb 20%, #2a93cc);
    color: white;
    border-bottom-right-radius: 4px;
}

.chatbot-input-form {
    display: flex;
    padding: 16px;
    background: white;
    border-top: 1px solid #e1e8ed;
    gap: 8px;
}

.chatbot-input {
    flex: 1;
    border: 1px solid #e1e8ed;
    border-radius: 24px;
    padding: 10px 16px;
    font-size: 14px;
    outline: none;
    transition: border 0.3s ease;
}

.chatbot-input:focus {
    border-color: #667eea;
}

.chatbot-send {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #96defb 20%, #2a93cc);
    border: none;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.chatbot-send:hover {
    transform: scale(1.1);
}

.chatbot-send:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.typing-indicator {
    display: inline-block;
    padding: 12px 16px;
    background: white;
    border-radius: 12px;
    border-bottom-left-radius: 4px;
}

.typing-indicator span {
    height: 8px;
    width: 8px;
    background: #667eea;
    border-radius: 50%;
    display: inline-block;
    margin: 0 2px;
    animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-indicator span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {
    0%, 60%, 100% {
        transform: translateY(0);
    }
    30% {
        transform: translateY(-10px);
    }
}

@media (max-width: 480px) {
    #chatbot-widget {
        bottom: 110px; /* Tetap di atas WhatsApp di mobile */
        right: 10px;
    }
    
    .chatbot-window {
        width: calc(100vw - 40px);
        right: -10px;
        height: 450px;
        max-height: calc(100vh - 200px);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('chatbot-toggle');
    const window = document.getElementById('chatbot-window');
    const close = document.getElementById('chatbot-close');
    const form = document.getElementById('chatbot-form');
    const input = document.getElementById('chatbot-input');
    const messages = document.getElementById('chatbot-messages');
    const badge = document.getElementById('chatbot-badge');
    const sendBtn = document.getElementById('chatbot-send');
    
    let isOpen = false;

    // Toggle chat window
    toggle.addEventListener('click', function() {
        isOpen = !isOpen;
        window.style.display = isOpen ? 'flex' : 'none';
        badge.style.display = 'none';
    });

    close.addEventListener('click', function() {
        isOpen = false;
        window.style.display = 'none';
    });

    // Handle form submit
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = input.value.trim();
        if (!message) return;

        // Add user message
        addMessage(message, 'user');
        input.value = '';
        
        // Show typing indicator
        const typingId = showTypingIndicator();
        
        // Disable send button
        sendBtn.disabled = true;

        // Send to server
        fetch('{{ route("chatbot.send") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || 
                                document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            // Remove typing indicator
            removeTypingIndicator(typingId);
            
            if (data.success) {
                addMessage(data.message, 'bot');
            } else {
                addMessage('Maaf, terjadi kesalahan. Silakan coba lagi.', 'bot');
            }
        })
        .catch(error => {
            removeTypingIndicator(typingId);
            addMessage('Maaf, koneksi bermasalah. Silakan coba lagi.', 'bot');
            console.error('Error:', error);
        })
        .finally(() => {
            sendBtn.disabled = false;
            input.focus();
        });
    });

    function addMessage(text, type) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chatbot-message ${type}-message`;
        messageDiv.innerHTML = `<div class="message-content">${escapeHtml(text)}</div>`;
        messages.appendChild(messageDiv);
        messages.scrollTop = messages.scrollHeight;
    }

    function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'chatbot-message bot-message';
        typingDiv.id = 'typing-indicator-' + Date.now();
        typingDiv.innerHTML = `
            <div class="typing-indicator">
                <span></span>
                <span></span>
                <span></span>
            </div>
        `;
        messages.appendChild(typingDiv);
        messages.scrollTop = messages.scrollHeight;
        return typingDiv.id;
    }

    function removeTypingIndicator(id) {
        const indicator = document.getElementById(id);
        if (indicator) {
            indicator.remove();
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>