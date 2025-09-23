const chatBtn = document.getElementById("chatToggleBtn");
const chatBox = document.getElementById("chatBox");
const closeChat = document.getElementById("closeChat");
const chatBody = document.getElementById("chatBody");
const textarea = document.querySelector(".text-area");
const sendButton = document.querySelector(".send-button");

// Open chat
chatBtn.addEventListener("click", () => {
  chatBox.classList.add("active");
  chatBtn.style.display = "none";
  scrollChatToBottom();
});

// Close chat (button)
closeChat.addEventListener("click", () => {
  closeChatBox();
});

// Helper: close chat
function closeChatBox() {
  chatBox.classList.remove("active");
  setTimeout(() => {
    chatBtn.style.display = "flex";
  }, 300); // matches CSS transition
}

// Scroll to bottom function
function scrollChatToBottom() {
  if (chatBody) {
    chatBody.scrollTop = chatBody.scrollHeight;
  }
}

// Auto-resize function for textarea
function autoResize(el) {
  el.style.height = "auto";
  el.style.height = el.scrollHeight + "px";
}

// Bind resize to textarea input
if (textarea) {
  textarea.addEventListener("input", () => autoResize(textarea));
}

// Example: auto-scroll when a new message is sent
if (sendButton) {
  sendButton.addEventListener("click", () => {
    scrollChatToBottom();
  });
}

// Scroll to bottom on page load
window.addEventListener("DOMContentLoaded", scrollChatToBottom);
