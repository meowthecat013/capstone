<?php
require_once 'config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check if user is admin
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || !$user['is_admin']) {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// Get patient ID from request
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['error' => 'Invalid patient ID']);
    exit;
}

$patientId = $_GET['id'];

// Get patient details
$stmt = $pdo->prepare("SELECT 
                        u.*,
                        TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) as age,
                        (SELECT mood FROM daily_vitals WHERE user_id = u.id ORDER BY date DESC LIMIT 1) as mood_status,
                        (SELECT blood_pressure_systolic FROM daily_vitals WHERE user_id = u.id ORDER BY date DESC LIMIT 1) as blood_pressure_systolic,
                        (SELECT blood_pressure_diastolic FROM daily_vitals WHERE user_id = u.id ORDER BY date DESC LIMIT 1) as blood_pressure_diastolic,
                        (SELECT heart_rate FROM daily_vitals WHERE user_id = u.id ORDER BY date DESC LIMIT 1) as heart_rate,
                        (SELECT blood_sugar FROM daily_vitals WHERE user_id = u.id ORDER BY date DESC LIMIT 1) as blood_sugar
                      FROM users u 
                      WHERE u.id = ? AND u.role = 'patient'");
$stmt->execute([$patientId]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    header("HTTP/1.1 404 Not Found");
    echo json_encode(['error' => 'Patient not found']);
    exit;
}

// Format blood pressure
if ($patient['blood_pressure_systolic'] && $patient['blood_pressure_diastolic']) {
    $patient['blood_pressure'] = $patient['blood_pressure_systolic'] . '/' . $patient['blood_pressure_diastolic'];
} else {
    $patient['blood_pressure'] = 'N/A';
}

header('Content-Type: application/json');
echo json_encode($patient);