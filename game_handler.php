<?php
session_start();
require_once 'auth.php'; // Include your authentication functions
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$dbname = 'stroke_patient_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Handle different actions
switch ($input['action'] ?? '') {
    case 'save_game_session':
        handleSaveGameSession($pdo, $input);
        break;
    case 'get_leaderboard':
        handleGetLeaderboard($pdo);
        break;
    case 'get_user_sessions':
        handleGetUserSessions($pdo);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function handleSaveGameSession($pdo, $data) {
    try {
        // Calculate session duration in seconds
        $sessionTimeParts = explode(':', $data['session_time']);
        $sessionDuration = (int)$sessionTimeParts[0] * 60 + (int)$sessionTimeParts[1];
        
        // Get user ID from session if logged in
        $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
        
        // Insert or update game session
        $stmt = $pdo->prepare("
            INSERT INTO bubble_game_sessions (
                user_id, player_id, game_name, session_start, session_end, 
                session_duration, level_reached, score, completed_levels
            ) VALUES (
                :user_id, :player_id, :game_name, 
                FROM_UNIXTIME(:session_start), FROM_UNIXTIME(:session_end), 
                :session_duration, :level_reached, :score, :completed_levels
            )
        ");
        
        $sessionStart = time() - $sessionDuration;
        $completedLevels = $data['completed'] ? 1 : 0;
        
        $stmt->execute([
            ':user_id' => $userId,
            ':player_id' => $data['player_id'],
            ':game_name' => 'Bubble Game',
            ':session_start' => $sessionStart,
            ':session_end' => time(),
            ':session_duration' => $sessionDuration,
            ':level_reached' => $data['level'],
            ':score' => $data['score'],
            ':completed_levels' => $completedLevels
        ]);
        
        // Get the session ID for level tracking
        $sessionId = $pdo->lastInsertId();
        
        // Save level details
        $levelStmt = $pdo->prepare("
            INSERT INTO bubble_game_levels (
                session_id, level_number, score_earned, time_spent, completed
            ) VALUES (
                :session_id, :level_number, :score_earned, :time_spent, :completed
            )
        ");
        
        $levelStmt->execute([
            ':session_id' => $sessionId,
            ':level_number' => $data['level'],
            ':score_earned' => $data['score'],
            ':time_spent' => $sessionDuration,
            ':completed' => $data['completed'] ? 1 : 0
        ]);
        
        echo json_encode(['success' => true, 'user_logged_in' => isLoggedIn()]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleGetLeaderboard($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT 
                COALESCE(u.username, s.player_id) AS player_name,
                s.player_id,
                s.level_reached AS level,
                s.score,
                CONCAT(
                    FLOOR(s.session_duration / 60), ':', 
                    LPAD(MOD(s.session_duration, 60), 2, '0')
                ) AS session_time,
                s.created_at
            FROM bubble_game_sessions s
            LEFT JOIN users u ON s.user_id = u.id
            ORDER BY s.score DESC, s.level_reached DESC, s.session_duration ASC
            LIMIT 20
        ");
        
        $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $leaderboard]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleGetUserSessions($pdo) {
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                s.id,
                s.game_name,
                s.level_reached,
                s.score,
                CONCAT(
                    FLOOR(s.session_duration / 60), ':', 
                    LPAD(MOD(s.session_duration, 60), 2, '0')
                ) AS session_time,
                s.created_at
            FROM bubble_game_sessions s
            WHERE s.user_id = :user_id
            ORDER BY s.created_at DESC
            LIMIT 50
        ");
        
        $stmt->execute([':user_id' => $_SESSION['user_id']]);
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $sessions]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>