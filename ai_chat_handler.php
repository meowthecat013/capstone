<?php
require_once 'config.php';
require_once 'ai_processor.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    switch ($action) {
        case 'send_message':
            $conversationId = $input['conversation_id'];
            $message = trim($input['message']);
            
            if (empty($message)) {
                throw new Exception('Message cannot be empty');
            }
            
            // Verify conversation belongs to user
            $stmt = $pdo->prepare("SELECT * FROM ai_chat_conversations WHERE id = ? AND user_id = ?");
            $stmt->execute([$conversationId, $userId]);
            $conversation = $stmt->fetch();
            
            if (!$conversation) {
                throw new Exception('Invalid conversation');
            }
            
            // Save user message
            $stmt = $pdo->prepare("INSERT INTO ai_chat_messages 
                                  (conversation_id, sender_id, sender_role, message) 
                                  VALUES (?, ?, 'user', ?)");
            $stmt->execute([$conversationId, $userId, $message]);
            
            // Get conversation context (last 5 messages)
            $stmt = $pdo->prepare("SELECT * FROM ai_chat_messages 
                                  WHERE conversation_id = ? 
                                  ORDER BY sent_at DESC 
                                  LIMIT 5");
            $stmt->execute([$conversationId]);
            $history = array_reverse($stmt->fetchAll());
            
            // Process with our offline AI
            $aiResponse = processWithAI($message, $history, $userId);
            
            // Save AI response
            $stmt = $pdo->prepare("INSERT INTO ai_chat_messages 
                                  (conversation_id, sender_id, sender_role, message, is_ai) 
                                  VALUES (?, 0, 'ai', ?, TRUE)");
            $stmt->execute([$conversationId, $aiResponse]);
            
            echo json_encode([
                'success' => true, 
                'response' => $aiResponse,
                'conversation_id' => $conversationId
            ]);
            break;
            
        case 'transfer_to_human':
            $conversationId = $input['conversation_id'];
            
            // Verify conversation belongs to user
            $stmt = $pdo->prepare("SELECT * FROM ai_chat_conversations WHERE id = ? AND user_id = ?");
            $stmt->execute([$conversationId, $userId]);
            $conversation = $stmt->fetch();
            
            if (!$conversation) {
                throw new Exception('Invalid conversation');
            }
            
            // Get conversation summary for human handoff
            $stmt = $pdo->prepare("SELECT message, sender_role FROM ai_chat_messages WHERE conversation_id = ? ORDER BY sent_at ASC");
            $stmt->execute([$conversationId]);
            $messages = $stmt->fetchAll();
            
            $summary = "AI Conversation Summary:\n";
            foreach ($messages as $msg) {
                $sender = $msg['sender_role'] === 'user' ? 'Patient' : 'AI Assistant';
                $summary .= "[$sender]: {$msg['message']}\n";
            }
            
            // Create or update admin conversation with this context
            $stmt = $pdo->prepare("SELECT id FROM admin_chat_conversations WHERE user_id = ? AND is_active = 1 LIMIT 1");
            $stmt->execute([$userId]);
            $adminConversation = $stmt->fetch();
            
            if ($adminConversation) {
                $pdo->prepare("UPDATE admin_chat_conversations SET context_summary = ? WHERE id = ?")
                    ->execute([$summary, $adminConversation['id']]);
            } else {
                $pdo->prepare("INSERT INTO admin_chat_conversations (user_id, context_summary) VALUES (?, ?)")
                    ->execute([$userId, $summary]);
            }
            
            // Notify admins about the transfer
            // (Implement your notification system here)
            
            echo json_encode(['success' => true]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}