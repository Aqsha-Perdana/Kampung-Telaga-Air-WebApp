<!-- Floating Chatbot Widget -->
<div id="chatbot-widget">
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

<style>
#chatbot-widget {
    position: fixed;
    bottom: 104px;
    right: 24px;
    z-index: 1042;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

.chatbot-toggle {
    width: 58px;
    height: 58px;
    border-radius: 50%;
    background: linear-gradient(140deg, #7dd3fc 8%, #2563eb 92%);
    border: 2px solid rgba(255, 255, 255, 0.75);
    color: white;
    cursor: pointer;
    box-shadow: 0 10px 24px rgba(37, 99, 235, 0.33);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.chatbot-toggle:hover {
    transform: translateY(-1px) scale(1.05);
    box-shadow: 0 14px 28px rgba(37, 99, 235, 0.38);
}

.chatbot-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    background: #ff4757;
    color: white;
    border-radius: 50%;
    width: 19px;
    height: 19px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 700;
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

@media (max-width: 767.98px) {
    #chatbot-widget {
        bottom: calc(114px + env(safe-area-inset-bottom));
        right: 12px;
    }

    .chatbot-toggle {
        width: 56px;
        height: 56px;
    }
    
    .chatbot-window {
        width: min(92vw, 360px);
        right: 0;
        height: min(68vh, 520px);
        max-height: calc(100vh - 160px);
        border-radius: 18px;
        box-shadow: 0 18px 38px rgba(15, 23, 42, 0.25);
    }

    .chatbot-header {
        padding: 14px 16px;
    }

    .chatbot-header h3 {
        font-size: 16px;
    }

    .chatbot-messages {
        padding: 14px;
    }

    .message-content {
        max-width: 86%;
    }

    .chatbot-input-form {
        padding: 12px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('chatbot-toggle');
    const chatWindow = document.getElementById('chatbot-window');
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
        chatWindow.style.display = isOpen ? 'flex' : 'none';
        badge.style.display = 'none';
    });

    close.addEventListener('click', function() {
        isOpen = false;
        chatWindow.style.display = 'none';
    });

    // Handle form submit
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = input.value.trim();
        if (!message) return;

        // Add user message
        addMessage(message, 'user');
        input.value = '';
        
        const localReply = getLocalQuickReply(message);
        if (localReply) {
            addMessage(localReply, 'bot');
            input.focus();
            return;
        }

        // Show typing indicator
        const typingId = showTypingIndicator();
        sendBtn.disabled = true;

        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 12000);

        // Send to server
        fetch('{{ route("chatbot.send") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || 
                                document.querySelector('input[name="_token"]').value
            },
            signal: controller.signal,
            body: JSON.stringify({
                message: message,
                current_path: window.location.pathname + window.location.search
            })
        })
        .then(async response => {
            const data = await response.json().catch(() => null);
            if (!response.ok || !data) {
                throw new Error('Invalid response');
            }
            return data;
        })
        .then(data => {
            // Remove typing indicator
            removeTypingIndicator(typingId);
            
            if (data.success) {
                addMessage(data.message, 'bot');
            } else {
                addMessage('Sorry, something went wrong. Please try again.', 'bot');
            }
        })
        .catch(error => {
            removeTypingIndicator(typingId);
            if (error.name === 'AbortError') {
                addMessage('The server took too long to respond. Please send your question again.', 'bot');
            } else {
                addMessage('Sorry, there is a connection problem. Please try again.', 'bot');
            }
            console.error('Error:', error);
        })
        .finally(() => {
            clearTimeout(timeoutId);
            sendBtn.disabled = false;
            input.focus();
        });
    });

    function getLocalQuickReply(text) {
        const cleaned = text
            .toLowerCase()
            .replace(/[^\p{L}\p{N}\s]/gu, ' ')
            .replace(/\s+/g, ' ')
            .trim();

        if (!cleaned) return null;

        const greetings = ['halo', 'hai', 'hi', 'hello', 'morning', 'afternoon', 'evening'];
        if (greetings.includes(cleaned)) {
            return 'Hello, I can help with tour packages, destinations, homestays, culinary spots, the kiosk, checkout, and your orders.';
        }

        const thanks = ['terima kasih', 'makasih', 'thanks', 'thx', 'thank you'];
        if (thanks.includes(cleaned)) {
            return 'You are welcome. If you want, I can also help you choose the most suitable package.';
        }

        if (
            cleaned.includes('checkout') ||
            cleaned.includes('cara bayar') ||
            cleaned.includes('pembayaran') ||
            cleaned.includes('how to pay') ||
            cleaned.includes('payment')
        ) {
            return [
                'How to check out:',
                '1. Log in to your traveler account.',
                '2. Add a package to your cart at /cart.',
                '3. Open /checkout and complete the customer details.',
                '4. Continue with card payment through Stripe.',
                '5. Check your order status at /orders.'
            ].join('\n');
        }

        if (cleaned.includes('refund')) {
            return 'Refund requests can be submitted from the order detail page and only apply to orders with paid status.';
        }

        if (cleaned.includes('riwayat') || cleaned.includes('order history') || cleaned.includes('pesanan saya')) {
            return 'Your order history is available at /orders after you log in.';
        }

        return null;
    }

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

