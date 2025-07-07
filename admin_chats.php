<?php
require_once 'config.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$adminId = $_SESSION['user_id'];

// Get waiting conversations
$waitingConversations = $pdo->query("
    SELECT c.id, u.full_name as user_name, c.created_at 
    FROM admin_chat_conversations c
    JOIN users u ON c.user_id = u.id
    WHERE c.is_active = 1 AND c.admin_id IS NULL
    ORDER BY c.created_at ASC
")->fetchAll();

// Get active conversations for this admin
$activeConversations = $pdo->prepare("
    SELECT c.id, u.full_name as user_name, c.created_at,
           (SELECT COUNT(*) FROM admin_chat_messages m 
            WHERE m.conversation_id = c.id AND m.sender_role = 'patient' AND m.is_read = FALSE) as unread_count
    FROM admin_chat_conversations c
    JOIN users u ON c.user_id = u.id
    WHERE c.is_active = 1 AND c.admin_id = ?
    ORDER BY c.created_at ASC
");
$activeConversations->execute([$adminId]);
$activeConversations = $activeConversations->fetchAll();

// Get selected conversation
$selectedConversationId = $_GET['conversation_id'] ?? null;
$selectedConversation = null;
$messages = [];

if ($selectedConversationId) {
    // Verify admin has access to this conversation
    $stmt = $pdo->prepare("
        SELECT c.*, u.full_name as user_name 
        FROM admin_chat_conversations c
        JOIN users u ON c.user_id = u.id
        WHERE c.id = ? AND (c.admin_id IS NULL OR c.admin_id = ?)
    ");
    $stmt->execute([$selectedConversationId, $adminId]);
    $selectedConversation = $stmt->fetch();
    
    if ($selectedConversation) {
        // If conversation has no admin, assign to current admin
        if (!$selectedConversation['admin_id']) {
            $pdo->prepare("UPDATE admin_chat_conversations SET admin_id = ? WHERE id = ?")
                ->execute([$adminId, $selectedConversationId]);
            $selectedConversation['admin_id'] = $adminId;
        }
        
        // Get messages
        $stmt = $pdo->prepare("
            SELECT m.*, u.full_name as sender_name 
            FROM admin_chat_messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.conversation_id = ? 
            ORDER BY m.sent_at ASC
        ");
        $stmt->execute([$selectedConversationId]);
        $messages = $stmt->fetchAll();
        
        // Mark messages as read
        $pdo->prepare("
            UPDATE admin_chat_messages 
            SET is_read = TRUE 
            WHERE conversation_id = ? AND sender_role = 'patient'
        ")->execute([$selectedConversationId]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>NeuroAid - Admin Chat</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
  <style>
    :root {
      --primary: #1D4C43;
      --white: #ffffff;
      --light-bg: #f8f9fa;
      --gray: #ced4da;
      --text-dark: #212529;
      --shadow: rgba(0, 0, 0, 0.08);
      --highlight: #31A06A;
      --hover-bg: #D3E2D9;
      --hover-text: #228C3E;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
    }

    body {
      background: var(--light-bg);
      color: var(--text-dark);
    }

    .header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 60px;
      background: var(--white);
      border-bottom: 1px solid var(--gray);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 30px;
      z-index: 1000;
      box-shadow: 0 2px 4px var(--shadow);
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo img {
      height: 40px;
    }

    .logo span {
      font-size: 20px;
      font-weight: bold;
    }

    .logo span span {
      color: var(--primary);
    }

    .datetime {
      font-size: 14px;
      font-weight: bold;
      color: var(--primary);
    }

    .layout {
      display: flex;
      margin-top: 60px;
    }

    .sidebar {
      width: 240px;
      background: var(--white);
      padding: 20px;
      border-right: 1px solid var(--gray);
      height: calc(100vh - 60px);
      position: fixed;
      top: 60px;
      left: 0;
      overflow-y: auto;
    }

    .search-wrapper {
      position: relative;
      margin-bottom: 25px;
    }

    .search-wrapper input,
    .search-container input {
      width: 100%;
      padding: 8px 12px 8px 34px;
      border: 1px solid var(--gray);
      border-radius: 6px;
    }

    .search-wrapper i,
    .search-container i {
      position: absolute;
      top: 50%;
      left: 10px;
      transform: translateY(-50%);
      color: #777;
    }

    .menu-section {
      font-size: 12px;
      color: #777;
      margin: 20px 0 12px;
      padding-bottom: 4px;
      border-bottom: 1px solid var(--gray);
      text-transform: uppercase;
    }

    .menu-btn {
      display: flex;
      align-items: center;
      gap: 10px;
      background: none;
      border: none;
      width: 100%;
      padding: 10px 16px;
      margin-bottom: 8px;
      border-radius: 999px;
      text-align: left;
      cursor: pointer;
      color: var(--text-dark);
      text-decoration: none;
      position: relative;
      transition: 0.3s ease;
    }

    .menu-btn i {
      width: 20px;
    }

    .menu-btn:hover,
    .menu-btn.active {
      background-color: var(--hover-bg);
      color: var(--hover-text);
      font-weight: 600;
    }

    .menu-btn:hover::before,
    .menu-btn.active::before {
      content: '';
      position: absolute;
      left: 6px;
      top: 50%;
      transform: translateY(-50%);
      height: 60%;
      width: 6px;
      background-color: var(--highlight);
      border-radius: 4px;
    }

    .logout {
      margin-top: 30px;
    }

    .main {
      margin-left: 240px;
      padding: 30px;
      width: calc(100% - 240px);
    }

    /* Chat specific styles */
    .chat-container {
      display: flex;
      height: calc(100vh - 120px);
      background: var(--white);
      border-radius: 12px;
      box-shadow: 0 2px 6px var(--shadow);
      overflow: hidden;
    }

    .conversation-list {
      width: 300px;
      border-right: 1px solid var(--gray);
      display: flex;
      flex-direction: column;
    }

    .conversation-list-header {
      padding: 15px;
      background: var(--primary);
      color: var(--white);
      text-align: center;
    }

    .conversation-tabs {
      display: flex;
      border-bottom: 1px solid var(--gray);
    }

    .conversation-tab {
      flex: 1;
      padding: 12px;
      text-align: center;
      cursor: pointer;
      background: var(--white);
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .conversation-tab.active {
      background: var(--light-bg);
      border-bottom: 2px solid var(--highlight);
      color: var(--primary);
    }

    .conversation-list-content {
      flex: 1;
      overflow-y: auto;
    }

    .conversation-item {
      padding: 12px 15px;
      border-bottom: 1px solid var(--gray);
      cursor: pointer;
      display: flex;
      justify-content: space-between;
      transition: background 0.3s ease;
    }

    .conversation-item:hover {
      background: var(--hover-bg);
    }

    .conversation-item.active {
      background: var(--hover-bg);
      border-left: 4px solid var(--highlight);
    }

    .conversation-item .user-name {
      font-weight: 500;
      color: var(--text-dark);
    }

    .conversation-item .time {
      font-size: 12px;
      color: #777;
    }

    .conversation-item .unread-count {
      background: var(--highlight);
      color: white;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
    }

    .chat-area {
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .chat-header {
      padding: 15px 20px;
      border-bottom: 1px solid var(--gray);
      background: var(--white);
    }

    .chat-header h3 {
      color: var(--primary);
      margin-bottom: 5px;
    }

    .chat-header p {
      font-size: 14px;
      color: #777;
    }

    .chat-messages {
      flex: 1;
      padding: 20px;
      overflow-y: auto;
      background: var(--light-bg);
    }

    .message {
      margin-bottom: 15px;
      max-width: 80%;
      display: flex;
    }

    .admin-message {
      justify-content: flex-end;
      margin-left: auto;
    }

    .user-message {
      justify-content: flex-start;
      margin-right: auto;
    }

    .message-content {
      padding: 12px 16px;
      border-radius: 18px;
      line-height: 1.4;
      word-wrap: break-word;
      box-shadow: 0 2px 4px var(--shadow);
    }

    .admin-message .message-content {
      background: var(--primary);
      color: white;
      border-top-right-radius: 5px;
      border-bottom-left-radius: 18px;
      border-bottom-right-radius: 5px;
    }

    .user-message .message-content {
      background: var(--white);
      color: var(--text-dark);
      border-top-left-radius: 5px;
      border-bottom-right-radius: 18px;
      border-bottom-left-radius: 5px;
      border: 1px solid var(--gray);
    }

    .message-time {
      font-size: 11px;
      color: #777;
      margin-top: 5px;
      text-align: right;
    }

    .chat-input-container {
      padding: 15px;
      background: var(--white);
      border-top: 1px solid var(--gray);
      display: flex;
    }

    .chat-input {
      flex: 1;
      padding: 12px 15px;
      border: 1px solid var(--gray);
      border-radius: 30px;
      outline: none;
      font-size: 14px;
      transition: border 0.3s ease;
    }

    .chat-input:focus {
      border-color: var(--highlight);
    }

    .chat-send-btn {
      background: var(--highlight);
      color: white;
      border: none;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      margin-left: 10px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .chat-send-btn:hover {
      background: var(--hover-text);
    }

    .no-conversation-selected {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      background: var(--light-bg);
      color: #777;
    }

    .no-conversation-selected i {
      font-size: 48px;
      color: var(--primary);
      margin-bottom: 15px;
      opacity: 0.5;
    }

    .empty-state {
      padding: 20px;
      text-align: center;
      color: #777;
    }

    .table-section {
      background: var(--white);
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 2px 6px var(--shadow);
      margin-bottom: 30px;
    }

    .table-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .table-header h3 {
      color: var(--primary);
    }

    @media (max-width: 768px) {
      .conversation-list {
        width: 100%;
        display: <?php echo $selectedConversation ? 'none' : 'block'; ?>;
      }
      
      .chat-area {
        display: <?php echo $selectedConversation ? 'flex' : 'none'; ?>;
      }
    }
  </style>
</head>
<body>
  <div class="header">
    <div class="logo">
      <img src="image/logo.png" alt="NeuroAid Logo">
      <span>Neuro<span>Aid</span> - Admin</span>
    </div>
    <div class="datetime">
      <?php 
      echo date('l, F j, Y - g:i:s A'); 
      ?>
    </div>
  </div>

  <div class="layout">
    <div class="sidebar">
      <div class="search-wrapper">
        <input type="text" placeholder="Search..." />
        <i class="fas fa-search"></i>
      </div>

      <div class="menu-section">Admin Tools</div>
      <a href="admin_dashboard.php" class="menu-btn"><i class="fas fa-home"></i>Dashboard</a>
      <a href="patient_management.php" class="menu-btn"><i class="fas fa-user"></i>Patient Management</a>
      <a href="monitoring.php" class="menu-btn"><i class="fas fa-heartbeat"></i>Monitoring</a>
      <a href="admin_chats.php" class="menu-btn active"><i class="fas fa-comments"></i>Chat</a>
      <a href="caregiver.php" class="menu-btn"><i class="fas fa-hands-helping"></i>CareGiver Management</a>
      <a href="content.php" class="menu-btn"><i class="fas fa-file-alt"></i>Content Manager</a>
      <a href="feedback.php" class="menu-btn"><i class="fas fa-exclamation-circle"></i>Feedback & Issues</a>

      <div class="menu-section">Settings</div>
      <a href="settings.php" class="menu-btn"><i class="fas fa-user-cog"></i>Settings</a>

      <div class="logout">
        <a href="logout.php" class="menu-btn"><i class="fas fa-sign-out-alt"></i>Logout</a>
      </div>
    </div>

    <div class="main">
      <h2 style="margin-bottom: 20px; color: var(--primary); font-size: 24px;">Chat Dashboard</h2>
      
      <div class="chat-container">
        <div class="conversation-list">
          <div class="conversation-list-header">
            <h3>Admin Chat Dashboard</h3>
            <p>Active Conversations</p>
          </div>
          
          <div class="conversation-tabs">
            <div class="conversation-tab active" data-tab="active">My Chats</div>
            <div class="conversation-tab" data-tab="waiting">Waiting</div>
          </div>
          
          <div class="conversation-list-content">
            <div id="activeConversations">
              <?php if (empty($activeConversations)): ?>
                <div class="empty-state">
                  <p>No active conversations</p>
                </div>
              <?php else: ?>
                <?php foreach ($activeConversations as $conv): ?>
                  <div class="conversation-item <?php echo $selectedConversationId == $conv['id'] ? 'active' : ''; ?>" 
                       data-conversation-id="<?php echo $conv['id']; ?>">
                    <div>
                      <div class="user-name"><?php echo htmlspecialchars($conv['user_name']); ?></div>
                      <div class="time"><?php echo date("M j, h:i A", strtotime($conv['created_at'])); ?></div>
                    </div>
                    <?php if ($conv['unread_count'] > 0): ?>
                      <div class="unread-count"><?php echo $conv['unread_count']; ?></div>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
            
            <div id="waitingConversations" style="display: none;">
              <?php if (empty($waitingConversations)): ?>
                <div class="empty-state">
                  <p>No waiting conversations</p>
                </div>
              <?php else: ?>
                <?php foreach ($waitingConversations as $conv): ?>
                  <div class="conversation-item" data-conversation-id="<?php echo $conv['id']; ?>">
                    <div>
                      <div class="user-name"><?php echo htmlspecialchars($conv['user_name']); ?></div>
                      <div class="time"><?php echo date("M j, h:i A", strtotime($conv['created_at'])); ?></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
        
        <div class="chat-area">
          <?php if ($selectedConversation): ?>
            <div class="chat-header">
              <h3><?php echo htmlspecialchars($selectedConversation['user_name']); ?></h3>
              <p>Conversation started <?php echo date("M j, h:i A", strtotime($selectedConversation['created_at'])); ?></p>
            </div>
            
            <div class="chat-messages" id="chatMessages">
              <?php if (empty($messages)): ?>
                <div class="message user-message">
                  <div class="message-content">
                    <p>User joined the chat</p>
                  </div>
                </div>
              <?php else: ?>
                <?php foreach ($messages as $message): ?>
                  <div class="message <?php echo $message['sender_role'] === 'admin' ? 'admin-message' : 'user-message'; ?>" data-message-id="<?php echo $message['id']; ?>">
                    <div class="message-content">
                      <p><?php echo htmlspecialchars($message['message']); ?></p>
                      <div class="message-time">
                        <?php echo date("h:i A", strtotime($message['sent_at'])); ?>
                        <?php if ($message['sender_role'] === 'admin'): ?>
                          <i class="fas fa-check<?php echo $message['is_read'] ? '-double' : ''; ?>"></i>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
            
            <div class="chat-input-container">
              <form id="chatForm">
                <input type="text" id="adminMessage" class="chat-input" placeholder="Type your message here..." autocomplete="off">
                <button type="submit" class="chat-send-btn">
                  <i class="fas fa-paper-plane"></i>
                </button>
              </form>
            </div>
          <?php else: ?>
            <div class="no-conversation-selected">
              <i class="fas fa-comments"></i>
              <p>Select a conversation from the list</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
      const chatForm = document.getElementById('chatForm');
      const adminMessage = document.getElementById('adminMessage');
      const chatMessages = document.getElementById('chatMessages');
      const conversationItems = document.querySelectorAll('.conversation-item');
      const tabButtons = document.querySelectorAll('.conversation-tab');
      const activeConversationsDiv = document.getElementById('activeConversations');
      const waitingConversationsDiv = document.getElementById('waitingConversations');
      const selectedConversationId = <?php echo $selectedConversationId ? $selectedConversationId : 'null'; ?>;
      
      let lastMessageId = <?php echo $selectedConversation && !empty($messages) ? end($messages)['id'] : 0; ?>;
      let isPollingActive = true;
      let pollingTimeout;

      // Tab switching
      tabButtons.forEach(button => {
          button.addEventListener('click', function() {
              tabButtons.forEach(btn => btn.classList.remove('active'));
              this.classList.add('active');
              
              if (this.dataset.tab === 'active') {
                  activeConversationsDiv.style.display = 'block';
                  waitingConversationsDiv.style.display = 'none';
              } else {
                  activeConversationsDiv.style.display = 'none';
                  waitingConversationsDiv.style.display = 'block';
              }
          });
      });
      
      // Conversation item click
      conversationItems.forEach(item => {
          item.addEventListener('click', function() {
              const conversationId = this.dataset.conversationId;
              window.location.href = `admin_chats.php?conversation_id=${conversationId}`;
          });
      });
      
      // Scroll to bottom of chat
      function scrollToBottom() {
          if (chatMessages) {
              chatMessages.scrollTop = chatMessages.scrollHeight;
          }
      }
      
      // Add a message to the chat
      function addMessage(sender, message, timestamp = null, messageId = null, isRead = false) {
          // Check if message already exists
          if (messageId && document.querySelector(`.message[data-message-id="${messageId}"]`)) {
              return;
          }
          
          const messageDiv = document.createElement('div');
          messageDiv.className = `message ${sender}-message`;
          if (messageId) {
              messageDiv.dataset.messageId = messageId;
              lastMessageId = Math.max(lastMessageId, messageId);
          }
          
          const time = timestamp ? new Date(timestamp) : new Date();
          const timeString = time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
          
          messageDiv.innerHTML = `
              <div class="message-content">
                  <p>${message}</p>
                  <div class="message-time">
                      ${timeString}
                      ${sender === 'admin' ? `<i class="fas fa-check${isRead ? '-double' : ''}"></i>` : ''}
                  </div>
              </div>
          `;
          
          if (chatMessages) {
              chatMessages.appendChild(messageDiv);
              scrollToBottom();
          }
      }
      
      // Update conversation list
      function updateConversationList(elementId, conversations) {
          const listElement = document.getElementById(elementId);
          
          if (conversations.length === 0) {
              listElement.innerHTML = '<div class="empty-state"><p>No conversations</p></div>';
              return;
          }

          let html = '';
          conversations.forEach(conv => {
              html += `
                  <div class="conversation-item ${conv.id == selectedConversationId ? 'active' : ''}" 
                       data-conversation-id="${conv.id}">
                      <div>
                          <div class="user-name">${conv.user_name}</div>
                          <div class="time">${new Date(conv.created_at).toLocaleString()}</div>
                      </div>
                      ${conv.unread_count > 0 ? `<div class="unread-count">${conv.unread_count}</div>` : ''}
                  </div>
              `;
          });
          
          listElement.innerHTML = html;
          
          // Reattach click handlers
          document.querySelectorAll(`#${elementId} .conversation-item`).forEach(item => {
              item.addEventListener('click', function() {
                  window.location.href = `admin_chats.php?conversation_id=${this.dataset.conversationId}`;
              });
          });
      }

      if (chatForm) {
          chatForm.addEventListener('submit', function(e) {
              e.preventDefault();
              const message = adminMessage.value.trim();
              
              if (message) {
                  // Generate a temporary ID for the client-side message
                  const tempId = 'temp-' + Date.now();
                  
                  // Add admin message to chat immediately with temp ID
                  addMessage('admin', message, null, tempId);
                  adminMessage.value = '';
                  
                  // Send to server
                  fetch('admin_chat_handler.php', {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/json',
                      },
                      body: JSON.stringify({
                          conversation_id: selectedConversationId,
                          message: message,
                          action: 'send_message',
                          temp_id: tempId  // Send the temp ID to the server
                      })
                  })
                  .then(response => response.json())
                  .then(data => {
                      if (data.success && data.message_id) {
                          // Update the temporary message with the real ID from server
                          const tempMessage = document.querySelector(`.message[data-message-id="${tempId}"]`);
                          if (tempMessage) {
                              tempMessage.dataset.messageId = data.message_id;
                          }
                      }
                  })
                  .catch(error => {
                      console.error('Error:', error);
                      // Optionally remove the temporary message if sending failed
                  });
              }
          });
      }
      
      // Check for new messages and conversation updates
      function checkForUpdates() {
          if (!isPollingActive) return;
          
          fetch('admin_chat_handler.php', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
              },
              body: JSON.stringify({
                  action: 'check_updates',
                  admin_id: <?php echo $adminId; ?>,
                  last_message_id: lastMessageId,
                  conversation_id: selectedConversationId
              })
          })
          .then(response => response.json())
          .then(data => {
              // Update active conversations list
              if (data.active_conversations) {
                  updateConversationList('activeConversations', data.active_conversations);
              }
              
              // Update waiting conversations list
              if (data.waiting_conversations) {
                  updateConversationList('waitingConversations', data.waiting_conversations);
              }
              
              // Add new messages if in the correct conversation
              if (data.new_messages && selectedConversationId) {
                  data.new_messages.forEach(msg => {
                      // Skip messages that were temporarily added client-side
                      if (msg.temp_id) {
                          const existingTempMsg = document.querySelector(`.message[data-message-id="temp-${msg.temp_id}"]`);
                          if (existingTempMsg) {
                              // Update the temporary message with the real ID
                              existingTempMsg.dataset.messageId = msg.id;
                              return;
                          }
                      }
                      
                      if (msg.conversation_id == selectedConversationId) {
                          addMessage(msg.sender_role === 'admin' ? 'admin' : 'user', 
                                    msg.message, msg.sent_at, msg.id, msg.is_read);
                      }
                  });
              }
          })
          .catch(error => {
              console.error('Polling error:', error);
          })
          .finally(() => {
              if (isPollingActive) {
                  pollingTimeout = setTimeout(checkForUpdates, 2000);
              }
          });
      }
      
      // Clean up when page is closed
      window.addEventListener('beforeunload', function() {
          isPollingActive = false;
          if (pollingTimeout) {
              clearTimeout(pollingTimeout);
          }
      });
      
      // Start checking for updates
      checkForUpdates();
      
      // Initial scroll to bottom
      scrollToBottom();
  });
  </script>
</body>
</html>