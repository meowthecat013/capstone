<?php
require_once 'config.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$firstDay = date('Y-m-01', strtotime($month));
$lastDay = date('Y-m-t', strtotime($month));
$prevMonth = date('Y-m', strtotime('-1 month', strtotime($firstDay)));
$nextMonth = date('Y-m', strtotime('+1 month', strtotime($firstDay)));

// Get all schedules for the month
$stmt = $pdo->prepare("SELECT * FROM patient_schedules 
    WHERE user_id = ? AND schedule_date BETWEEN ? AND ?");
$stmt->execute([$_SESSION['user_id'], $firstDay, $lastDay]);
$schedules = $stmt->fetchAll();

// Organize schedules by day
$scheduleDays = [];
foreach ($schedules as $schedule) {
    $day = date('j', strtotime($schedule['schedule_date']));
    if (!isset($scheduleDays[$day])) {
        $scheduleDays[$day] = [];
    }
    $scheduleDays[$day][] = $schedule;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .calendar-day {
            height: 120px;
            overflow-y: auto;
        }
        .today {
            background-color: #e6f7ff;
        }
        .has-schedule {
            background-color: #f0fff0;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Calendar - <?= date('F Y', strtotime($firstDay)) ?></h2>
            <div>
                <a href="calendar_view.php?month=<?= $prevMonth ?>" class="btn btn-outline-primary">Previous</a>
                <a href="calendar_view.php?month=<?= date('Y-m') ?>" class="btn btn-outline-secondary">Today</a>
                <a href="calendar_view.php?month=<?= $nextMonth ?>" class="btn btn-outline-primary">Next</a>
            </div>
        </div>
        
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Sun</th>
                    <th>Mon</th>
                    <th>Tue</th>
                    <th>Wed</th>
                    <th>Thu</th>
                    <th>Fri</th>
                    <th>Sat</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $startDay = date('w', strtotime($firstDay));
                $totalDays = date('t', strtotime($firstDay));
                $dayCount = 1;
                
                for ($i = 0; $i < 6; $i++): 
                    if ($dayCount > $totalDays) break;
                    echo '<tr>';
                    for ($j = 0; $j < 7; $j++):
                        if (($i === 0 && $j < $startDay) || $dayCount > $totalDays) {
                            echo '<td></td>';
                        } else {
                            $currentDate = date('Y-m-') . str_pad($dayCount, 2, '0', STR_PAD_LEFT);
                            $isToday = $currentDate === date('Y-m-d');
                            $hasSchedule = isset($scheduleDays[$dayCount]);
                            
                            echo '<td class="calendar-day ' . ($isToday ? 'today' : '') . ($hasSchedule ? ' has-schedule' : '') . '">';
                            echo '<div class="d-flex justify-content-between">';
                            echo '<strong>' . $dayCount . '</strong>';
                            if ($hasSchedule) {
                                echo '<span class="badge bg-info">' . count($scheduleDays[$dayCount]) . '</span>';
                            }
                            echo '</div>';
                            
                            if ($hasSchedule) {
                                echo '<div class="mt-2">';
                                foreach ($scheduleDays[$dayCount] as $schedule) {
                                    echo '<div class="small">';
                                    echo '<span class="badge bg-' . 
                                        ($schedule['status'] === 'Completed' ? 'success' : 
                                        ($schedule['status'] === 'In Progress' ? 'info' : 
                                        ($schedule['status'] === 'Skipped' ? 'danger' : 'secondary'))) . '">';
                                    echo substr($schedule['task'], 0, 10) . '...';
                                    echo '</span>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            }
                            
                            echo '<div class="mt-1">';
                            echo '<a href="schedule_list.php?date=' . $currentDate . '" class="btn btn-sm btn-outline-primary w-100">View</a>';
                            echo '</div>';
                            
                            echo '</td>';
                            $dayCount++;
                        }
                    endfor;
                    echo '</tr>';
                endfor;
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>