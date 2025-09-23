<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VMC Basket - Admin/Products</title>
    <?php include 'links.php'; ?>
    <style>
        @media (max-width: 768px) {
            .chat-list{
                width: 100%;
            }
            .chat-window{
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
    </style>
</head>
<body>

<div class="container-fluid" >
        <div class="row">
        <!-- Top Navbar (visible only on small devices) -->
            <nav class="navbar navbar-light bg-light d-md-none">
                <div class="container-fluid d-flex justify-content-between align-items-center">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
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
            <main class="col-md-9 ms-sm-auto col-lg-10 content p-5 content">
                <div class="mt-2 d-flex flex-row align-items-center">
                    <h2 class="mb-0">Chats</h2>
                </div>

                <!-- Main Chat Section -->
                <div class="container-fluid chat-section">
                    <div class="row">
                        <!-- Left Sidebar: Chat List -->
                        <div class="col-md-4 col-lg-4 chat-list p-3 d-sm-block" id="chatList">
                            <!-- Search -->
                            <div class="mb-3 search-chat">
                                <input type="text" class="form-control" placeholder="Search Message">
                                <button><i class="bi bi-search"></i></button>
                            </div>
                            
                            <!-- Filters -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <button class="chat-btn active" id="allBtn">All</button>
                                    <button class="chat-btn" id="unreadBtn">Unread</button>
                                </div>
                                <div class="dropdown">
                                    <button class="chat-btn dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span id="filterDropdownText">Filter</span> <i class="bi bi-filter"></i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                                        <li><a class="dropdown-item" data-subject="All">All Subjects</a></li>
                                        <li><a class="dropdown-item" data-subject="Inquiry">Inquiry</a></li>
                                        <li><a class="dropdown-item" data-subject="Order Status">Order Status</a></li>
                                        <li><a class="dropdown-item" data-subject="Return/Refund">Return/Refund</a></li>
                                        <li><a class="dropdown-item" data-subject="Cancel Order">Cancel Order</a></li>
                                        <li><a class="dropdown-item" data-subject="Payment Issue">Payment Issue</a></li>
                                        <li><a class="dropdown-item" data-subject="Complain">Complain</a></li>
                                        <li><a class="dropdown-item" data-subject="Others">Others</a></li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Chat Users List -->
                            <ul class="list-group chat-users" id="chatUsersList">
                                <li class="list-group-item d-flex align-items-center active">
                                    <div class="me-2">
                                        <img src="images/profile_pic.png" class="chat-avatar" alt="Profile Picture">
                                    </div>
                                    <div>
                                        <strong>Name</strong>
                                        <div class="small text-muted">Subject: Complain</div>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                   <div class="me-2">
                                        <img src="images/profile_pic.png" class="chat-avatar" alt="Profile Picture">
                                    </div>
                                    <div>
                                        <strong>Name</strong>
                                        <div class="small text-muted">Subject: Complain</div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <!-- Right Chat Window -->
                        <div class="col-md-8 col-lg-8 chat-window d-flex flex-column" id="chatWindow">
                            <!-- Chat Header -->
                            <div class="chat-header d-flex justify-content-between align-items-center px-3 py-2">
                                <div class="d-flex align-items-center">
                                <button class="chat-button me-2" id="toggleSidebarBtn"><i class="bi bi-arrow-bar-left" id="toggleSidebarIcon"></i></button>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <img src="images/profile_pic.png" class="chat-avatar" alt="Profile Picture">
                                    </div>
                                    <div>
                                    <h6 class="mb-0">Fatima Balderas</h6>
                                    <small class="text-light">Year-Level/Course</small>
                                    </div>
                                </div>
                                </div>
                                <!-- Info Button triggers modal -->
                                <button class="chat-button" data-bs-toggle="modal" data-bs-target="#studentInfoModal">
                                    <i class="bi bi-info-circle"></i>
                                </button>

                                <!-- Student Info Modal -->
                                <div class="modal fade" id="studentInfoModal" tabindex="-1" aria-labelledby="studentInfoModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="studentInfoModalLabel">Student Information</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <img src="images/profile_pic.png" alt="Profile Picture" class="rounded-circle mb-3" width="120" height="120">
                                                <h4 class="mb-1">Fatima Balderas</h4>
                                                <p class="mb-1"><strong>School ID:</strong> 2023123456</p>
                                                <p class="mb-1"><strong>Year Level/Course:</strong> BSIT</p>
                                                <p class="mb-1"><strong>Email:</strong> fatima.balderas@email.com</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Chat Messages -->
                            <div class="chat-body flex-grow-1 p-3 overflow-auto d-flex flex-column-reverse" id="chatBody">
                                <!-- Outgoing -->
                                <div class="d-flex justify-content-end mb-3">
                                    <div>
                                        <div class="chat-bubble admin">Sure, what is the issue?</div>
                                        <small class="text-muted d-block text-end">06/08/2025 6:00 pm</small>
                                    </div>
                                </div>

                                <!-- Incoming -->
                                <div class="d-flex mb-3">
                                    <div class="me-2">
                                        <img src="images/profile_pic.png" class="chat-avatar" alt="Profile Picture">
                                    </div>
                                    <div>
                                        <div class="chat-bubble user">I have a complaint...</div>
                                        <small class="text-muted">06/08/2025 6:00 pm</small>
                                    </div>
                                </div>
                            </div>

                        <!-- Chat Input -->
                        <div class="chat-footer d-flex align-items-center p-3 border-top">
                            <textarea class="form-control text-area me-2" rows="2" placeholder="Send Message.." oninput="autoResize(this)"></textarea>
                            <button class="send-button"><i class="bi bi-send-fill"></i></button>
                        </div>  
                    </div>
                </div>
            </main>
        </div>

<script>
    // Ensure only one filter button is active at a time
    document.addEventListener('DOMContentLoaded', function() {
        const allBtn = document.getElementById('allBtn');
        const unreadBtn = document.getElementById('unreadBtn');
        allBtn.classList.add('active');
        allBtn.addEventListener('click', function() {
            allBtn.classList.add('active');
            unreadBtn.classList.remove('active');
        });
        unreadBtn.addEventListener('click', function() {
            unreadBtn.classList.add('active');
            allBtn.classList.remove('active');
        });
    });

    // Change dropdown button text when filter is selected
    document.querySelectorAll('.dropdown-item[data-subject]').forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const subject = this.textContent;
            document.getElementById('filterDropdownText').textContent = subject;
            // Filtering logic (already present)
            document.querySelectorAll('.chat-users li').forEach(function(chat) {
                if (this.getAttribute('data-subject') === 'All' || chat.querySelector('.small').textContent.includes(this.getAttribute('data-subject'))) {
                    chat.style.display = '';
                } else {
                    chat.style.display = 'none';
                }
            }.bind(this));
        });
    });

    // Highlight selected chat user
    document.querySelectorAll('.chat-users .list-group-item').forEach(function(item) {
        item.addEventListener('click', function() {
            document.querySelectorAll('.chat-users .list-group-item').forEach(function(li) {
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
    document.getElementById('toggleSidebarBtn').addEventListener('click', function() {
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

    document.addEventListener('DOMContentLoaded', function() {
    // For tablet/phone: show chat window when user is clicked
    function isMobileView() {
        return window.innerWidth <= 768; // Bootstrap lg breakpoint
    }
    var chatUsers = document.querySelectorAll('#chatUsersList .list-group-item');
    chatUsers.forEach(function(item) {
        item.addEventListener('click', function() {
            if (isMobileView()) {
                document.getElementById('chatWindow').classList.add('show');
                document.getElementById('chatList').classList.add('hide');
            }
        });
    });
    // Optionally, add a back button in chat window for mobile
    var toggleSidebarBtn = document.getElementById('toggleSidebarBtn');
    toggleSidebarBtn.addEventListener('click', function() {
        if (isMobileView()) {
            document.getElementById('chatWindow').classList.remove('show');
            document.getElementById('chatList').classList.remove('hide');
        }
    var toggleSidebarBtn = document.getElementById('toggleSidebarBtn');
    toggleSidebarBtn.addEventListener('click', function() {
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

</script>

</body>
</html>