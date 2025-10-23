<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VMC Basket - Admin/Chats</title>
    <?php include 'links.php'; ?>
    <style>
        @media (max-width: 768px) {
            .chat-list {
                width: 100%;
            }

            .chat-window {
                width: 100%;
                display: none !important;
            }
        }

        @media (max-width: 991.98px) {
            .chat-window.show {
                display: flex !important;
            }

            .chat-list.hide {
                display: none !important;
            }
        }

        .hide {
            display: none !important;
        }

        /* Add these styles in the existing <style> tag
        .small-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
        }

        .chat-bubble {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 15px;
            margin-bottom: 2px;
        }

        .chat-bubble.user {
            background-color: #e5e5e5;
            border-bottom-left-radius: 5px;
        }

        .chat-bubble.admin {
            background-color: #2c3e91;
            color: white;
            border-bottom-right-radius: 500px;
        } */
    </style>
</head>

<body>

    <?php
    session_start();
    require 'inc/config.php';
    date_default_timezone_set('Asia/Manila');
    // Fetch all unique users who have sent messages
    $query = "SELECT 
    i.user_id, 
    u.student_fname, 
    u.student_lname, 
    u.year_level, 
    u.photo,
    (
        SELECT COUNT(*) FROM inquiries 
        WHERE user_id = i.user_id AND sender = 'user' AND is_read = 0
    ) AS unread_count,
    (
        SELECT message FROM inquiries 
        WHERE user_id = i.user_id 
        ORDER BY created_at DESC LIMIT 1
    ) AS last_message
FROM inquiries i
JOIN users u ON i.user_id = u.id
WHERE i.sender = 'user'
GROUP BY i.user_id
ORDER BY 
    (SELECT created_at FROM inquiries WHERE user_id = i.user_id ORDER BY created_at DESC LIMIT 1) DESC";
    $result = mysqli_query($conn, $query);

    $firstUserId = null;
    if ($row = mysqli_fetch_assoc($result)) {
        $firstUserId = $row['user_id'];
        // Rewind the result pointer so the while loop below works
        mysqli_data_seek($result, 0);
    }
    ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Top Navbar (visible only on small devices) -->
            <nav class="navbar navbar-light bg-light d-md-none">
                <div class="container-fluid d-flex justify-content-between align-items-center">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu"
                        aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <img src="images/vmc_basket_logo.png" alt="VMC Logo" class="vmc-logo img-fluid">
                </div>
            </nav>

            <!-- Sidebar -->
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-white sidebar collapse">

                <div class="text-center py-3 d-none d-md-block">
                    <img src="images/vmc_basket_logo.png" alt="VMC Logo" class="vmc-logo img-fluid">
                </div>

                <ul class="nav flex-column px-2 mb-3 mt-4 mt-md-0">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link">
                            <i class="bi bi-house-door me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="orders.php" class="nav-link">
                            <i class="bi bi-bag-check me-2"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="prod.php" class="nav-link">
                            <i class="bi bi-box-seam me-2"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="cus.php" class="nav-link">
                            <i class="bi bi-people me-2"></i> Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="chat.php" class="nav-link active">
                            <i class="bi bi-chat-dots me-2"></i> Chat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="ratings.php" class="nav-link">
                            <i class="bi bi-list-stars me-2"></i> Ratings & Reviews
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="accounting.php" class="nav-link">
                            <i class="bi bi-receipt me-2"></i> Receipt Form
                        </a>
                    </li>
                    <!-- Logout for small screens (visible only on xs/sm) -->
                    <li class="nav-item d-block d-md-none">
                        <a href="logout.php" class="nav-link text-danger fw-semibold">
                            <i class="bi bi-box-arrow-right me-2"></i> Log Out
                        </a>
                    </li>
                </ul>
                <!-- Logout at the bottom for md/lg screens -->
                <div class="position-absolute w-100 d-none d-md-block" style="bottom: 30px; left: 0;">
                    <ul class="nav flex-column px-2">
                        <li class="nav-item">
                            <a href="logout.php" class="nav-link text-danger fw-semibold">
                                <i class="bi bi-box-arrow-right me-2"></i> Log Out
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Title Page and Search -->
            <main class="col-md-9 ms-sm-auto col-lg-10 content p-3">
                <div class="d-flex justify-content-end mb-5"> </div>
                <div class="mt-2 mb-3">
                    <h2>Chats</h2>
                </div>

                <!-- Main Chat Section -->
                <div class="container-fluid chat-section">
                    <div class="row">
                        <!-- Left Sidebar: Chat List -->
                        <div class="col-md-4 col-lg-4 chat-list p-3 d-sm-block" id="chatList">
                                                     
                            <!-- Search -->
                            <div class="mb-3 search-chat">
                                <input type="text" class="form-control" id="searchInput" placeholder="Search Message">
                                <button><i class="bi bi-search"></i></button>
                            </div>
                            
                            <!-- Filters -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <button class="chat-btn active" id="allBtn" data-filter="all">All</button>
                                    <button class="chat-btn" id="unreadBtn" data-filter="unread">Unread</button>
                                </div>
                            </div>

                            <!-- Chat Users List -->
                            <ul class="list-group chat-users" id="chatUsersList">
                                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                    <li class="list-group-item d-flex align-items-center"
                                        data-user-id="<?php echo $row['user_id']; ?>">
                                        <div class="me-2 position-relative">
                                            <img src="<?php echo $row['photo'] ? './uploads/' . $row['photo'] : './images/profile_pic.png'; ?>"
                                                class="chat-avatar" alt="Profile Picture">
                                            <?php if ($row['unread_count'] > 0) { ?>
                                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                                    <?php echo $row['unread_count']; ?>
                                                </span>
                                            <?php } ?>
                                        </div>
                                        <div>
                                            <strong><?php echo $row['student_fname'] . ' ' . $row['student_lname']; ?></strong>
                                            <div class="small text-muted">
                                                <?php echo substr($row['last_message'], 0, 30) . '...'; ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>

                        <!-- Right Chat Window -->
                        <div class="col-md-8 col-lg-8 chat-window d-flex flex-column" id="chatWindow">
                            <!-- Chat Header -->
                            <div class="chat-header d-flex justify-content-between align-items-center px-3 py-2">
                                <div class="d-flex align-items-center">
                                    <button class="chat-button me-2" id="toggleSidebarBtn">
                                        <i class="bi bi-arrow-bar-left" id="toggleSidebarIcon"></i>
                                    </button>
                                    <div class="d-flex align-items-center" id="selectedUserInfo">
                                        <!-- This will be populated dynamically -->
                                        <div class="me-2">
                                            <img src="./images/profile_pic.png" class="chat-avatar"
                                                alt="Profile Picture" id="headerUserImage">
                                        </div>
                                        <div>
                                            <h6 class="mb-0" id="headerUserName">Select a chat</h6>
                                            <small class="text-white" id="headerUserDetails">-</small>
                                        </div>
                                    </div>
                                </div>
                                <button class="chat-button" data-bs-toggle="modal" data-bs-target="#studentInfoModal">
                                    <i class="bi bi-info-circle"></i>
                                </button>

                                <!-- Student Info Modal -->

                                <div class="modal fade" id="studentInfoModal" tabindex="-1"
                                    aria-labelledby="studentInfoModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="studentInfoModalLabel">Student Information
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <img id="modalUserImage" src="./images/profile_pic.png"
                                                    class="chat-profile mb-3" alt="Profile Picture">
                                                <h4 class="mb-1" id="modalUserName">Select a student</h4>
                                                <p class="mb-1"><strong>School ID:</strong> <span
                                                        id="modalStudentId">-</span></p>
                                                <p class="mb-1"><strong>Year Level/Course:</strong> <span
                                                        id="modalYearLevel">-</span></p>
                                                <p class="mb-1"><strong>Email:</strong> <span id="modalEmail">-</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Chat Messages -->

                            <div class="chat-body flex-grow-1 p-3 overflow-auto" id="chatBody">
                                <!-- Messages will be loaded here dynamically -->
                            </div>

                            <!-- Chat Input -->
                            <div class="chat-footer d-flex align-items-center p-3 border-top">
                                <textarea class="form-control text-area me-2" rows="2"
                                    placeholder="Type your reply here..." oninput="autoResize(this)"></textarea>
                                <button class="send-button btn btn-primary"><i class="bi bi-send-fill"></i></button>
                            </div>
                        </div>
                    </div>
            </main>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('searchInput');
                const chatUsersList = document.getElementById('chatUsersList');
                const allBtn = document.getElementById('allBtn');
                const unreadBtn = document.getElementById('unreadBtn');
                
                // Store original list items for filtering
                const originalItems = Array.from(chatUsersList.getElementsByTagName('li'));
                
                // Search functionality
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    filterChats(searchTerm, getCurrentFilter());
                });
                
                // Filter functionality
                allBtn.addEventListener('click', function() {
                    setActiveFilter(this);
                    filterChats(searchInput.value.toLowerCase(), 'all');
                });
                
                unreadBtn.addEventListener('click', function() {
                    setActiveFilter(this);
                    filterChats(searchInput.value.toLowerCase(), 'unread');
                });
                
                function getCurrentFilter() {
                    const activeButton = document.querySelector('.chat-btn.active');
                    return activeButton.getAttribute('data-filter');
                }
                
                function setActiveFilter(button) {
                    document.querySelectorAll('.chat-btn').forEach(btn => {
                        btn.classList.remove('active');
                    });
                    button.classList.add('active');
                }
                

                function filterChats(searchTerm, filterType) {
                    let hasVisibleItems = false;

                    originalItems.forEach(item => {
                        const userName = item.querySelector('strong').textContent.toLowerCase();
                        const lastMessage = item.querySelector('.small.text-muted').textContent.toLowerCase();
                        // Check for unread badge (unread_count > 0)
                        const hasUnreadBadge = item.querySelector('.badge.bg-danger') !== null;

                        // Only show if matches search AND matches filter
                        const matchesSearch = userName.includes(searchTerm) || lastMessage.includes(searchTerm);
                        let matchesFilter = true;
                        if (filterType === 'unread') {
                            matchesFilter = hasUnreadBadge;
                        }

                        if (matchesSearch && matchesFilter) {
                            item.style.display = '';
                            hasVisibleItems = true;
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    // Show/hide the chatUsersList <ul>
                    chatUsersList.style.display = hasVisibleItems ? '' : 'none';
                    showNoResults(!hasVisibleItems);
                }
                
                function showNoResults(show) {
                    let noResultsEl = document.getElementById('noResults');
                    const chatList = document.getElementById('chatList');

                    if (show) {
                        if (!noResultsEl) {
                            noResultsEl = document.createElement('div');
                            noResultsEl.id = 'noResults';
                            noResultsEl.className = 'text-center p-4';
                            noResultsEl.innerHTML = `
                                <div class="text-muted">
                                    <i class="bi bi-search fs-4 mb-2"></i>
                                    <p class="mb-0">No messages found</p>
                                </div>
                            `;
                            chatList.appendChild(noResultsEl);
                        }
                        noResultsEl.style.display = 'block';
                    } else if (noResultsEl) {
                        noResultsEl.style.display = 'none';
                    }
                }
                
                // Add these styles to the existing style element
                const additionalStyles = `
                    #noResults {
                        background-color: #f8f9fa;
                        border-radius: 8px;
                        margin: 1rem 0;
                    }
                    
                    .chat-users:empty {
                        display: none;
                    }
                    
                    #searchInput {
                        transition: all 0.3s ease;
                    }
                    
                    #searchInput:focus {
                        box-shadow: 0 0 0 0.2rem rgba(44, 62, 145, 0.25);
                        border-color: #2c3e91;
                    }
                `;
                
                // Add the additional styles
                style.textContent += additionalStyles;
            });
            
            // Add CSS styles
            const style = document.createElement('style');
            style.textContent = `
                .chat-btn {
                    padding: 5px 15px;
                    border: 1px solid #dee2e6;
                    background: transparent;
                    border-radius: 20px;
                    margin-right: 5px;
                    transition: all 0.3s ease;
                }
                
                .chat-btn.active {
                    background: #2c3e91;
                    color: white;
                    border-color: #2c3e91;
                }
                
                .search-chat {
                    position: relative;
                }
                
                .search-chat button {
                    position: absolute;
                    right: 10px;
                    top: 50%;
                    transform: translateY(-50%);
                    border: none;
                    background: transparent;
                    color: #6c757d;
                }
                
                .search-chat input {
                    padding-right: 40px;
                    border-radius: 20px;
                }
            `;
            document.head.appendChild(style);
            // Change dropdown button text when filter is selected
            document.querySelectorAll('.dropdown-item[data-subject]').forEach(function (item) {
                item.addEventListener('click', function (e) {
                    e.preventDefault();
                    const subject = this.textContent;
                    document.getElementById('filterDropdownText').textContent = subject;
                    // Filtering logic (already present)
                    document.querySelectorAll('.chat-users li').forEach(function (chat) {
                        if (this.getAttribute('data-subject') === 'All' || chat.querySelector('.small').textContent.includes(this.getAttribute('data-subject'))) {
                            chat.style.display = '';
                        } else {
                            chat.style.display = 'none';
                        }
                    }.bind(this));
                });
            });

            // Highlight selected chat user
            document.querySelectorAll('.chat-users .list-group-item').forEach(function (item) {
                item.addEventListener('click', function () {
                    document.querySelectorAll('.chat-users .list-group-item').forEach(function (li) {
                        li.classList.remove('active');
                    });
                    this.classList.add('active');
                });
            });

            // auto-resize Message Textarea
            function autoResize(textarea) {
                textarea.style.height = 'auto';
                let newHeight = Math.min(textarea.scrollHeight, 120);
                textarea.style.height = newHeight + 'px';
            }

            // Toggle Sidebar
            document.getElementById('toggleSidebarBtn').addEventListener('click', function () {
                var chatList = document.getElementById('chatList');
                var chatWindow = document.querySelector('.chat-window');
                var icon = document.getElementById('toggleSidebarIcon');
                // Toggle hide/show for chatList and chatWindow
                chatList.classList.toggle('hide');
                chatWindow.classList.toggle('show');
                if (chatList.classList.contains('hide')) {
                    chatWindow.classList.remove('col-md-8', 'col-lg-8');
                    chatWindow.classList.add('w-100');
                    icon.classList.remove('bi-arrow-bar-left');
                    icon.classList.add('bi-arrow-bar-right');
                } else {
                    chatWindow.classList.remove('w-100');
                    chatWindow.classList.add('col-md-8', 'col-lg-8');
                    icon.classList.remove('bi-arrow-bar-right');
                    icon.classList.add('bi-arrow-bar-left');
                }
            });

            document.addEventListener('DOMContentLoaded', function () {
                // For tablet/phone: show chat window when user is clicked
                function isMobileView() {
                    return window.innerWidth <= 768; // Bootstrap lg breakpoint
                }
                var chatUsers = document.querySelectorAll('#chatUsersList .list-group-item');
                chatUsers.forEach(function (item) {
                    item.addEventListener('click', function () {
                        if (isMobileView()) {
                            document.getElementById('chatWindow').classList.add('show');
                            document.getElementById('chatList').classList.add('hide');
                        }
                    });
                });
                // Optionally, add a back button in chat window for mobile
                var toggleSidebarBtn = document.getElementById('toggleSidebarBtn');
                toggleSidebarBtn.addEventListener('click', function () {
                    if (isMobileView()) {
                        document.getElementById('chatWindow').classList.remove('show');
                        document.getElementById('chatList').classList.remove('hide');
                    }
                    var toggleSidebarBtn = document.getElementById('toggleSidebarBtn');
                    toggleSidebarBtn.addEventListener('click', function () {
                        if (isMobileView()) {
                            document.getElementById('chatWindow').classList.remove('show');
                            document.getElementById('chatList').classList.remove('hide');
                        }
                        // On desktop, toggle works via the main handler above
                    });
                });
            });

            // New chat message placed at the bottom
            function addChatMessage(html) {
                const chatBody = document.getElementById('chatBody');
                chatBody.insertAdjacentHTML('beforeend', html);
                chatBody.scrollTop = chatBody.scrollHeight; // Always scroll to bottom (which is visually the bottom)
            }

            // On small devices, resize chat window to mobile size and fix chat-footer
            function adjustChatLayout() {
                const chatWindow = document.getElementById('chatWindow');
                const chatBody = document.getElementById('chatBody');
                const chatFooter = chatWindow.querySelector('.chat-footer');
                const chatHeader = chatWindow.querySelector('.chat-header');
                if (window.innerWidth <= 576) {
                    chatWindow.style.position = 'fixed';
                    chatWindow.style.top = '0';
                    chatWindow.style.left = '0';
                    chatWindow.style.width = '100vw';
                    chatWindow.style.height = '100vh';
                    chatWindow.style.zIndex = '100';
                    chatWindow.style.background = '#fff';
                    chatFooter.style.position = 'fixed';
                    chatFooter.style.bottom = '0';
                    chatFooter.style.left = '0';
                    chatFooter.style.right = '0';
                    chatFooter.style.zIndex = '10';
                    chatFooter.style.background = '#fff';
                    chatFooter.style.width = '100%';
                    chatFooter.style.boxShadow = '0 -2px 8px rgba(0,0,0,0.05)';
                    // Ensure chatBody is above chatFooter and not hidden
                    chatBody.style.marginBottom = chatFooter.offsetHeight + 'px';
                    chatBody.style.overflowY = 'auto';
                    chatBody.style.height = (window.innerHeight - chatHeader.offsetHeight - chatFooter.offsetHeight) + 'px';
                } else {
                    chatWindow.style.position = '';
                    chatWindow.style.top = '';
                    chatWindow.style.left = '';
                    chatWindow.style.width = '';
                    chatWindow.style.height = '';
                    chatWindow.style.zIndex = '';
                    chatWindow.style.background = '';
                    chatFooter.style.position = '';
                    chatFooter.style.bottom = '';
                    chatFooter.style.left = '';
                    chatFooter.style.right = '';
                    chatFooter.style.zIndex = '';
                    chatFooter.style.background = '';
                    chatFooter.style.width = '';
                    chatFooter.style.boxShadow = '';
                    chatBody.style.marginBottom = '';
                    chatBody.style.overflowY = '';
                    chatBody.style.height = '';
                }
            }
            window.addEventListener('resize', adjustChatLayout);
            document.addEventListener('DOMContentLoaded', adjustChatLayout);

            let currentUserId = null;

            // Load messages for selected user
            function loadMessages(userId) {
                if (!userId) return;
                currentUserId = userId;

                fetch(`get_user_details.php?user_id=${userId}`)
                    .then(response => response.json())
                    .then(user => {
                        // Update header with user details
                        document.getElementById('headerUserName').textContent = user.student_fname + ' ' + user.student_lname;
                        document.getElementById('headerUserDetails').textContent = user.year_level;
                        document.getElementById('headerUserImage').src = user.photo ? './uploads/' + user.photo : './images/profile_pic.png';

                        // Update modal user info
                        document.getElementById('modalUserImage').src = user.photo ? './uploads/' + user.photo : './images/profile_pic.png';
                        document.getElementById('modalUserName').textContent = user.student_fname + ' ' + user.student_lname;
                        document.getElementById('modalStudentId').textContent = user.student_no;
                        document.getElementById('modalYearLevel').textContent = user.year_level;
                        document.getElementById('modalEmail').textContent = user.email;
                    })
                    .catch(error => console.error('Error loading user details:', error));

                // Then fetch messages
                fetch(`get_messages.php?user_id=${userId}`)
                    .then(response => response.json())
                    .then(data => {
                        const chatBody = document.getElementById('chatBody');
                        chatBody.innerHTML = '';
                        
                        // Make sure we're working with an array
                        const messages = Array.isArray(data.messages) ? data.messages : [];
                        
                        // Create a container for messages
                        const messagesContainer = document.createElement('div');
                        messagesContainer.className = 'd-flex flex-column';
                        
                        // Add messages in chronological order
                        messages.forEach(message => {
                            const isAdmin = message.sender === 'admin';
                            const html = `
                                <div class="d-flex mb-3 ${isAdmin ? 'justify-content-end' : 'justify-content-start'}">
                                    ${!isAdmin ? `
                                        <div class="me-2">
                                            <img src="./uploads/${message.photo || 'profile_pic.png'}" 
                                                 class="chat-avatar" alt="User Profile">
                                        </div>
                                    ` : ''}
                                    <div class="chat-message">
                                        <div class="chat-bubble ${isAdmin ? 'admin' : 'user'}">
                                            ${message.message}
                                        </div>
                                        <small class="chat-timestamp text-muted ${isAdmin ? 'text-end' : 'text-start'}">
                                            ${message.created_at}
                                        </small>
                                    </div>
                                    ${isAdmin ? `
                                        <div class="ms-2">
                                            <img src="./images/VMC School Logo.png" 
                                                 class="chat-avatar" alt="Admin Profile">
                                        </div>
                                    ` : ''}
                                </div>
                            `;
                            messagesContainer.insertAdjacentHTML('beforeend', html);
                        });
                        
                        // Add the messages container to the chat body
                        chatBody.appendChild(messagesContainer);
                        
                        // Scroll to bottom
                        chatBody.scrollTop = chatBody.scrollHeight;
                    })
                    .catch(error => console.error('Error loading messages:', error));
            }
            
            
            // Handle message sending
            document.querySelector('.send-button').addEventListener('click', function () {
                sendMessage();
            });

            // Allow sending with Enter key (Shift+Enter for new line)
            document.querySelector('.text-area').addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            function sendMessage() {
                if (!currentUserId) return;

                const textarea = document.querySelector('.text-area');
                const message = textarea.value.trim();

                if (message) {
                    const formData = new FormData();
                    formData.append('user_id', currentUserId);
                    formData.append('message', message);

                    fetch('send_message.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                textarea.value = '';
                                textarea.style.height = 'auto';
                                loadMessages(currentUserId);
                            }
                        });
                }
            }

            // Add click event to chat users
            document.querySelectorAll('.chat-users .list-group-item').forEach(item => {
                item.addEventListener('click', function () {
                    const userId = this.dataset.userId;
                    loadMessages(userId);

                    // Update UI to show selected user
                    document.querySelectorAll('.chat-users .list-group-item').forEach(li => {
                        li.classList.remove('active');
                    });
                    this.classList.add('active');
                });
            });

            // Auto refresh messages every 5 seconds
            setInterval(() => {
                if (currentUserId) {
                    loadMessages(currentUserId);
                }
            }, 5000);

            document.addEventListener('DOMContentLoaded', function () {
                // For tablet/phone: show chat window when user is clicked
                function isMobileView() {
                    return window.innerWidth <= 768; // Bootstrap lg breakpoint
                }
                var chatUsers = document.querySelectorAll('#chatUsersList .list-group-item');
                chatUsers.forEach(function (item) {
                    item.addEventListener('click', function () {
                        if (isMobileView()) {
                            document.getElementById('chatWindow').classList.add('show');
                            document.getElementById('chatList').classList.add('hide');
                        }
                    });
                });
                // Optionally, add a back button in chat window for mobile
                var toggleSidebarBtn = document.getElementById('toggleSidebarBtn');
                toggleSidebarBtn.addEventListener('click', function () {
                    if (isMobileView()) {
                        document.getElementById('chatWindow').classList.remove('show');
                        document.getElementById('chatList').classList.remove('hide');
                    }
                    var toggleSidebarBtn = document.getElementById('toggleSidebarBtn');
                    toggleSidebarBtn.addEventListener('click', function () {
                        if (isMobileView()) {
                            document.getElementById('chatWindow').classList.remove('show');
                            document.getElementById('chatList').classList.remove('hide');
                        }
                        // On desktop, toggle works via the main handler above
                    });
                });
            });

                       
            const selectedUserInfo = document.getElementById('selectedUserInfo');
            if (!currentUserId) {
                selectedUserInfo.querySelector('#headerUserName').textContent = 'Select a chat';
                selectedUserInfo.querySelector('#headerUserDetails').textContent = '-';
                document.getElementById('headerUserImage').src = './images/profile_pic.png';
            }
       
       
    
    var firstUserId = <?php echo $firstUserId ? json_encode($firstUserId) : 'null'; ?>;
                        
            document.addEventListener('DOMContentLoaded', function () {
                if (firstUserId) {
                    // Highlight the first user in the list
                    const firstUserLi = document.querySelector('.chat-users .list-group-item[data-user-id="' + firstUserId + '"]');
                    if (firstUserLi) {
                        firstUserLi.classList.add('active');
                    }
                    // Load messages for the first user
                    loadMessages(firstUserId);
                }
            });
           
        </script>


</body>

</html>
