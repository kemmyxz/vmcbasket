<?php 
include 'links.php';
session_start();

// Add debugging information
error_reporting(E_ALL);
ini_set('display_errors', 1);



$user_id = $_SESSION['user_id'];

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

