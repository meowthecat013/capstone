<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header("HTTP/1.1 401 Unauthorized");
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];
$response = ['success' => false];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'start') {
            $game = $_POST['game'] ?? '';
            if (!empty($game)) {
                $sessionId = startGameSesssion($userId, $game);
                $response = ['success' => true, 'sessionId' => $sessionId];
            }
        } 
        elseif ($action === 'end') {
            $sessionId = $_POST['sessionId'] ?? 0;
            if ($sessionId > 0) {
                $success = endGameSesssion($sessionId);
                $response = ['success' => $success];
            }
        }
    }
} catch (Exception $e) {
    error_log("Session tracking error: " . $e->getMessage());
    $response['message'] = 'An error occurred';
}

header('Content-Type: application/json');
echo json_encode($response);
?>