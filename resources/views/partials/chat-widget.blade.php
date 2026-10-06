<div class="chat-widget">
    <div class="chat-header">
        <div class="chat-header-avatar"><i class="fa-solid fa-robot"></i></div>
        <div style="flex-grow: 1;">
            <div class="chat-header-title">{{ $title ?? 'Konsultasi Pakar AI' }}</div>
            <p class="text-muted" style="font-size: 0.75rem; margin-top: 0.05rem;">{{ $subtitle ?? 'Bertanya mengenai asisten pakar pepaya' }}</p>
        </div>
        <div class="chat-header-status"></div>
    </div>

    <!-- Chat message list -->
    <div class="chat-messages" id="chat-messages-box-{{ $scope }}">
        <div class="chat-bubble assistant">
            {!! $greeting ?? 'Halo! Ada yang bisa saya bantu?' !!}
        </div>
    </div>

    <!-- Chat typing area -->
    <form id="chat-form-{{ $scope }}" onsubmit="handleSendMsg_{{ $scope }}(event)">
        <div class="chat-input-area">
            <input type="text" id="chat-input-text-{{ $scope }}" class="chat-input" placeholder="Tanyakan obat, pencegahan, atau detail lainnya..." autocomplete="off">
            <button type="submit" class="chat-send-btn" id="chat-send-button-{{ $scope }}">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </div>
    </form>
</div>

<script>
    (function() {
        const scope = "{{ $scope }}";
        const endpoint = "{{ $endpoint }}";
        
        const chatMsgBox = document.getElementById(`chat-messages-box-${scope}`);
        const chatInput = document.getElementById(`chat-input-text-${scope}`);
        const sendBtn = document.getElementById(`chat-send-button-${scope}`);
        const chatForm = document.getElementById(`chat-form-${scope}`);

        // Expose handleSendMsg function globally but scoped
        window[`handleSendMsg_${scope}`] = function(event) {
            event.preventDefault();
            const text = chatInput.value.trim();
            if (!text) return;

            // Display user bubble immediately
            appendChatBubble('user', text);
            chatInput.value = '';
            scrollToBottom();

            // Lock UI and show typing placeholder
            chatInput.disabled = true;
            sendBtn.disabled = true;
            
            const typingBubble = appendChatBubble('assistant', '<i class="fa-solid fa-circle-notch fa-spin"></i> Sedang mengetik...');
            scrollToBottom();

            // Call backend via AJAX
            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ message: text })
            })
            .then(response => response.json())
            .then(data => {
                // Remove typing bubble
                typingBubble.remove();

                if (data.status === 'success') {
                    appendChatBubble('assistant', data.ai_chat.pesan);
                } else {
                    appendChatBubble('assistant', 'Maaf, terjadi kesalahan: ' + data.message);
                }
                scrollToBottom();
            })
            .catch(err => {
                typingBubble.remove();
                appendChatBubble('assistant', 'Gagal terhubung dengan asisten AI. Pastikan server aktif.');
                scrollToBottom();
            })
            .finally(() => {
                chatInput.disabled = false;
                sendBtn.disabled = false;
                chatInput.focus();
            });
        };

        // Load initial chat history on document ready
        document.addEventListener("DOMContentLoaded", function() {
            loadChatHistory();
        });

        // Trigger load history in case of dynamic SPA pages or dynamic rendering
        setTimeout(loadChatHistory, 100);

        function loadChatHistory() {
            fetch(endpoint)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        // Clear messages box except the first greeting bubble
                        chatMsgBox.innerHTML = `
                            <div class="chat-bubble assistant">
                                {!! $greeting ?? 'Halo! Ada yang bisa saya bantu?' !!}
                            </div>
                        `;
                        
                        data.forEach(chat => {
                            appendChatBubble(chat.role, chat.pesan);
                        });
                        scrollToBottom();
                    }
                })
                .catch(err => console.error(`Gagal memuat histori chat untuk ${scope}:`, err));
        }

        function appendChatBubble(role, message) {
            const bubble = document.createElement('div');
            bubble.className = `chat-bubble ${role}`;
            
            // Convert markdown stars/bold in messages simple replacement
            if (role === 'assistant') {
                let formattedMsg = message
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\*(.*?)\*/g, '<em>$1</em>')
                    .replace(/\n/g, '<br>');
                bubble.innerHTML = formattedMsg;
            } else {
                bubble.innerText = message;
            }
            
            chatMsgBox.appendChild(bubble);
            return bubble;
        }

        function scrollToBottom() {
            chatMsgBox.scrollTop = chatMsgBox.scrollHeight;
        }
    })();
</script>
