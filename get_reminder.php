<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

$userId = $_SESSION['user_id'];
$reminderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($reminderId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM reminders WHERE id = ? AND user_id = ?");
    $stmt->execute([$reminderId, $userId]);
    $reminder = $stmt->fetch();
    
    if ($reminder) {
        header('Content-Type: application/json');
        echo json_encode([
            'id' => $reminder['id'],
            'title' => $reminder['title'],
            'description' => $reminder['description'],
            'reminder_time' => $reminder['reminder_time'],
            'days_of_week' => $reminder['days_of_week']
        ]);
        exit;
    }
}

header('HTTP/1.1 404 Not Found');
exit;
?>