<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit;
}

$userId = $_SESSION['user_id'];
$days = isset($_GET['days']) ? (int)$_GET['days'] : 7;

$stmt = $pdo->prepare("SELECT * FROM daily_vitals 
                      WHERE user_id = ? 
                      ORDER BY date DESC 
                      LIMIT ?");
$stmt->execute([$userId, $days]);
$vitals = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($vitals);
?>