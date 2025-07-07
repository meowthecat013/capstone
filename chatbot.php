<?php
require_once 'config.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false];

try {
    if (!isLoggedIn()) {
        throw new Exception('Unauthorized');
    }

    $userId = $_SESSION['user_id'];
    
    // Get user's latest vitals
    $stmt = $pdo->prepare("SELECT * FROM daily_vitals WHERE user_id = ? ORDER BY date DESC LIMIT 1");
    $stmt->execute([$userId]);
    $vitals = $stmt->fetch();
    
    // Get user profile
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userProfile = $stmt->fetch();
    
    // Prepare data to send to Python API
    $pythonApiUrl = 'http://localhost:5000/chat'; // Change to your Python server URL
    
    $postData = [
        'user_id' => $userId,
        'message' => $input['message'] ?? '',
        'vitals' => $vitals ? [
            'mood' => $vitals['mood'],
            'blood_pressure_systolic' => $vitals['blood_pressure_systolic'],
            'blood_pressure_diastolic' => $vitals['blood_pressure_diastolic'],
            'heart_rate' => $vitals['heart_rate'],
            'blood_sugar' => $vitals['blood_sugar']
        ] : null,
        'user_profile' => [
            'full_name' => $userProfile['full_name'],
            'stroke_type' => $userProfile['stroke_type'],
            'rehabilitation_status' => $userProfile['rehabilitation_status']
        ]
    ];
    
    // Call Python API
    $ch = curl_init($pythonApiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $pythonResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($httpCode !== 200) {
        throw new Exception('AI service unavailable');
    }
    
    $aiData = json_decode($pythonResponse, true);
    
    // Save message to database
    $conversationId = $input['conversation_id'];
    
    // Save user message
    $stmt = $pdo->prepare("INSERT INTO chatbot_messages (conversation_id, sender, message) VALUES (?, 'user', ?)");
    $stmt->execute([$conversationId, $input['message']]);
    
    // Save bot response
    $stmt = $pdo->prepare("INSERT INTO chatbot_messages (conversation_id, sender, message) VALUES (?, 'bot', ?)");
    $stmt->execute([$conversationId, $aiData['response']]);
    
    $response = [
        'success' => true,
        'response' => $aiData['response'],
        'vitals_updated' => $vitals !== false,
        'latest_vitals' => $vitals
    ];
    
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);