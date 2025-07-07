<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];
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
            if (strlen($message) > 2000) {
                throw new Exception('Message is too long');
            }
            
            // Verify conversation belongs to user
            $stmt = $pdo->prepare("SELECT * FROM admin_chat_conversations WHERE id = ? AND user_id = ?");
            $stmt->execute([$conversationId, $userId]);
            $conversation = $stmt->fetch();
            
            if (!$conversation) {
                throw new Exception('Invalid conversation');
            }
            
            // Insert message
            $stmt = $pdo->prepare("INSERT INTO admin_chat_messages (conversation_id, sender_id, sender_role, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$conversationId, $userId, $userRole, $message]);
            
            echo json_encode(['success' => true, 'message_id' => $pdo->lastInsertId()]);
            break;
            
        case 'end_chat':
            $conversationId = $input['conversation_id'];
            
            // Verify conversation belongs to user
            $stmt = $pdo->prepare("SELECT * FROM admin_chat_conversations WHERE id = ? AND user_id = ?");
            $stmt->execute([$conversationId, $userId]);
            $conversation = $stmt->fetch();
            
            if (!$conversation) {
                throw new Exception('Invalid conversation');
            }
            
            // End the chat
            $pdo->prepare("UPDATE admin_chat_conversations SET is_active = 0 WHERE id = ?")
                ->execute([$conversationId]);
            
            echo json_encode(['success' => true]);
            break;
            
        case 'check_messages':
            $conversationId = $input['conversation_id'];
            $lastMessageId = $input['last_message_id'] ?? 0;
            
            // Verify conversation belongs to user
            $stmt = $pdo->prepare("SELECT c.*, u.full_name as admin_name 
                                  FROM admin_chat_conversations c
                                  LEFT JOIN users u ON c.admin_id = u.id
                                  WHERE c.id = ? AND c.user_id = ?");
            $stmt->execute([$conversationId, $userId]);
            $conversation = $stmt->fetch();
            
            if (!$conversation) {
                throw new Exception('Invalid conversation');
            }
            
            // Get new messages
            $stmt = $pdo->prepare("SELECT m.*, u.full_name as sender_name 
                           FROM admin_chat_messages m
                           JOIN users u ON m.sender_id = u.id
                           WHERE m.conversation_id = ? AND m.id > ?
                           ORDER BY m.sent_at ASC");
            $stmt->execute([$conversationId, $lastMessageId]);
            $newMessages = $stmt->fetchAll();
            
            // Check if admin has been assigned
            $adminOnline = (bool)$conversation['admin_id'];
            $adminName = $conversation['admin_name'];
            
            echo json_encode([
                'new_messages' => $newMessages,
                'admin_online' => $adminOnline,
                'admin_name' => $adminName,
                'success' => true
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}