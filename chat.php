<?php include 'links.php'; ?>

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
        <small>School Hours</small>
      </div>
    </div>
    <div data-bs-theme="dark">
      <button class="btn btn-sm btn-close text-white" id="closeChat"></button>
    </div>
  </div>

  <!-- Chat Body -->
  <div class="chat-body p-3 flex-grow-1 overflow-auto" id="chatBody">
    <!-- Example messages -->
    <div class="d-flex mb-4">
      <div class="me-2">
        <img src="admin/images/VMC School Logo.png" class="chat-avatar" alt="VMC admin">
      </div>
      <div>
        <div class="chat-bubble admin">Hello! How can I assist you today?</div>
        <small class="text-muted d-block text-end">06/08/2025 6:00 pm</small>
      </div>
    </div>

    <div class="d-flex justify-content-end mb-4">
      <div>
        <div class="chat-bubble user">I have a complaint...</div>
        <small class="text-muted">06/08/2025 6:00 pm</small>
      </div>
    </div>
  </div>

  <!-- Chat Footer -->
  <div class="chat-footer d-flex align-items-center p-3 border-top">
    <textarea class="form-control text-area me-2" rows="2" placeholder="Send Message.."></textarea>
    <button class="send-button"><i class="bi bi-send-fill"></i></button>
  </div>
</div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="chat.js"></script>