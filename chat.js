document.addEventListener('DOMContentLoaded', function() {
    const chatBtn = document.getElementById("chatToggleBtn");
    const chatBox = document.getElementById("chatBox");
    const closeChat = document.getElementById("closeChat");
    const chatBody = document.getElementById("chatBody");
    const messageInput = document.getElementById("messageInput");
    const sendButton = document.getElementById("sendMessage");

    // Open chat
    chatBtn.addEventListener("click", () => {
        chatBox.classList.add("active");
        chatBtn.style.display = "none";
        loadMessages();
    });

    // Close chat
    closeChat.addEventListener("click", () => {
        closeChatBox();
    });

    // Send message on button click
    sendButton.addEventListener("click", sendMessage);

    // Send message on Enter key (but allow new lines with Shift+Enter)
    messageInput.addEventListener("keypress", function(e) {
        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    async function sendMessage() {
        const message = messageInput.value.trim();
        if (message) {
            try {
                const formData = new FormData();
                formData.append('action', 'send');
                formData.append('message', message);

                const response = await fetch('chat_handler.php', {
                    method: 'POST',
                    body: formData
                });

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server returned non-JSON response');
                }

                const data = await response.json();
                
                if (data.success) {
                    messageInput.value = '';
                    loadMessages();
                } else {
                    console.error('Error:', data.message);
                    alert('Failed to send message: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to send message. Please try again.');
            }
        }
    }

    function loadMessages() {
        fetch('chat_handler.php?action=load')
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server returned non-JSON response');
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    console.error('Server error:', data.message);
                    return;
                }
                chatBody.innerHTML = '';
                data.messages.forEach(msg => {
                    appendMessage(msg);
                });
                scrollChatToBottom();
            })
            .catch(error => {
                console.error('Error loading messages:', error);
            });
    }

    function appendMessage(msg) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `d-flex ${msg.is_admin == 1 ? '' : 'justify-content-end'} mb-3`;
        
        messageDiv.innerHTML = `
            ${msg.is_admin == 1 ? `
                <div class="me-2">
                    <img src="admin/images/VMC School Logo.png" class="chat-avatar" alt="VMC admin" style="width: 30px; height: 30px;">
                </div>
            ` : ''}
            <div>
                <div class="chat-bubble ${msg.is_admin == 1 ? 'admin' : 'user'}">
                    ${msg.message}
                </div>
                <small class="text-muted ${msg.is_admin == 1 ? 'text-start' : 'text-end'} d-block">
                    ${msg.created_at}
                </small>
            </div>
        `;
        
        chatBody.appendChild(messageDiv);
    }

    function scrollChatToBottom() {
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    function closeChatBox() {
        chatBox.classList.remove("active");
        setTimeout(() => {
            chatBtn.style.display = "flex";
        }, 300);
    }

    // Load messages every 5 seconds
    setInterval(loadMessages, 5000);
});