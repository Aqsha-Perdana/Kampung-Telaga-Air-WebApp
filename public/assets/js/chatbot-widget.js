(function () {
    function initChatbotWidget() {
        var widget = document.getElementById('chatbot-widget');
        if (!widget) return;

        var toggle = document.getElementById('chatbot-toggle');
        var chatWindow = document.getElementById('chatbot-window');
        var close = document.getElementById('chatbot-close');
        var form = document.getElementById('chatbot-form');
        var input = document.getElementById('chatbot-input');
        var messages = document.getElementById('chatbot-messages');
        var badge = document.getElementById('chatbot-badge');
        var sendBtn = document.getElementById('chatbot-send');

        if (!toggle || !chatWindow || !close || !form || !input || !messages || !badge || !sendBtn) {
            return;
        }

        var endpoint = widget.dataset.endpoint || '';
        var csrfToken = widget.dataset.csrf || '';
        var isOpen = false;

        toggle.addEventListener('click', function () {
            isOpen = !isOpen;
            chatWindow.style.display = isOpen ? 'flex' : 'none';
            badge.style.display = 'none';

            if (isOpen) {
                input.focus();
            }
        });

        close.addEventListener('click', function () {
            isOpen = false;
            chatWindow.style.display = 'none';
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var message = input.value.trim();
            if (!message) return;

            addMessage(message, 'user');
            input.value = '';

            var localReply = getLocalQuickReply(message);
            if (localReply) {
                addMessage(localReply, 'bot');
                input.focus();
                return;
            }

            var typingId = showTypingIndicator();
            sendBtn.disabled = true;

            var controller = new AbortController();
            var timeoutId = setTimeout(function () {
                controller.abort();
            }, 12000);

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                signal: controller.signal,
                body: JSON.stringify({
                    message: message,
                    current_path: window.location.pathname + window.location.search
                })
            })
                .then(async function (response) {
                    var data = await response.json().catch(function () { return null; });
                    if (!response.ok || !data) {
                        throw new Error('Invalid response');
                    }

                    return data;
                })
                .then(function (data) {
                    removeTypingIndicator(typingId);

                    if (data.success) {
                        addMessage(data.message, 'bot');
                    } else {
                        addMessage('Sorry, something went wrong. Please try again.', 'bot');
                    }
                })
                .catch(function (error) {
                    removeTypingIndicator(typingId);

                    if (error.name === 'AbortError') {
                        addMessage('The server took too long to respond. Please send your question again.', 'bot');
                    } else {
                        addMessage('Sorry, there is a connection problem. Please try again.', 'bot');
                    }

                    console.error('Error:', error);
                })
                .finally(function () {
                    clearTimeout(timeoutId);
                    sendBtn.disabled = false;
                    input.focus();
                });
        });

        function getLocalQuickReply(text) {
            var cleaned = text
                .toLowerCase()
                .replace(/[^\p{L}\p{N}\s]/gu, ' ')
                .replace(/\s+/g, ' ')
                .trim();

            if (!cleaned) return null;

            var greetings = ['halo', 'hai', 'hi', 'hello', 'morning', 'afternoon', 'evening'];
            if (greetings.includes(cleaned)) {
                return 'Hello, I can help with tour packages, destinations, homestays, culinary spots, the kiosk, checkout, and your orders.';
            }

            var thanks = ['terima kasih', 'makasih', 'thanks', 'thx', 'thank you'];
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
                    '4. Choose Stripe or Xendit as your payment method.',
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
            var messageDiv = document.createElement('div');
            messageDiv.className = 'chatbot-message ' + type + '-message';
            messageDiv.innerHTML = '<div class="message-content">' + escapeHtml(text) + '</div>';
            messages.appendChild(messageDiv);
            messages.scrollTop = messages.scrollHeight;
        }

        function showTypingIndicator() {
            var typingDiv = document.createElement('div');
            typingDiv.className = 'chatbot-message bot-message';
            typingDiv.id = 'typing-indicator-' + Date.now();
            typingDiv.innerHTML = [
                '<div class="typing-indicator">',
                '    <span></span>',
                '    <span></span>',
                '    <span></span>',
                '</div>'
            ].join('');
            messages.appendChild(typingDiv);
            messages.scrollTop = messages.scrollHeight;
            return typingDiv.id;
        }

        function removeTypingIndicator(id) {
            var indicator = document.getElementById(id);
            if (indicator) {
                indicator.remove();
            }
        }

        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initChatbotWidget);
    } else {
        initChatbotWidget();
    }
})();
