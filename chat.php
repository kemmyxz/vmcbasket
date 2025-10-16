<?php 
include 'links.php';


// Add debugging information
error_reporting(E_ALL);
ini_set('display_errors', 1);



?>

<!-- Floating Chat Button -->
<button id="chatToggleBtn" class="chat-btn">
    <i class="bi bi-chat-dots-fill"></i>
</button>

<!-- Chat Box -->
<div id="chatBox" class="chat-box shadow-lg d-flex flex-column">
    <!-- Chat Header -->
    <div class="chat-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <div class="me-2">
                <img src="admin/images/VMC School Logo.png" class="chat-avatar" alt="VMC admin">
            </div>
            <div>
                <h6 class="mb-0">VMC Admin</h6>
                <small class="status-text">School Hours</small>
            </div>
        </div>
        <div data-bs-theme="dark">
            <button class="btn btn-sm btn-close text-white" id="closeChat"></button>
        </div>
    </div>

    <!-- Chat Body -->
    <div class="chat-body p-3 flex-grow-1 overflow-auto" id="chatBody">
        <!-- Messages will be loaded here dynamically -->
    </div>

    <!-- Chat Footer -->
    <div class="chat-footer d-flex align-items-center p-3 border-top">
        <textarea id="messageInput" class="form-control text-area me-2" rows="2" placeholder="Send Message.."></textarea>
        <button id="sendMessage" class="send-button"><i class="bi bi-send-fill"></i></button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="chat.js"></script>

<script>
let chatInitialized = false;

function chatInit() {
    if (chatInitialized) return;
    
    const chatToggleBtn = document.getElementById('chatToggleBtn');
    const chatBox = document.getElementById('chatBox');
    const closeChat = document.getElementById('closeChat');
    
    chatToggleBtn?.addEventListener('click', () => {
        chatBox.classList.toggle('show');
        loadChatMessages(); // Load messages when chat is opened
    });
    
    closeChat?.addEventListener('click', () => {
        chatBox.classList.remove('show');
    });
    
    chatInitialized = true;
    console.log('Chat initialized');
}

function checkNewChatMessages() {
    if (!document.getElementById('chatBox').classList.contains('show')) {
        return; // Don't check if chat is closed
    }
    loadChatMessages();
}

function loadChatMessages() {
    // Fetch messages from server
    fetch('get_messages.php')
        .then(response => response.json())
        .then(messages => {
            const chatBody = document.getElementById('chatBody');
            // Update chat messages
            // ... message display logic ...
        })
        .catch(error => console.error('Error loading messages:', error));
}

// Initialize chat when document is ready
document.addEventListener('DOMContentLoaded', chatInit);
</script>

