<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

// Get or create AI conversation
$stmt = $pdo->prepare("SELECT * FROM ai_chat_conversations WHERE user_id = ? AND is_active = 1 LIMIT 1");
$stmt->execute([$userId]);
$aiConversation = $stmt->fetch();

if (!$aiConversation) {
    $pdo->prepare("INSERT INTO ai_chat_conversations (user_id) VALUES (?)")->execute([$userId]);
    $aiConversationId = $pdo->lastInsertId();
    $aiConversation = ['id' => $aiConversationId, 'user_id' => $userId];
} else {
    $aiConversationId = $aiConversation['id'];
}

// Get AI messages
$stmt = $pdo->prepare("SELECT * FROM ai_chat_messages WHERE conversation_id = ? ORDER BY sent_at ASC");
$stmt->execute([$aiConversationId]);
$aiMessages = $stmt->fetchAll();

// Get or create admin conversation
$stmt = $pdo->prepare("SELECT c.*, u.full_name as admin_name 
                       FROM admin_chat_conversations c
                       LEFT JOIN users u ON c.admin_id = u.id
                       WHERE c.user_id = ? AND c.is_active = 1
                       ORDER BY c.created_at DESC LIMIT 1");
$stmt->execute([$userId]);
$adminConversation = $stmt->fetch();

if (!$adminConversation) {
    $stmt = $pdo->prepare("INSERT INTO admin_chat_conversations (user_id) VALUES (?)");
    $stmt->execute([$userId]);
    $adminConversationId = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("SELECT c.*, u.full_name as admin_name 
                           FROM admin_chat_conversations c
                           LEFT JOIN users u ON c.admin_id = u.id
                           WHERE c.id = ?");
    $stmt->execute([$adminConversationId]);
    $adminConversation = $stmt->fetch();
} else {
    $adminConversationId = $adminConversation['id'];
}

// Get admin messages
$stmt = $pdo->prepare("SELECT m.*, u.full_name as sender_name 
                       FROM admin_chat_messages m
                       JOIN users u ON m.sender_id = u.id
                       WHERE m.conversation_id = ? 
                       ORDER BY m.sent_at ASC");
$stmt->execute([$adminConversationId]);
$adminMessages = $stmt->fetchAll();
$lastAdminMessageId = !empty($adminMessages) ? end($adminMessages)['id'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeuroAid Support</title>
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
            position: relative;
        }
        
        .chat-header h3 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .chat-tabs {
            display: flex;
            margin-top: 10px;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .chat-tab {
            flex: 1;
            padding: 8px;
            text-align: center;
            background: #1e3d34;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .chat-tab.active {
            background: #4a8c7c;
            font-weight: bold;
        }
        
        .chat-tab:not(.active):hover {
            background: #2d5a4c;
        }
        
        .chat-content {
            display: none;
            flex: 1;
            flex-direction: column;
            height: calc(100% - 120px);
        }
        
        .chat-content.active {
            display: flex;
        }
        
        .chat-messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            background: #f9f9f9;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            min-height: 0;
        }
        
        .message {
            margin-bottom: 15px;
            max-width: 100%;
            display: flex;
        }

        .ai-message, .admin-message {
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

        .ai-message .message-content {
            background: #e8f5e9;
            color: #333;
            border-top-left-radius: 5px;
            border-bottom-right-radius: 18px;
            border-bottom-left-radius: 5px;
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
        
        .sender-label {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 2px;
            color: #555;
        }
        
        .chat-input {
            padding: 15px;
            background: white;
            border-top: 1px solid #e0e0e0;
            display: flex;
        }
        
        .chat-input input {
            flex: 1;
            width: 700px;
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
        
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
            display: inline-block;
        }
        
        .online {
            background: #4CAF50;
        }
        
        .offline {
            background: #ccc;
        }
        
        .typing-indicator {
            display: flex;
            padding: 5px 15px;
            margin-bottom: 15px;
        }
        
        .typing-dot {
            width: 8px;
            height: 8px;
            background-color: #888;
            border-radius: 50%;
            margin: 0 2px;
            animation: typingAnimation 1.4s infinite ease-in-out;
        }
        
        .typing-dot:nth-child(1) {
            animation-delay: 0s;
        }
        
        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes typingAnimation {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-5px); }
        }
        
        .suggested-questions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        
        .suggested-question {
            background: #e3f2fd;
            border-radius: 15px;
            padding: 5px 12px;
            font-size: 13px;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .suggested-question:hover {
            background: #bbdefb;
        }
        .voice-button {
        background: #4a8c7c;
        color: white;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        margin-left: 5px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .voice-button:hover {
        background: #2d5a4c;
    }
    
    .voice-button.listening {
        background: #e74c3c;
        animation: pulse 1.5s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <h3>NeuroAid Support</h3>
            <div class="chat-tabs">
                <div class="chat-tab active" data-tab="ai">AI Assistant</div>
                <div class="chat-tab" data-tab="admin">Human Support</div>
            </div>
        </div>
        
        <!-- AI Chat Tab -->
        <div class="chat-content active" id="ai-chat">
            <div class="chat-messages" id="aiMessages">
                <?php if (empty($aiMessages)): ?>
                    <div class="message ai-message">
                        <div class="message-content">
                            <div class="sender-label">NeuroAid Assistant</div>
                            <p>Hello! I'm your NeuroAid AI assistant. I'm here to help you with any questions you have about stroke recovery, exercises, or general support. How can I help you today?</p>
                        </div>
                    </div>
                    
                    <div class="suggested-questions">
                        <div class="suggested-question">What exercises can I do today?</div>
                        <div class="suggested-question">How can I improve my speech?</div>
                        <div class="suggested-question">What foods are good for recovery?</div>
                        <div class="suggested-question">When should I contact my doctor?</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($aiMessages as $message): ?>
                        <div class="message <?php echo $message['sender_role'] === 'ai' ? 'ai-message' : 'user-message'; ?>">
                            <div class="message-content">
                                <?php if ($message['sender_role'] === 'ai'): ?>
                                    <div class="sender-label">NeuroAid Assistant</div>
                                <?php endif; ?>
                                <p><?php echo htmlspecialchars($message['message']); ?></p>
                                <div class="message-time">
                                    <?php echo date("h:i A", strtotime($message['sent_at'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div id="aiTypingIndicator" class="typing-indicator" style="display: none;">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>
            
            <div class="chat-input">
    <form id="aiChatForm">
        <input type="text" id="aiUserMessage" placeholder="Type your message to the AI assistant..." autocomplete="off">
        <button type="button" id="aiVoiceButton" class="voice-button">
            <i class="fas fa-microphone"></i>
        </button>
        <button type="submit">
            <i class="fas fa-paper-plane"></i>
        </button>
    </form>
</div>
            
            <div class="chat-controls">
                <button id="transferToHuman" class="btn-small">Transfer to Human</button>
                <button id="aiHelpButton" class="btn-small">Get Help Suggestions</button>
            </div>
        </div>
        
        <!-- Admin Chat Tab -->
        <div class="chat-content" id="admin-chat">
            <div class="chat-messages" id="adminMessages">
                <?php if (empty($adminMessages)): ?>
                    <div class="message admin-message">
                        <div class="message-content">
                            <div class="sender-label">Support Team</div>
                            <p>Hello! Our human support team will be with you shortly. Please describe your issue and we'll connect you with the right specialist.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($adminMessages as $message): ?>
                        <div class="message <?php echo $message['sender_role'] === 'admin' ? 'admin-message' : 'user-message'; ?>">
                            <div class="message-content">
                                <div class="sender-label"><?php echo htmlspecialchars($message['sender_name']); ?></div>
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
    <form id="adminChatForm">
        <input type="text" id="adminUserMessage" placeholder="Type your message to support..." autocomplete="off" <?php echo !$adminConversation['admin_id'] ? 'disabled placeholder="Waiting for admin to connect..."' : ''; ?>>
        <button type="button" id="adminVoiceButton" class="voice-button" <?php echo !$adminConversation['admin_id'] ? 'disabled' : ''; ?>>
            <i class="fas fa-microphone"></i>
        </button>
        <button type="submit" <?php echo !$adminConversation['admin_id'] ? 'disabled' : ''; ?>>
            <i class="fas fa-paper-plane"></i>
        </button>
    </form>
</div>
            
            <div class="chat-controls">
                <button id="endChat" class="btn-small">End Chat</button>
                <button id="sendAttachment" class="btn-small">Send Attachment</button>
            </div>
        </div>
    </div>

    <script>


// Voice Recognition Functionality
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    
    if (SpeechRecognition) {
        const aiVoiceButton = document.getElementById('aiVoiceButton');
        const adminVoiceButton = document.getElementById('adminVoiceButton');
        
        // Create recognition instances for both chats
        const aiRecognition = new SpeechRecognition();
        const adminRecognition = new SpeechRecognition();
        
        aiRecognition.continuous = false;
        aiRecognition.interimResults = false;
        aiRecognition.lang = 'en-US';
        
        adminRecognition.continuous = false;
        adminRecognition.interimResults = false;
        adminRecognition.lang = 'en-US';
        
        // AI Voice Handler
        aiVoiceButton.addEventListener('click', function() {
            if (this.classList.contains('listening')) {
                aiRecognition.stop();
                this.classList.remove('listening');
                return;
            }
            
            this.classList.add('listening');
            aiRecognition.start();
        });
        
        aiRecognition.onresult = function(event) {
            const transcript = event.results[0][0].transcript;
            aiUserMessage.value = transcript;
            aiVoiceButton.classList.remove('listening');
            
            // Auto-submit if there's text
            if (transcript.trim()) {
                aiChatForm.dispatchEvent(new Event('submit'));
            }
        };
        
        aiRecognition.onerror = function(event) {
            console.error('Speech recognition error', event.error);
            aiVoiceButton.classList.remove('listening');
            alert('Voice recognition error: ' + event.error);
        };
        
        aiRecognition.onend = function() {
            aiVoiceButton.classList.remove('listening');
        };
        
        // Admin Voice Handler
        adminVoiceButton.addEventListener('click', function() {
            if (this.classList.contains('listening')) {
                adminRecognition.stop();
                this.classList.remove('listening');
                return;
            }
            
            this.classList.add('listening');
            adminRecognition.start();
        });
        
        adminRecognition.onresult = function(event) {
            const transcript = event.results[0][0].transcript;
            adminUserMessage.value = transcript;
            adminVoiceButton.classList.remove('listening');
            
            // Auto-submit if there's text
            if (transcript.trim()) {
                adminChatForm.dispatchEvent(new Event('submit'));
            }
        };
        
        adminRecognition.onerror = function(event) {
            console.error('Speech recognition error', event.error);
            adminVoiceButton.classList.remove('listening');
            alert('Voice recognition error: ' + event.error);
        };
        
        adminRecognition.onend = function() {
            adminVoiceButton.classList.remove('listening');
        };
    } else {
        // Hide voice buttons if not supported
        document.querySelectorAll('.voice-button').forEach(btn => {
            btn.style.display = 'none';
        });
        
        // Optional: Show a message that voice is not supported
        console.log('Speech recognition not supported in this browser');
    }






    
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching functionality
        const tabs = document.querySelectorAll('.chat-tab');
        const chatContents = document.querySelectorAll('.chat-content');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                chatContents.forEach(content => content.classList.remove('active'));
                document.getElementById(`${this.dataset.tab}-chat`).classList.add('active');
                
                // Scroll to bottom when switching tabs
                const messagesContainer = document.getElementById(`${this.dataset.tab === 'ai' ? 'ai' : 'admin'}Messages`);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            });
        });
        
        // Scroll to bottom on initial load
        document.getElementById('aiMessages').scrollTop = document.getElementById('aiMessages').scrollHeight;
        document.getElementById('adminMessages').scrollTop = document.getElementById('adminMessages').scrollHeight;
        
        // AI Chat Functionality
        const aiChatForm = document.getElementById('aiChatForm');
        const aiUserMessage = document.getElementById('aiUserMessage');
        const aiMessagesContainer = document.getElementById('aiMessages');
        const aiTypingIndicator = document.getElementById('aiTypingIndicator');
        const aiConversationId = <?php echo $aiConversation['id']; ?>;
        const transferToHumanBtn = document.getElementById('transferToHuman');
        const aiHelpButton = document.getElementById('aiHelpButton');
        
        // Suggested questions click handler
        document.querySelectorAll('.suggested-question').forEach(btn => {
            btn.addEventListener('click', function() {
                aiUserMessage.value = this.textContent;
                aiChatForm.dispatchEvent(new Event('submit'));
            });
        });
        
        // AI Chat Form Submission
        aiChatForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const message = aiUserMessage.value.trim();
            
            if (message) {
                addAIMessage('user', message);
                aiUserMessage.value = '';
                
                // Show typing indicator
                aiTypingIndicator.style.display = 'flex';
                aiMessagesContainer.scrollTop = aiMessagesContainer.scrollHeight;
                
                try {
                    const response = await fetch('ai_chat_handler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            conversation_id: aiConversationId,
                            message: message,
                            action: 'send_message'
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success && data.response) {
                        // Simulate typing delay for more natural interaction
                        await new Promise(resolve => setTimeout(resolve, 1500));
                        addAIMessage('ai', data.response);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    addAIMessage('ai', "I'm having trouble responding right now. Please try again later or contact human support.");
                } finally {
                    aiTypingIndicator.style.display = 'none';
                }
            }
        });
        
        // Add message to AI chat
        function addAIMessage(sender, message) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}-message`;
            
            const time = new Date();
            const timeString = time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            
            messageDiv.innerHTML = `
                <div class="message-content">
                    ${sender === 'ai' ? '<div class="sender-label">NeuroAid Assistant</div>' : ''}
                    <p>${message}</p>
                    <div class="message-time">${timeString}</div>
                </div>
            `;
            
            aiMessagesContainer.appendChild(messageDiv);
            aiMessagesContainer.scrollTop = aiMessagesContainer.scrollHeight;
        }
        
        // Transfer to human support
        transferToHumanBtn.addEventListener('click', function() {
            if (confirm('Would you like to transfer this conversation to a human support agent?')) {
                // Get AI conversation context
                fetch('ai_chat_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        conversation_id: aiConversationId,
                        action: 'transfer_to_human'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('A human support agent will join the conversation shortly.');
                        // Switch to admin tab
                        document.querySelector('.chat-tab[data-tab="admin"]').click();
                    }
                });
            }
        });
        
        // Get help suggestions
        aiHelpButton.addEventListener('click', function() {
            addAIMessage('ai', "Here are some topics I can help with:");
            
            const suggestions = [
                "Daily recovery exercises",
                "Speech improvement techniques",
                "Nutrition for stroke recovery",
                "Emotional support resources",
                "Medication reminders",
                "Progress tracking"
            ];
            
            const suggestionsContainer = document.createElement('div');
            suggestionsContainer.className = 'suggested-questions';
            
            suggestions.forEach(suggestion => {
                const suggestionEl = document.createElement('div');
                suggestionEl.className = 'suggested-question';
                suggestionEl.textContent = suggestion;
                suggestionEl.addEventListener('click', function() {
                    aiUserMessage.value = this.textContent;
                    aiChatForm.dispatchEvent(new Event('submit'));
                });
                suggestionsContainer.appendChild(suggestionEl);
            });
            
            aiMessagesContainer.appendChild(suggestionsContainer);
            aiMessagesContainer.scrollTop = aiMessagesContainer.scrollHeight;
        });
        
        // Admin Chat Functionality
        const adminChatForm = document.getElementById('adminChatForm');
        const adminUserMessage = document.getElementById('adminUserMessage');
        const adminMessagesContainer = document.getElementById('adminMessages');
        const adminConversationId = <?php echo $adminConversation['id']; ?>;
        const endChatBtn = document.getElementById('endChat');
        const sendAttachmentBtn = document.getElementById('sendAttachment');

        // Real-time polling variables
        let lastAdminMessageId = <?php echo $lastAdminMessageId; ?>;
        let isAdminConnected = <?php echo $adminConversation['admin_id'] ? 'true' : 'false'; ?>;
        let pollInterval;

        // Start polling for new messages when admin tab is active
        document.querySelector('.chat-tab[data-tab="admin"]').addEventListener('click', function() {
            startPolling();
        });

        // Stop polling when switching tabs
        document.querySelector('.chat-tab[data-tab="ai"]').addEventListener('click', function() {
            stopPolling();
        });

        function startPolling() {
            stopPolling(); // Clear any existing interval
            pollInterval = setInterval(fetchNewAdminMessages, 3000); // Poll every 3 seconds
            fetchNewAdminMessages(); // Immediate check
        }

        function stopPolling() {
            if (pollInterval) {
                clearInterval(pollInterval);
            }
        }

        // Admin Chat Form Submission
        adminChatForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const message = adminUserMessage.value.trim();
            
            if (message) {
                // Don't add to UI yet - wait for server response
                adminUserMessage.value = '';
                
                try {
                    const response = await fetch('admin1_chat_handler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            conversation_id: adminConversationId,
                            message: message,
                            action: 'send_message'
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (!data.success) {
                        alert('Failed to send message');
                        adminUserMessage.value = message; // Restore message if failed
                    }
                } catch (error) {
                    console.error('Error:', error);
                    adminUserMessage.value = message; // Restore message if error
                }
            }
        });

        // Fetch new admin messages
        function fetchNewAdminMessages() {
            fetch('admin1_chat_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    conversation_id: adminConversationId,
                    action: 'get_messages',
                    last_message_id: lastAdminMessageId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.messages.length > 0) {
                    // Filter out any messages we might have already processed
                    const newMessages = data.messages.filter(msg => msg.id > lastAdminMessageId);
                    
                    if (newMessages.length > 0) {
                        newMessages.forEach(message => {
                            // Check if message already exists in DOM to prevent duplicates
                            if (!document.querySelector(`[data-message-id="${message.id}"]`)) {
                                addAdminMessage(
                                    message.sender_role === 'admin' ? 'admin' : 'user',
                                    message.message,
                                    message.sender_name,
                                    message.sent_at,
                                    message.id
                                );
                                lastAdminMessageId = Math.max(lastAdminMessageId, message.id);
                            }
                        });
                        
                        // Check if admin has connected
                        if (!isAdminConnected && data.admin_connected) {
                            isAdminConnected = true;
                            adminUserMessage.disabled = false;
                            adminUserMessage.placeholder = "Type your message to support...";
                            adminChatForm.querySelector('button').disabled = false;
                            addAdminMessage('admin', "An admin has joined the conversation. How can we help you?", "Support Team", new Date().toISOString());
                        }
                    }
                }
            })
            .catch(error => console.error('Error fetching messages:', error));
        }

        // Add message to admin chat
        function addAdminMessage(sender, message, senderName, sentAt, messageId = null) {
            // If no messageId provided (for outgoing messages), generate a temporary one
            if (!messageId) {
                messageId = 'temp_' + Date.now();
            }
            
            // Check if message already exists
            if (document.querySelector(`[data-message-id="${messageId}"]`)) {
                return;
            }
            
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}-message`;
            messageDiv.dataset.messageId = messageId;
            
            const time = new Date(sentAt);
            const timeString = time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            
            messageDiv.innerHTML = `
                <div class="message-content">
                    <div class="sender-label">${senderName}</div>
                    <p>${message}</p>
                    <div class="message-time">${timeString}</div>
                </div>
            `;
            
            adminMessagesContainer.appendChild(messageDiv);
            adminMessagesContainer.scrollTop = adminMessagesContainer.scrollHeight;
        }

        // End chat button
        endChatBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to end this chat?')) {
                fetch('admin1_chat_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        conversation_id: adminConversationId,
                        action: 'end_chat'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        addAdminMessage('admin', "This chat has been ended. You can start a new one anytime.", "Support Team", new Date().toISOString());
                        adminUserMessage.disabled = true;
                        adminUserMessage.placeholder = "Chat ended - start a new conversation if needed";
                        adminChatForm.querySelector('button').disabled = true;
                        endChatBtn.disabled = true;
                        stopPolling();
                    }
                });
            }
        });

        // Start polling if already on admin tab
        if (document.querySelector('.chat-tab[data-tab="admin"]').classList.contains('active')) {
            startPolling();
        }
    });
    </script>
</body>
</html>