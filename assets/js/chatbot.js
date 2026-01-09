// assets/js/chatbot.js

document.addEventListener('DOMContentLoaded', () => {
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const chatMessages = document.getElementById('chatMessages');

    if (chatForm) {
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = chatInput.value.trim();
            if (!message) return;

            // 1. Add User Message
            addMessage('user', message);
            chatInput.value = '';

            // 2. Show Loading
            const loadingId = addLoading();

            try {
                // 3. Call API
                const response = await fetch('../api/chatbot_api_proxy.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: message })
                });

                const data = await response.json();

                // 4. Remove Loading and Add Bot Message
                removeLoading(loadingId);
                if (data.reply) {
                    addMessage('bot', data.reply);
                } else if (data.error) {
                    addMessage('bot', "Error: " + data.error);
                }
            } catch (err) {
                removeLoading(loadingId);
                addMessage('bot', "Sorry, i am not available right now.");
                console.error(err);
            }
        });
    }

    function addMessage(sender, text) {
        const div = document.createElement('div');
        div.className = `flex ${sender === 'user' ? 'justify-end' : 'justify-start'} mb-4 animate-fade-in`;

        const bubble = document.createElement('div');
        bubble.className = `px-4 py-2 max-w-[80%] shadow-sm ${sender === 'user' ? 'chat-bubble-user' : 'chat-bubble-bot'}`;
        bubble.textContent = text;

        div.appendChild(bubble);
        chatMessages.appendChild(div);

        // Scroll to bottom
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function addLoading() {
        const id = 'loading-' + Date.now();
        const div = document.createElement('div');
        div.id = id;
        div.className = 'flex justify-start mb-4';
        div.innerHTML = `
            <div class="px-4 py-2 chat-bubble-bot flex space-x-2 items-center">
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce delay-100"></div>
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce delay-200"></div>
            </div>
        `;
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        return id;
    }

    function removeLoading(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }
});
