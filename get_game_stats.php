<?php
session_start();
require_once 'auth.php';

if (!isLoggedIn()) {
    http_response_code(401);
    die(json_encode(['error' => 'Not authenticated']));
}

header('Content-Type: application/json');

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'stroke_patient_system';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("
        SELECT id, game_name, start_time, end_time, duration 
        FROM monitor_game_sessions 
        WHERE user_id = ?
        ORDER BY start_time DESC
    ");
    
    $stmt->execute([$_SESSION['user_id']]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($results);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>