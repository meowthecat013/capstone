<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$adminId = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    switch ($action) {
        case 'send_message':
    $conversationId = $input['conversation_id'];
    $message = $input['message'];
    $tempId = $input['temp_id'] ?? null;
    
    // Verify conversation belongs to admin
    $stmt = $pdo->prepare("SELECT * FROM admin_chat_conversations WHERE id = ? AND (admin_id = ? OR admin_id IS NULL)");
    $stmt->execute([$conversationId, $adminId]);
    $conversation = $stmt->fetch();
    
    if (!$conversation) {
        throw new Exception('Invalid conversation');
    }
    
    // If conversation has no admin, assign to current admin
    if (!$conversation['admin_id']) {
        $pdo->prepare("UPDATE admin_chat_conversations SET admin_id = ? WHERE id = ?")
            ->execute([$adminId, $conversationId]);
    }
    
    // Insert message
    $stmt = $pdo->prepare("INSERT INTO admin_chat_messages (conversation_id, sender_id, sender_role, message) VALUES (?, ?, 'admin', ?)");
    $stmt->execute([$conversationId, $adminId, $message]);
    $messageId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message_id' => $messageId,
        'temp_id' => $tempId  // Echo back the temp ID for reference
    ]);
    break;
            
        case 'check_updates':
            $lastMessageId = $input['last_message_id'] ?? 0;
            $conversationId = $input['conversation_id'] ?? null;
            
            // Get active conversations for this admin
            $stmt = $pdo->prepare("
                SELECT c.id, u.full_name as user_name, c.created_at,
                       (SELECT COUNT(*) FROM admin_chat_messages m 
                        WHERE m.conversation_id = c.id AND m.sender_role = 'patient' AND m.is_read = FALSE) as unread_count
                FROM admin_chat_conversations c
                JOIN users u ON c.user_id = u.id
                WHERE c.is_active = 1 AND (c.admin_id = ? OR c.admin_id IS NULL)
                ORDER BY c.created_at ASC
            ");
            $stmt->execute([$adminId]);
            $activeConversations = $stmt->fetchAll();
            
            // Get waiting conversations
            $waitingConversations = $pdo->query("
                SELECT c.id, u.full_name as user_name, c.created_at 
                FROM admin_chat_conversations c
                JOIN users u ON c.user_id = u.id
                WHERE c.is_active = 1 AND c.admin_id IS NULL
                ORDER BY c.created_at ASC
            ")->fetchAll();
            
            // Get new messages if a conversation is selected
            $newMessages = [];
            if ($conversationId) {
                $stmt = $pdo->prepare("
                    SELECT m.*, u.full_name as sender_name, 
                           CASE WHEN m.sender_role = 'admin' THEN 'admin' ELSE 'user' END as sender_type
                    FROM admin_chat_messages m
                    JOIN users u ON m.sender_id = u.id
                    WHERE m.conversation_id = ? AND m.id > ?
                    ORDER BY m.sent_at ASC
                ");
                $stmt->execute([$conversationId, $lastMessageId]);
                $newMessages = $stmt->fetchAll();
            }
            
            echo json_encode([
                'active_conversations' => $activeConversations,
                'waiting_conversations' => $waitingConversations,
                'new_messages' => $newMessages,
                'success' => true
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}