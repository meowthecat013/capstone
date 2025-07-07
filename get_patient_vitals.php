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
$days = isset($_GET['days']) ? (int)$_GET['days'] : 7;

// Get patient vitals history
$stmt = $pdo->prepare("SELECT 
                        date,
                        mood,
                        blood_pressure_systolic,
                        blood_pressure_diastolic,
                        heart_rate,
                        blood_sugar
                      FROM daily_vitals 
                      WHERE user_id = ? 
                      AND date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                      ORDER BY date DESC");
$stmt->execute([$patientId, $days]);
$vitals = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($vitals);