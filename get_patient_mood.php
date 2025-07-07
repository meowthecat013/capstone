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

// Get mood tracking data
$result = [
    'most_common_mood' => '',
    'positive_percentage' => 0,
    'tracking_rate' => 0,
    'recent_entries' => [],
    'mood_counts' => [],
    'mood_trend' => [],
    'mood_by_day' => [
        'datasets' => []
    ],
    'mood_game_correlation' => []
];

// Most common mood
$stmt = $pdo->prepare("SELECT mood, COUNT(*) as count FROM daily_vitals 
                       WHERE user_id = ? AND mood IS NOT NULL
                       GROUP BY mood 
                       ORDER BY count DESC 
                       LIMIT 1");
$stmt->execute([$patientId]);
$common = $stmt->fetch();
$result['most_common_mood'] = $common ? $common['mood'] : 'N/A';

// Positive mood percentage (Happy + Neutral)
$stmt = $pdo->prepare("SELECT 
                         COUNT(CASE WHEN mood IN ('Happy', 'Neutral') THEN 1 END) as positive,
                         COUNT(*) as total
                       FROM daily_vitals 
                       WHERE user_id = ? AND mood IS NOT NULL");
$stmt->execute([$patientId]);
$pos = $stmt->fetch();
$result['positive_percentage'] = $pos['total'] ? round(($pos['positive'] / $pos['total']) * 100) : 0;

// Tracking rate (days with mood recorded vs total days)
$stmt = $pdo->prepare("SELECT 
                         DATEDIFF(CURDATE(), MIN(date)) as total_days,
                         COUNT(*) as recorded_days
                       FROM daily_vitals 
                       WHERE user_id = ? AND mood IS NOT NULL");
$stmt->execute([$patientId]);
$tracking = $stmt->fetch();
$result['tracking_rate'] = $tracking['total_days'] ? round(($tracking['recorded_days'] / $tracking['total_days']) * 100) : 0;

// Recent entries
$stmt = $pdo->prepare("SELECT 
                         date,
                         mood,
                         blood_pressure_systolic,
                         blood_pressure_diastolic,
                         heart_rate,
                         feelings
                       FROM daily_vitals 
                       WHERE user_id = ? 
                       AND mood IS NOT NULL
                       ORDER BY date DESC 
                       LIMIT 10");
$stmt->execute([$patientId]);
$result['recent_entries'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mood counts
$stmt = $pdo->prepare("SELECT 
                         mood,
                         COUNT(*) as count
                       FROM daily_vitals 
                       WHERE user_id = ? AND mood IS NOT NULL
                       GROUP BY mood
                       ORDER BY count DESC");
$stmt->execute([$patientId]);
$result['mood_counts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mood trend
$stmt = $pdo->prepare("SELECT 
                         date,
                         mood
                       FROM daily_vitals 
                       WHERE user_id = ? 
                       AND mood IS NOT NULL
                       AND date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                       ORDER BY date");
$stmt->execute([$patientId, $days]);
$result['mood_trend'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mood by day of week
$moods = ['Happy', 'Neutral', 'Tired', 'Anxious', 'Sad', 'Angry'];
$moodByDay = [];

foreach ($moods as $mood) {
    $stmt = $pdo->prepare("SELECT 
                             DAYOFWEEK(date) as day_num,
                             COUNT(*) as count
                           FROM daily_vitals 
                           WHERE user_id = ? 
                           AND mood = ?
                           GROUP BY DAYOFWEEK(date)
                           ORDER BY day_num");
    $stmt->execute([$patientId, $mood]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Initialize array with 0 counts for each day
    $dayCounts = array_fill(1, 7, 0);
    foreach ($data as $row) {
        $dayCounts[$row['day_num']] = (int)$row['count'];
    }
    
    $moodByDay[] = [
        'label' => $mood,
        'data' => array_values($dayCounts),
        'backgroundColor' => getMoodColorHex($mood)
    ];
}

$result['mood_by_day']['datasets'] = $moodByDay;

// Mood & game correlation
$stmt = $pdo->prepare("SELECT 
                         dv.date,
                         dv.mood,
                         SUM(g.duration) as total_duration,
                         COUNT(g.id) as game_count
                       FROM daily_vitals dv
                       LEFT JOIN monitor_game_sessions g ON dv.user_id = g.user_id AND DATE(g.created_at) = dv.date
                       WHERE dv.user_id = ?
                       AND dv.mood IS NOT NULL
                       AND dv.date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                       GROUP BY dv.date, dv.mood
                       ORDER BY dv.date");
$stmt->execute([$patientId, $days]);
$correlationData = $stmt->fetchAll(PDO::FETCH_ASSOC);

$moodMap = ['Happy'=>5, 'Neutral'=>4, 'Tired'=>3, 'Anxious'=>2, 'Sad'=>1, 'Angry'=>0];
foreach ($correlationData as $row) {
    if ($row['total_duration']) {
        $result['mood_game_correlation'][] = [
            'x' => (int)$row['total_duration'],
            'y' => $moodMap[$row['mood']] ?? 3
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($result);

function getMoodColorHex($mood) {
    $colors = [
        'Happy' => '#2a9d8f',
        'Neutral' => '#6c757d',
        'Tired' => '#457b9d',
        'Anxious' => '#f4a261',
        'Sad' => '#e63946',
        'Angry' => '#d62828'
    ];
    return $colors[$mood] ?? '#6c757d';
}