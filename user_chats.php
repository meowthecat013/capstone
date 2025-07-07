<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

// Check for active conversation
$stmt = $pdo->prepare("SELECT c.*, u.full_name as admin_name 
                       FROM admin_chat_conversations c
                       LEFT JOIN users u ON c.admin_id = u.id
                       WHERE c.user_id = ? AND c.is_active = 1
                       ORDER BY c.created_at DESC LIMIT 1");
$stmt->execute([$userId]);
$conversation = $stmt->fetch();

// If no active conversation, create one
if (!$conversation) {
    $stmt = $pdo->prepare("INSERT INTO admin_chat_conversations (user_id) VALUES (?)");
    $stmt->execute([$userId]);
    $conversationId = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("SELECT c.*, u.full_name as admin_name 
                           FROM admin_chat_conversations c
                           LEFT JOIN users u ON c.admin_id = u.id
                           WHERE c.id = ?");
    $stmt->execute([$conversationId]);
    $conversation = $stmt->fetch();
} else {
    $conversationId = $conversation['id'];
}

// Get messages
$stmt = $pdo->prepare("SELECT m.*, u.full_name as sender_name 
                       FROM admin_chat_messages m
                       JOIN users u ON m.sender_id = u.id
                       WHERE m.conversation_id = ? 
                       ORDER BY m.sent_at ASC");
$stmt->execute([$conversationId]);
$messages = $stmt->fetchAll();

// Mark messages as read when user opens chat
$pdo->prepare("UPDATE admin_chat_messages 
               SET is_read = TRUE 
               WHERE conversation_id = ? AND sender_role = 'admin'")
   ->execute([$conversationId]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Chat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', 'Roboto', sans-serif;
            background: #f5f5f5;
            height: 100vh;
            overflow: hidden;
        }
        
        .chat-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            max-height: -webkit-fill-available;
            background: white;
        }
        
        .chat-header {
            background: #2d5a4c;
            color: white;
            padding: 15px 20px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .chat-header h3 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .chat-header p {
            font-size: 14px;
            opacity: 0.8;
        }
        
        .chat-messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            background: #f9f9f9;
        }
        
        .message {
            margin-bottom: 15px;
            max-width: 100%;
            display: flex;
        }

        .admin-message {
            justify-content: flex-start;
        }

        .user-message {
            justify-content: flex-end;
        }

        .message-content {
            padding: 10px 15px;
            border-radius: 18px;
            line-height: 1.4;
            max-width: 70%;
            word-wrap: break-word;
        }

        .admin-message .message-content {
            background: #e3f2fd;
            color: #333;
            border-top-left-radius: 5px;
            border-bottom-right-radius: 18px;
            border-bottom-left-radius: 5px;
        }

        .user-message .message-content {
            background: #2d5a4c;
            color: white;
            border-top-right-radius: 5px;
            border-bottom-left-radius: 18px;
            border-bottom-right-radius: 5px;
        }
        
        .message-time {
            font-size: 11px;
            color: #777;
            margin-top: 3px;
            text-align: right;
        }
        
        .chat-input {
            padding: 15px;
            background: white;
            border-top: 1px solid #e0e0e0;
            display: flex;
        }
        
        .chat-input input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            outline: none;
            font-size: 14px;
        }
        
        .chat-input button {
            background: #2d5a4c;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            margin-left: 10px;
            cursor: pointer;
        }
        
        .chat-controls {
            display: flex;
            justify-content: space-between;
            padding: 10px 15px;
            background: #f5f5f5;
            border-top: 1px solid #e0e0e0;
        }
        
        .btn-small {
            padding: 8px 15px;
            background: #2d5a4c;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
        }
        
        .btn-small:hover {
            background: #1e3d34;
        }
        
        .admin-status {
            font-size: 13px;
            color: #666;
            display: flex;
            align-items: center;
        }
        
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .online {
            background: #4CAF50;
        }
        
        .offline {
            background: #ccc;
        }
     * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', 'Roboto', sans-serif;
            background: #f5f5f5;
            height: 100vh;
            overflow: hidden;
        }
        
        .chat-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            max-height: -webkit-fill-available;
            background: white;
        }
        
        </style>
</head>
<body>
   <div class="chat-container">
        <div class="chat-header">
            <h3>NeuroAid Support</h3>
            <p><?php echo $conversation['admin_name'] ? "Chatting with ".htmlspecialchars($conversation['admin_name']) : "Waiting for admin to join"; ?></p>
            <div class="admin-status">
                <span class="status-indicator <?php echo $conversation['admin_id'] ? 'online' : 'offline'; ?>"></span>
                <?php echo $conversation['admin_id'] ? 'Admin online' : 'No admin available'; ?>
            </div>
        </div>
        
        <div class="chat-messages" id="chatMessages">
            <?php if (empty($messages)): ?>
                <div class="message admin-message">
                    <div class="message-content">
                        <p>Hello! Our support team will be with you shortly. Please describe your issue.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message <?php echo $message['sender_role'] === 'admin' ? 'admin-message' : 'user-message'; ?>" data-message-id="<?php echo $message['id']; ?>">
                        <div class="message-content">
                            <p><?php echo htmlspecialchars($message['message']); ?></p>
                            <div class="message-time">
                                <?php echo date("h:i A", strtotime($message['sent_at'])); ?>
                                <?php if ($message['sender_role'] === 'admin' && $message['is_read']): ?>
                                    <i class="fas fa-check-double"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="chat-input">
            <form id="chatForm">
                <input type="text" id="userMessage" placeholder="Type your message here..." autocomplete="off" <?php echo !$conversation['admin_id'] ? 'disabled placeholder="Waiting for admin to connect..."' : ''; ?>>
                <button type="submit" <?php echo !$conversation['admin_id'] ? 'disabled' : ''; ?>>
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
        
        <div class="chat-controls">
            <button id="endChat" class="btn-small">End Chat</button>
            <button id="sendAttachment" class="btn-small">Send Attachment</button>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatForm = document.getElementById('chatForm');
        const userMessage = document.getElementById('userMessage');
        const chatMessages = document.getElementById('chatMessages');
        const endChatBtn = document.getElementById('endChat');
        const conversationId = <?php echo $conversationId; ?>;
        let adminOnline = <?php echo $conversation['admin_id'] ? 'true' : 'false'; ?>;
        let lastMessageId = <?php echo empty($messages) ? 0 : end($messages)['id']; ?>;
        let isPollingActive = true;
        let isPolling = false;
        let pollingTimeout;
        
        // Scroll to bottom of chat
        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        // Add a message to the chat
        function addMessage(sender, message, timestamp = null, messageId = null) {
            // Check if message already exists
            if (messageId && document.querySelector(`.message[data-message-id="${messageId}"]`)) {
                return;
            }
            
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}-message`;
            if (messageId) {
                messageDiv.dataset.messageId = messageId;
            }
            
            const time = timestamp ? new Date(timestamp) : new Date();
            const timeString = time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            
            messageDiv.innerHTML = `
                <div class="message-content">
                    <p>${message}</p>
                    <div class="message-time">${timeString}</div>
                </div>
            `;
            chatMessages.appendChild(messageDiv);
            scrollToBottom();
            
            if (messageId) {
                lastMessageId = Math.max(lastMessageId, messageId);
            }
        }
        
        // Update admin status
        function updateAdminStatus(isOnline, adminName = null) {
            const statusIndicator = document.querySelector('.status-indicator');
            const statusText = document.querySelector('.admin-status span:last-child');
            
            adminOnline = isOnline;
            
            if (isOnline) {
                statusIndicator.className = 'status-indicator online';
                statusText.textContent = 'Admin online';
                userMessage.disabled = false;
                userMessage.placeholder = 'Type your message here...';
                document.querySelector('button[type="submit"]').disabled = false;
                
                if (adminName) {
                    document.querySelector('.chat-header p').textContent = `Chatting with ${adminName}`;
                }
            } else {
                statusIndicator.className = 'status-indicator offline';
                statusText.textContent = 'No admin available';
            }
        }
        
        // Handle form submission
        chatForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const message = userMessage.value.trim();
            
            if (message && adminOnline) {
                const sendButton = chatForm.querySelector('button[type="submit"]');
                sendButton.disabled = true;
                
                try {
                    const response = await fetch('user_chat_handler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            conversation_id: conversationId,
                            message: message,
                            action: 'send_message'
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        addMessage('user', message, null, data.message_id);
                        userMessage.value = '';
                    } else {
                        console.error('Failed to send message');
                        alert('Failed to send message. Please try again.');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while sending your message.');
                } finally {
                    sendButton.disabled = false;
                }
            }
        });
        
        // End chat
        endChatBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to end this chat?')) {
                fetch('user_chat_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        conversation_id: conversationId,
                        action: 'end_chat'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        addMessage('admin', "Thank you for contacting us. This chat has been ended.");
                        userMessage.disabled = true;
                        document.querySelector('button[type="submit"]').disabled = true;
                        endChatBtn.disabled = true;
                        updateAdminStatus(false);
                    }
                });
            }
        });
        
        // Check for new messages
        function checkForNewMessages() {
            if (!isPollingActive || isPolling) return;
            
            isPolling = true;
            
            fetch('user_chat_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    conversation_id: conversationId,
                    action: 'check_messages',
                    last_message_id: lastMessageId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.new_messages && data.new_messages.length > 0) {
                    data.new_messages.forEach(msg => {
                        if (msg.sender_role !== 'user' || msg.id > lastMessageId) {
                            addMessage(msg.sender_role === 'admin' ? 'admin' : 'user', 
                                      msg.message, msg.sent_at, msg.id);
                        }
                    });
                }
                
                if (data.admin_online !== undefined) {
                    updateAdminStatus(data.admin_online, data.admin_name);
                }
            })
            .catch(error => {
                console.error('Polling error:', error);
            })
            .finally(() => {
                isPolling = false;
                if (isPollingActive) {
                    pollingTimeout = setTimeout(checkForNewMessages, 2000);
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
        
        // Start checking for new messages
        checkForNewMessages();
        
        // Initial scroll to bottom
        scrollToBottom();
    });
    </script>
</body>
</html>