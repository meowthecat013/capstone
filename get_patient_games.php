<?php
require_once 'config.php';

// Authentication checks (same as other endpoints)

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['error' => 'Invalid patient ID']);
    exit;
}

$patientId = $_GET['id'];
$days = isset($_GET['days']) ? (int)$_GET['days'] : 7;

// Get game session data
$result = [
    'today_count' => 0,
    'avg_duration' => 0,
    'total_sessions' => 0,
    'favorite_game' => '',
    'recent_sessions' => [],
    'daily_counts' => [],
    'daily_duration' => [],
    'game_distribution' => [],
    'time_distribution' => [
        'morning' => 0,
        'afternoon' => 0,
        'evening' => 0,
        'night' => 0
    ]
];

// Today's game count
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM monitor_game_sessions 
                       WHERE user_id = ? AND DATE(created_at) = CURDATE()");
$stmt->execute([$patientId]);
$result['today_count'] = $stmt->fetch()['count'];

// Average duration
$stmt = $pdo->prepare("SELECT AVG(duration) as avg FROM monitor_game_sessions 
                       WHERE user_id = ? AND duration IS NOT NULL");
$stmt->execute([$patientId]);
$result['avg_duration'] = $stmt->fetch()['avg'];

// Total sessions
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM monitor_game_sessions 
                       WHERE user_id = ?");
$stmt->execute([$patientId]);
$result['total_sessions'] = $stmt->fetch()['total'];

// Favorite game
$stmt = $pdo->prepare("SELECT game_name, COUNT(*) as count FROM monitor_game_sessions 
                       WHERE user_id = ? 
                       GROUP BY game_name 
                       ORDER BY count DESC 
                       LIMIT 1");
$stmt->execute([$patientId]);
$fav = $stmt->fetch();
$result['favorite_game'] = $fav ? $fav['game_name'] : 'N/A';

// Recent sessions
$stmt = $pdo->prepare("SELECT 
                         DATE(created_at) as date,
                         game_name,
                         TIME(start_time) as start_time,
                         duration
                       FROM monitor_game_sessions 
                       WHERE user_id = ? 
                       ORDER BY created_at DESC 
                       LIMIT 10");
$stmt->execute([$patientId]);
$result['recent_sessions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Daily counts
$stmt = $pdo->prepare("SELECT 
                         DATE(created_at) as date,
                         COUNT(*) as count
                       FROM monitor_game_sessions 
                       WHERE user_id = ? 
                       AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                       GROUP BY DATE(created_at)
                       ORDER BY date");
$stmt->execute([$patientId, $days]);
$result['daily_counts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Daily duration
$stmt = $pdo->prepare("SELECT 
                         DATE(created_at) as date,
                         AVG(duration) as avg_duration
                       FROM monitor_game_sessions 
                       WHERE user_id = ? 
                       AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                       GROUP BY DATE(created_at)
                       ORDER BY date");
$stmt->execute([$patientId, $days]);
$result['daily_duration'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Game distribution
$stmt = $pdo->prepare("SELECT 
                         game_name,
                         COUNT(*) as count
                       FROM monitor_game_sessions 
                       WHERE user_id = ?
                       GROUP BY game_name
                       ORDER BY count DESC");
$stmt->execute([$patientId]);
$result['game_distribution'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Time distribution
$stmt = $pdo->prepare("SELECT 
                         CASE 
                           WHEN HOUR(start_time) BETWEEN 6 AND 11 THEN 'morning'
                           WHEN HOUR(start_time) BETWEEN 12 AND 16 THEN 'afternoon'
                           WHEN HOUR(start_time) BETWEEN 17 AND 21 THEN 'evening'
                           ELSE 'night'
                         END as time_period,
                         COUNT(*) as count
                       FROM monitor_game_sessions 
                       WHERE user_id = ?
                       GROUP BY time_period");
$stmt->execute([$patientId]);
$timeData = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($timeData as $row) {
    $result['time_distribution'][$row['time_period']] = $row['count'];
}

header('Content-Type: application/json');
echo json_encode($result);