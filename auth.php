<?php
// Authentication functions

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function loginUser($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        return $user;
    }
    
    return false;
}


function registerUser($userData) {
    global $pdo;
    
    // Validate required fields
    $required = ['username', 'password', 'email', 'full_name', 'date_of_birth', 'gender', 
                'stroke_type', 'stroke_date', 'stroke_severity', 'affected_side', 'rehabilitation_status'];
    foreach ($required as $field) {
        if (empty($userData[$field])) {
            return false;
        }
    }
    
    // Hash password
    $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users 
            (username, password, email, full_name, date_of_birth, gender, phone, address,
             stroke_type, stroke_date, stroke_severity, affected_side, rehabilitation_status,
             medical_history, current_medications, allergies,
             caregiver_name, caregiver_relationship, caregiver_phone, caregiver_email) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $userData['username'],
            $hashedPassword,
            $userData['email'],
            $userData['full_name'],
            $userData['date_of_birth'],
            $userData['gender'],
            $userData['phone'] ?? null,
            $userData['address'] ?? null,
            $userData['stroke_type'],
            $userData['stroke_date'],
            $userData['stroke_severity'],
            $userData['affected_side'],
            $userData['rehabilitation_status'],
            $userData['medical_history'] ?? null,
            $userData['current_medications'] ?? null,
            $userData['allergies'] ?? null,
            $userData['caregiver_name'] ?? null,
            $userData['caregiver_relationship'] ?? null,
            $userData['caregiver_phone'] ?? null,
            $userData['caregiver_email'] ?? null
        ]);
        
        return $pdo->lastInsertId();
    } catch(PDOException $e) {
        // Log the error for debugging
        error_log("Registration error: " . $e->getMessage());
        return false;
    }
}
function getUserData($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

function startGameSesssion($userId, $gameName) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO monitor_game_sessions (user_id, game_name, start_time) VALUES (?, ?, NOW())");
    $stmt->execute([$userId, $gameName]);
    return $pdo->lastInsertId();
}

function endGameSesssion($sessionId) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE monitor_game_sessions SET end_time = NOW(), duration = TIMESTAMPDIFF(SECOND, start_time, NOW()) WHERE id = ?");
    return $stmt->execute([$sessionId]);
}
?>